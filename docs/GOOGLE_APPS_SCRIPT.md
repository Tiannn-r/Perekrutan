# Petunjuk Integrasi Google Form ke Backend Laravel

Skrip Google Apps Script ini menghubungkan Google Form pendaftaran pelamar langsung ke endpoint webhook Laravel kita:
`POST /api/webhooks/google-form`

---

## 1. Kode Google Apps Script

Buka Google Form Anda $\rightarrow$ Klik ikon titik tiga di kanan atas $\rightarrow$ Pilih **Script editor** (atau Ekstensi $\rightarrow$ Apps Script pada Google Sheet terkait). Hapus kode default dan tempelkan skrip berikut:

```javascript
/**
 * Trigger otomatis saat kandidat mengirimkan formulir lamaran kerja
 */
function onFormSubmit(e) {
  var itemResponses = e.response.getItemResponses();
  
  var payload = {
    nama: "",
    email: "",
    telepon: "",
    posisi: "",
    berkas: ""
  };

  // Ekstraksi jawaban berdasarkan judul pertanyaan
  for (var i = 0; i < itemResponses.length; i++) {
    var title = itemResponses[i].getItem().getTitle().toLowerCase();
    var val = itemResponses[i].getResponse();

    if (title.indexOf("nama") !== -1) {
      payload.nama = String(val);
    } else if (title.indexOf("email") !== -1) {
      payload.email = String(val);
    } else if (title.indexOf("telepon") !== -1 || title.indexOf("whatsapp") !== -1 || title.indexOf("hp") !== -1) {
      payload.telepon = String(val);
    } else if (title.indexOf("posisi") !== -1 || title.indexOf("jabatan") !== -1) {
      payload.posisi = String(val);
    } else if (title.indexOf("cv") !== -1 || title.indexOf("resume") !== -1 || title.indexOf("berkas") !== -1) {
      payload.berkas = Array.isArray(val) ? val.join(", ") : String(val);
    }
  }

  // URL Backend Laravel Anda
  // Untuk pengujian lokal gunakan ngrok / cloudflared tunnel (contoh: https://xxxx.ngrok-free.app/api/webhooks/google-form)
  var webhookUrl = "http://127.0.0.1:8000/api/webhooks/google-form";

  var options = {
    method: "post",
    contentType: "application/json",
    payload: JSON.stringify(payload),
    muteHttpExceptions: true
  };

  try {
    var response = UrlFetchApp.fetch(webhookUrl, options);
    Logger.log("Response: " + response.getContentText());
  } catch (error) {
    Logger.log("Error posting to webhook: " + error.toString());
  }
}
```

---

## 2. Mengaktifkan Trigger di Apps Script
1. Di panel kiri Apps Script, klik ikon **Triggers** (gambar jam alarm).
2. Klik tombol **Add Trigger** di kanan bawah.
3. Konfigurasi:
   - *Choose which function to run*: `onFormSubmit`
   - *Select event source*: `From form` (atau `From spreadsheet`)
   - *Select event type*: `On form submit`
4. Klik **Save** dan setujui izin akses Google.

Setiap ada pelamar baru yang mengisi Google Form, data akan otomatis masuk ke database MySQL `db_perekrutan` dengan status `New Application` secara instan!
