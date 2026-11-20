# Dokumen Desain Sistem: Konfirmasi Jadwal Wawancara Kandidat

Dokumen spesifikasi kebutuhan, arsitektur sistem, dan alur kerja integrasi Google Form dengan Dashboard HR.

---

## 1. Arsitektur Tingkat Tinggi (High-Level Architecture)

```mermaid
flowchart TD
    subgraph Ingestion["1. Ingestion Layer"]
        GF["Google Form (Pelamar)"]
        GAS["Google Apps Script (onFormSubmit)"]
        GS["Google Sheets (Mirror/Backup)"]
        GF -->|Submit Response| GAS
        GAS -->|Log Row| GS
    end

    subgraph CoreBackend["2. Backend & Database Layer"]
        API["Backend API (Node.js / Next.js API Routes)"]
        DB[(Database Relasional: PostgreSQL / SQLite)]
        AUTH["Security & Token Service (UUIDv4 / HMAC)"]
        MAIL["Notification Service (SMTP / Resend / Gmail API)"]
        
        GAS -->|HTTP POST Webhook| API
        API <--> DB
        API <--> AUTH
        API --> MAIL
    end

    subgraph HRPortal["3. HR Workspace"]
        HRUI["HR Dashboard (Web App)"]
        HRUser(("Tim HR / Rekruter"))
        HRUser <--> HRUI
        HRUI <-->|REST API / State Update| API
    end

    subgraph CandidateChannel["4. Candidate Touchpoints"]
        CAND(("Kandidat"))
        EMAIL["Email Undangan Wawancara"]
        PORTAL["Candidate Response Portal (Secure Token)"]
        
        MAIL -->|Kirim Undangan + Link Token| EMAIL
        EMAIL -->|Buka Link| PORTAL
        PORTAL -->|Kirim Aksi: Confirm / Reschedule / Decline| API
        CAND <--> EMAIL
        CAND <--> PORTAL
    end
```

---

## 2. Diagram Alur & Transisi Status (State Machine)

### 2.1 State Transitions

```mermaid
stateDiagram-v2
    [*] --> New_Application: Kandidat submit Google Form
    New_Application --> Invited: HR set tanggal, waktu, pewawancara & kirim undangan
    
    state Invited {
        [*] --> Awaiting_Response
        Awaiting_Response --> Expired: Melewati batas waktu tanpa respon
    }

    Invited --> Confirmed: Kandidat klik 'Confirm'
    Invited --> Declined: Kandidat klik 'Decline'
    Invited --> Reschedule_Requested: Kandidat ajukan 'Reschedule' (maks. 1x)
    
    Reschedule_Requested --> Invited: HR setuju & tentukan jadwal baru (Kirim ulang undangan)
    Reschedule_Requested --> Declined: HR menolak reschedule / batas reschedule terlampaui
    
    Confirmed --> [*]: Selesai / Lanjut ke Wawancara
    Declined --> [*]: Arsip / Selesai
    Expired --> [*]: Kadaluwarsa
```

### 2.2 Sequence Diagram: Alur End-to-End

```mermaid
sequenceDiagram
    autonumber
    actor Pelamar as Kandidat
    participant GF as Google Form
    participant GAS as Google Apps Script
    participant API as Backend Server
    participant DB as Database
    actor HR as HR Rekruter
    participant Mail as Email Service

    Pelamar->>GF: Mengisi data diri & resume lamaran
    GF->>GAS: Trigger onFormSubmit event
    GAS->>API: POST /api/webhooks/candidates (Payload kandidat)
    API->>DB: INSERT Candidate (status: NEW_APPLICATION)
    
    HR->>API: Buka Dashboard & pilih kandidat
    HR->>API: POST /api/interviews/schedule (Tanggal, Waktu, Interviewer, Link)
    API->>DB: INSERT Interview & generate Secure Token
    API->>Mail: Generate email template & kirim ke kandidat
    Mail-->>Pelamar: Email undangan + Pautan Respon Unik
    
    alt Skenario A: Kandidat Menyetujui (Confirm)
        Pelamar->>API: POST /api/public/portal/:token/respond { action: "CONFIRM" }
        API->>DB: UPDATE Interview status = CONFIRMED
        API->>Mail: Kirim konfirmasi ke HR & Kandidat (Google Calendar invite)
    else Skenario B: Kandidat Minta Reschedule
        Pelamar->>API: POST /api/public/portal/:token/respond { action: "RESCHEDULE", reason: "..." }
        API->>DB: UPDATE Interview status = RESCHEDULE_REQUESTED, increment reschedule_count
        API->>HR: Notifikasi alert di Dashboard & Email ke HR
        HR->>API: Review alasan & set jadwal baru
        API->>Mail: Kirim email undangan baru ke Kandidat
    else Skenario C: Kandidat Menolak (Decline)
        Pelamar->>API: POST /api/public/portal/:token/respond { action: "DECLINE" }
        API->>DB: UPDATE Interview status = DECLINED
        API->>HR: Notifikasi penolakan di Dashboard
    end
```

---

## 3. Desain Skema Basis Data (Database Schema)

```mermaid
erDiagram
    CANDIDATES ||--o{ INTERVIEWS : has
    INTERVIEWS ||--o{ RESCHEDULE_LOGS : logs
    INTERVIEWS ||--o{ NOTIFICATION_LOGS : triggers

    CANDIDATES {
        uuid id PK
        string full_name
        string email
        string phone
        string applied_position
        string portfolio_url
        string resume_url
        enum current_stage "APPLIED, SCREENING, INTERVIEWING, OFFERED, REJECTED"
        datetime created_at
        datetime updated_at
    }

    INTERVIEWS {
        uuid id PK
        uuid candidate_id FK
        int round_number "1, 2, dst."
        string stage_name "Contoh: HR Interview, Technical Test"
        datetime scheduled_datetime
        int duration_minutes "Default: 45 / 60"
        string interviewer_name
        string interviewer_email
        string meeting_location_link "Google Meet / Zoom / Onsite Room"
        string invitation_token "Unique UUID / Hash untuk akses portal"
        datetime token_expires_at "Jadwal wawancara atau T-1 jam"
        enum response_status "NEW_APPLICATION, INVITED, CONFIRMED, RESCHEDULE_REQUESTED, DECLINED, EXPIRED"
        int reschedule_count "Maksimal 1 kali"
        text candidate_notes "Catatan / alasan respon kandidat"
        datetime response_received_at
        datetime created_at
        datetime updated_at
    }

    RESCHEDULE_LOGS {
        uuid id PK
        uuid interview_id FK
        text reason
        datetime requested_alternative_slot
        enum hr_decision "PENDING, APPROVED_WITH_NEW_SLOT, REJECTED"
        datetime processed_at
        string processed_by_hr
    }

    NOTIFICATION_LOGS {
        uuid id PK
        uuid interview_id FK
        string recipient_email
        string notification_type "INVITATION, RESCHEDULE_ALERT, CONFIRMATION, REMINDER"
        string delivery_status "SENT, FAILED, DELIVERED"
        text error_message
        datetime sent_at
    }
```

---

## 4. Aturan Bisnis & Mekanisme Validasi (Business Rules)

1. **Kontrol Jadwal Eksklusif di HR**: Kandidat hanya dapat mengajukan permohonan *reschedule* disertai alasan tertulis. Kandidat **tidak dapat** langsung menetapkan jadwal baru secara otomatis tanpa verifikasi dan persetujuan HR.
2. **Batas Reschedule Maksimal 1 Kali**: Sistem secara terprogram menonaktifkan opsi *Reschedule* jika atribut `reschedule_count >= 1`. Percobaan permohonan kedua akan diblokir dengan pesan petunjuk untuk menghubungi HR secara langsung.
3. **Keamanan Token Respon & Penguncian Akses**:
   - Pautan respon yang dikirimkan ke email kandidat menggunakan format: `https://domain.com/portal/interview/{invitation_token}`.
   - Token hanya berlaku satu kali untuk respon final (**Confirm** atau **Decline**).
   - Token memiliki batas kedaluwarsa otomatis (`token_expires_at = scheduled_datetime` atau waktu yang ditentukan HR).
   - Setelah direspon final, akses ke tautan akan menampilkan status kartu ringkasan tanpa tombol aksi untuk mencegah *double submission*.
4. **Validasi Data Awal Google Form**:
   - Regex validation ketat pada Google Form untuk email dan nomor WhatsApp/telepon internasional/nasional (contoh: `^(\+62|62|0)8[1-9][0-9]{6,11}$`).

---

## 5. Spesifikasi Antarmuka Pengguna (UI / UX Wireframe)

### 5.1 Dashboard HR (Admin Interface)
- **Header & Metric Cards**: Total Kandidat Masuk, Menunggu Penjadwalan, Menunggu Konfirmasi (`Invited`), Terkonfirmasi (`Confirmed`), Reschedule Diminta (`Reschedule Requested`).
- **Tabel Kandidat & Filter**: Filter posisi, status, tanggal. Aksi cepat: Jadwalkan Wawancara, Tinjau Reschedule, Detail Kandidat.
- **Modal Penjadwalan**: Pemilihan tanggal & waktu, pewawancara, tautan Google Meet, preview email.
- **Drawer Peninjauan Reschedule**: Tampilan alasan kandidat, form penetapan jadwal baru pengganti.

### 5.2 Portal Respon Kandidat (Mobile-First Landing Page)
- **Kartu Ringkasan Undangan**: Nama, posisi, tanggal, jam, zona waktu, platform wawancara.
- **Tiga Tombol Aksi**:
  - `[Konfirmasi Hadir]` (Hijau): Konfirmasi 1 klik.
  - `[Ajukan Reschedule]` (Oranye): Form alasan wajib diisi; dinonaktifkan bila sudah pernah reschedule 1x.
  - `[Tolak Undangan]` (Merah): Konfirmasi penolakan proses.
- **Status State**: Halaman sukses informatif dengan konfirmasi status terkini dan instruksi kalender.

---

## 6. Spesifikasi API (API Contracts)

| Method | Endpoint | Deskripsi | Akses |
|---|---|---|---|
| `POST` | `/api/webhooks/google-form` | Menerima data pendaftaran baru dari Google Apps Script | Webhook Secret Key |
| `GET` | `/api/dashboard/metrics` | Statistik agregat untuk kartu metriks HR | HR Session |
| `GET` | `/api/candidates` | Daftar seluruh kandidat dengan filter & pagination | HR Session |
| `GET` | `/api/candidates/:id` | Detail data lengkap kandidat & riwayat wawancara | HR Session |
| `POST` | `/api/interviews/schedule` | Membuat sesi wawancara, generate token, dan kirim email | HR Session |
| `GET` | `/api/public/portal/:token` | Mengambil detail wawancara untuk halaman portal kandidat | Publik (Validated by Token) |
| `POST` | `/api/public/portal/:token/respond` | Mengirimkan respon kandidat (`CONFIRM`, `RESCHEDULE`, `DECLINE`) | Publik (Validated by Token) |
| `POST` | `/api/interviews/:id/resolve-reschedule` | HR menyetujui alasan dan menentukan jadwal pengganti | HR Session |

---

## 7. Rekomendasi Stack Teknologi

- **Framework**: **Next.js 14+ (App Router)** atau **React + Node.js (Express)**
- **Database & ORM**: **PostgreSQL / SQLite** dengan **Prisma ORM**
- **Styling**: **Tailwind CSS** + komponen UI modern
- **Email Service**: **Nodemailer / Resend / Gmail API**
- **Integrasi Google Form**: **Google Apps Script** (Trigger `onFormSubmit`)
