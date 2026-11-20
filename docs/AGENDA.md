# AGENDA INTEGRASI SOFTWARE & APLIKASI EKSTERNAL
**Sistem Konfirmasi Jadwal Wawancara Kandidat (RecruitFlow)**

Dokumen ini mencatat seluruh agenda, roadmap, dan spesifikasi teknis untuk menghubungkan sistem RecruitFlow dengan software dan aplikasi pihak ketiga (eksternal). Sesuai arahan, integrasi eksternal ini ditunda ke fase berikutnya setelah implementasi backend dan frontend internal selesai dan terverifikasi.

---

## 1. Ringkasan Agenda Integrasi

| No | Modul / Software Eksternal | Fungsi Utama | Prioritas | Status |
|---|---|---|---|---|
| 1 | **Google Form & Google Apps Script** | Ingest data pelamar baru secara otomatis ke webhook sistem | Fase 2 | Agenda Terjadwal |
| 2 | **SMTP / Email Delivery Gateway** | Pengiriman email resmi undangan wawancara & reminder otomatis | Fase 2 | Agenda Terjadwal |
| 3 | **Google Calendar / Outlook Calendar** | Sinkronisasi jadwal otomatis ke kalender interviewer & kandidat | Fase 3 | Agenda Terjadwal |
| 4 | **WhatsApp Business API / SMS Gateway** | Pengiriman reminder darurat 24 jam via pesan instan | Fase 3 | Agenda Terjadwal |
| 5 | **Google Meet / Zoom API Auto-generate** | Pembuatan link ruang rapat virtual secara dinamis | Fase 4 | Agenda Masa Depan |

---

## 2. Rincian Teknis & Roadmap Tiap Integrasi

### Agenda 1: Google Form & Apps Script Webhook
- **Tujuan**: Setiap ada kandidat baru yang mengisi Google Form rekrutmen, data otomatis dikirim ke backend Laravel tanpa HR harus input manual.
- **Endpoint Target**: `POST /api/webhooks/google-form`
- **Payload**:
  ```json
  {
    "nama": "Nama Lengkap",
    "email": "email@example.com",
    "telepon": "081234567890",
    "posisi": "Posisi yang dilamar",
    "berkas": "https://drive.google.com/..."
  }
  ```
- **Langkah Rollout**:
  1. Pasang script Google Apps Script (tersedia di [docs/GOOGLE_APPS_SCRIPT.md](file:///c:/Users/ibnhu/Desktop/Muhamad%20Ibnhu/Sistem%20Perekrutan/docs/GOOGLE_APPS_SCRIPT.md)) pada form rekrutmen perusahaan.
  2. Tambahkan trigger `On Form Submit`.
  3. Konfigurasi secret token header `X-Webhook-Secret` pada environment `.env` untuk validasi keamanan HMAC.

---

### Agenda 2: SMTP / Email Transactional Gateway
- **Tujuan**: Mengirimkan email undangan berformat HTML responsif dengan token unik `https://domain.com/portal/{token}`.
- **Provider yang Direkomendasikan**:
  - Resend / Mailgun / SendGrid (untuk production bertrafik tinggi)
  - Google Workspace SMTP (untuk volume internal standar)
- **Komponen Laravel**:
  - `Mailable`: `App\Mail\InterviewInvitationMail`
  - `Mailable`: `App\Mail\InterviewReminderMail`
  - `Mailable`: `App\Mail\RescheduleUpdateMail`
- **Konfigurasi Environment**:
  ```env
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.mailgun.org
  MAIL_PORT=587
  MAIL_USERNAME=api_key
  MAIL_PASSWORD=secret
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS="recruitment@technusantara.id"
  MAIL_FROM_NAME="HR TechNusantara"
  ```
- **Job Queue**: Menggunakan background queue `php artisan queue:work` agar responsivitas API tetap instan (< 100ms).

---

### Agenda 3: Two-Way Google Calendar / Microsoft 365 Sync
- **Tujuan**:
  - Saat jadwal wawancara dibuat di dashboard HR, otomatis membuat event di Google Calendar Interviewer dan menyertakan link portal kandidat.
  - Memeriksa ketersediaan jam kerja pewawancara (free/busy time detection).
- **Spesifikasi Teknis**:
  - Google Calendar API v3 (OAuth 2.0 Service Account).
  - Webhook Push Notification dari Google Calendar untuk mendeteksi jika HR mengubah jadwal langsung dari Google Calendar.

---

### Agenda 4: WhatsApp Gateway (Reminder Cadangan)
- **Tujuan**: Mengirimkan pesan pengingat kepada kandidat yang belum merespons dalam waktu 24 jam sebelum batas waktu konfirmasi berakhir.
- **Provider yang Didukung**:
  - Fonnte / Wablas / Twilio WhatsApp API.
- **Format Pesan**:
  > "Halo [Nama], kami mengingatkan perihal undangan wawancara posisi [Posisi] yang dijadwalkan pada [Hari, Tanggal]. Mohon konfirmasi kehadiran Anda melalui tautan resmi: [Link Portal]. Terima kasih."

---

### Agenda 5: Integrasi Platform Meeting Otomatis (Google Meet / Zoom API)
- **Tujuan**: Menghilangkan kebutuhan HR menginput link rapat secara manual saat memilih kategori `Online`.
- **Mekanisme**:
  - Saat HR memilih Kategori: `Online`, sistem dapat memanggil Google Calendar API / Zoom API untuk otomatis meng-generate `meeting_id` dan `join_url`.

---

## 3. Checklist Kesiapan Sebelum Aktivasi
- [x] Database schema mendukung semua atribut (`kategori`, `link_meeting`, `lokasi`, `batas_respon`, `token`).
- [x] REST API Controller siap menerima webhook dan parameter meeting.
- [x] Portal respon kandidat siap menerima respon (Confirm, Reschedule, Decline).
- [ ] Domain publik / reverse proxy (Cloudflare Tunnels / Ngrok) disiapkan untuk menerima webhook dari luar.
- [ ] Kredensial API eksternal (Google OAuth JSON / SendGrid API Key) dimasukkan ke file `.env`.
