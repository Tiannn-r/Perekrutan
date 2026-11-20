# RecruitFlow - Sistem Konfirmasi Jadwal Wawancara Kandidat

Sistem otomatisasi alur konfirmasi jadwal wawancara kerja kandidat yang terintegrasi dengan Google Form dan Dashboard HR terpusat. Dibangun dengan tech stack **Laravel 13, HTML, CSS, JavaScript, dan MySQL**.

---

## 🚀 Fitur Utama

1. **Dashboard HR Interaktif**:
   - 5 Metrik utama: Total Pelamar, Perlu Jadwal, Menunggu Respon, Reschedule, dan Terkonfirmasi.
   - Filter status cepat (*Semua, Reschedule, Menunggu, Terkonfirmasi, Perlu Jadwal*) dan pencarian kandidat instan.
   - Modal penjadwalan fleksibel dengan opsi **Kategori**:
     - **Online**: Input link meeting Google Meet / Zoom.
     - **Offline**: Input alamat fisik & ruangan pertemuan kantor.
   - Modal peninjauan permohonan *reschedule* langsung dari kandidat.
   - Pengambilan tautan portal ber-token langsung dari dashboard.

2. **Portal Respon Pelamar (Tautan Ber-Token Mandiri)**:
   - Kandidat tidak perlu membuat akun atau login (otentikasi aman melalui token satu-kali di tautan email).
   - **Kartu 1: Detail Undangan Utama**: Menampilkan jadwal, kategori, durasi, pewawancara, batas konfirmasi, dan tombol respon ringkas tanpa ikon dekoratif berlebih (*concise UI*).
   - **Kartu 2: Pengajuan Reschedule**: Formulir alasan penundaan dan saran waktu alternatif dengan pembatasan ketat kuota **maksimal 1 (satu) kali**.
   - **Kartu 3: Verifikasi 2 Langkah Penolakan**: Memastikan kandidat tidak salah tekan tombol tolak dan wajib menyertakan alasan penolakan.
   - **Kartu 4: Feedback / Status Terkunci**: Menampilkan tautan rapat virtual atau alamat kantor jika hadir, atau konfirmasi peninjauan jika reschedule.

3. **Preview Email Undangan**:
   - Tampilan visual email resmi yang dikirim ke inbox pelamar lengkap dengan tombol aksi responsif.

4. **Agenda Integrasi Eksternal Terjadwal**:
   - Dokumen teknis roadmap integrasi aplikasi luar (Google Form webhook, Google Calendar sync, WhatsApp gateway, SMTP mailer) tercatat rapi di [`docs/AGENDA.md`](docs/AGENDA.md).

---

## 🛠️ Tech Stack

- **Backend**: Laravel 13 (PHP 8.5+)
- **Database**: MySQL (`db_perekrutan`)
- **Frontend**: Blade Templating, HTML5, Vanilla JavaScript (Fetch API), Tailwind CSS
- **Testing**: PHPUnit / Laravel Test Suite (10 test cases, 41 assertions, 100% pass)

---

## 💻 Panduan Menjalankan Sistem

### 1. Prasyarat
- PHP >= 8.2 (disarankan PHP 8.5)
- Composer
- MySQL (XAMPP / MySQL Server lokal berjalan di port 3306)

### 2. Setup Database & Aplikasi
```bash
# Pastikan database db_perekrutan sudah dibuat di MySQL
# Jalankan migrasi dan seeder dummy
php artisan migrate:fresh --seed

# Jalankan test suite
php artisan test
```

### 3. Menjalankan Server Lokal
```bash
php artisan serve
```
Buka browser di:
- **Dashboard HR**: [http://127.0.0.1:8000/dashboard](http://127.0.0.1:8000/dashboard)
- **Portal Kandidat (Demo)**: [http://127.0.0.1:8000/portal/tok_demo_ahmad_fauzi](http://127.0.0.1:8000/portal/tok_demo_ahmad_fauzi)
- **Preview Email Undangan**: [http://127.0.0.1:8000/email-preview](http://127.0.0.1:8000/email-preview)

---

## 📂 Struktur Dokumentasi

- [`docs/AGENDA.md`](docs/AGENDA.md): Agenda & Roadmap integrasi aplikasi eksternal (Google Apps Script, WhatsApp, Calendar).
- [`docs/SYSTEM_DESIGN.md`](docs/SYSTEM_DESIGN.md): Arsitektur sistem, skema basis data, dan aturan bisnis (PRD).
- [`docs/GOOGLE_APPS_SCRIPT.md`](docs/GOOGLE_APPS_SCRIPT.md): Kode Google Apps Script untuk webhook otomatis Google Form.
- [`prototype/index.html`](prototype/index.html): Prototipe desain tampilan statis awal.
