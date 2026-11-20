<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'nama',
        'subjek',
        'isi_pesan',
        'petunjuk_persiapan',
        'ketentuan_pakaian',
    ];

    /**
     * Official default templates
     */
    public static function defaultTemplates(): array
    {
        return [
            'reminder_online' => [
                'nama' => 'Pengingat Wawancara Online (Google Meet)',
                'subjek' => 'Pengingat Wawancara Online: {posisi} - {nama} (Nanggala Kencana Rekatama Indonesia)',
                'isi_pesan' => "Halo {nama},\n\nIni adalah pengingat resmi bahwa jadwal wawancara online Anda untuk posisi {posisi} di Nanggala Kencana Rekatama Indonesia akan dilaksanakan pada:\n\n• Waktu: {jadwal}\n• Pewawancara: {pewawancara}\n• Media: Google Meet (Virtual)\n• Link Rapat: {link_meeting}\n\nInstruksi Kesiapan Wawancara:\n{persiapan}\n\nKetentuan Pakaian:\n{pakaian}\n\n{catatan}\n\nMohon hadir dan bersiap di Google Meet 5-10 menit sebelum waktu wawancara dimulai.\n\nSalam,\nTim Rekrutmen Nanggala Kencana Rekatama Indonesia",
                'petunjuk_persiapan' => "1. Pastikan koneksi internet stabil & ruangan tenang/bebas gangguan.\n2. Siapkan laptop/PC dengan webcam dan mikrofon aktif.\n3. Siapkan file CV dan portofolio digital yang siap dipresentasikan (screen share).\n4. Bergabung ke link Google Meet 5-10 menit sebelum waktu wawancara dimulai.",
                'ketentuan_pakaian' => "Berpakaian kemeja formal / rapi dan sopan (standar wawancara kerja profesional).",
            ],
            'reminder_offline' => [
                'nama' => 'Pengingat Wawancara Offline (Tatap Muka)',
                'subjek' => 'Pengingat Wawancara Tatap Muka: {posisi} - {nama} (Nanggala Kencana Rekatama Indonesia)',
                'isi_pesan' => "Halo {nama},\n\nIni adalah pengingat resmi bahwa jadwal wawancara tatap muka Anda untuk posisi {posisi} di Nanggala Kencana Rekatama Indonesia akan dilaksanakan pada:\n\n• Waktu: {jadwal}\n• Pewawancara: {pewawancara}\n• Lokasi Kantor: {lokasi}\n\nDokumen & Perlengkapan yang Wajib Dibawa:\n{persiapan}\n\nKetentuan Pakaian:\n{pakaian}\n\n{catatan}\n\nMohon tiba di lokasi kantor 15 menit sebelum waktu wawancara untuk proses registrasi tamu di resepsionis lobi.\n\nSalam,\nTim Rekrutmen Nanggala Kencana Rekatama Indonesia",
                'petunjuk_persiapan' => "1. Berkas cetak CV & Portofolio terbaru (hardcopy rangkap 2).\n2. Kartu Tanda Pengenal fisik (KTP / SIM) untuk penukaran visitor pass di resepsionis lobi.\n3. Alat tulis pribadi (pulpen hitam) dan laptop jika ada pengujian teknis.\n4. Hadir di lokasi kantor 15 menit sebelum waktu wawancara untuk registrasi tamu.",
                'ketentuan_pakaian' => "Pakaian kerja formal: kemeja berkerah rapi, celana bahan panjang (non-jeans), dan sepatu tertutup pantofel.",
            ],
            'invitation' => [
                'nama' => 'Undangan Wawancara Kerja Baru',
                'subjek' => 'Undangan Wawancara: {posisi} - {nama} (Nanggala Kencana Rekatama Indonesia)',
                'isi_pesan' => "Halo {nama},\n\nTerima kasih atas minat dan lamaran Anda untuk posisi {posisi} di Nanggala Kencana Rekatama Indonesia. Berdasarkan hasil peninjauan awal berkas lamaran Anda, kami bermaksud mengundang Anda untuk mengikuti tahapan wawancara pada:\n\n• Waktu: {jadwal}\n• Pewawancara: {pewawancara}\n• Kategori: {kategori}\n• Batas Konfirmasi: {batas_konfirmasi}\n\nSilakan konfirmasikan kehadiran Anda melalui tautan portal resmi:\n{portal_url}\n\nSalam,\nTim Rekrutmen Nanggala Kencana Rekatama Indonesia",
                'petunjuk_persiapan' => "Silakan pelajari profil Nanggala Kencana Rekatama Indonesia dan siapkan dokumen pendukung.",
                'ketentuan_pakaian' => "Pakaian formal rapi dan sopan.",
            ],
        ];
    }

    /**
     * Get or create template by key with fallback to defaults
     */
    public static function getByKey(string $key): self
    {
        $template = self::where('key', $key)->first();
        if (!$template) {
            $defaults = self::defaultTemplates();
            if (isset($defaults[$key])) {
                $template = self::create(array_merge(['key' => $key], $defaults[$key]));
            }
        }
        return $template;
    }

    /**
     * Replace template variables with real data
     */
    public function render(array $vars): array
    {
        $subject = $this->subjek;
        $body = $this->isi_pesan;

        foreach ($vars as $varKey => $val) {
            $placeholder = '{' . $varKey . '}';
            $subject = str_replace($placeholder, (string)$val, $subject);
            $body = str_replace($placeholder, (string)$val, $body);
        }

        return [
            'subjek' => $subject,
            'isi_pesan' => $body,
        ];
    }
}
