<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'grup',
        'keterangan',
    ];

    /**
     * Default system configurations managed by Super Admin
     */
    public static function defaultSettings(): array
    {
        return [
            'default_interviewer' => [
                'value' => 'Tim HR Nanggala Kencana Rekatama Indonesia',
                'grup' => 'penjadwalan',
                'keterangan' => 'Nama pewawancara default saat HR membuat jadwal wawancara baru',
            ],
            'default_duration' => [
                'value' => '45',
                'grup' => 'penjadwalan',
                'keterangan' => 'Durasi default wawancara dalam satuan menit',
            ],
            'default_location' => [
                'value' => 'Gedung Kantor Nanggala Kencana Rekatama Indonesia, Ruang Meeting Alpha Lt. 3, Jakarta',
                'grup' => 'penjadwalan',
                'keterangan' => 'Alamat & ruangan default untuk sesi wawancara tatap muka (offline)',
            ],
            'default_meet_prefix' => [
                'value' => 'https://meet.google.com/nkri-interview',
                'grup' => 'penjadwalan',
                'keterangan' => 'Tautan Google Meet default untuk sesi wawancara online',
            ],
            'default_deadline_hours' => [
                'value' => '24',
                'grup' => 'penjadwalan',
                'keterangan' => 'Batas waktu respon konfirmasi kehadiran kandidat (dalam jam sebelum jadwal)',
            ],
            'default_max_reschedule' => [
                'value' => '1',
                'grup' => 'penjadwalan',
                'keterangan' => 'Maksimal kuota permohonan reschedule kandidat per tahapan wawancara (Aturan BR-06)',
            ],
            'company_name' => [
                'value' => 'Nanggala Kencana Rekatama Indonesia',
                'grup' => 'perusahaan',
                'keterangan' => 'Nama resmi perusahaan',
            ],
            'company_email' => [
                'value' => 'recruitment@nkri.co.id',
                'grup' => 'perusahaan',
                'keterangan' => 'Email resmi tim rekrutmen pengirim notifikasi',
            ],
        ];
    }

    /**
     * Get setting value by key with fallback
     */
    public static function getVal(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if ($setting) {
            return $setting->value;
        }

        $defaults = self::defaultSettings();
        if (isset($defaults[$key])) {
            return $defaults[$key]['value'];
        }

        return $default;
    }

    /**
     * Set setting value by key
     */
    public static function setVal(string $key, $value, ?string $grup = 'general', ?string $keterangan = null): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'grup' => $grup ?: 'general',
                'keterangan' => $keterangan,
            ]
        );
    }
}
