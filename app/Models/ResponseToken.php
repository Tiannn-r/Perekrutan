<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_id',
        'token',
        'sudah_dipakai',
        'kedaluwarsa_at',
    ];

    protected $casts = [
        'sudah_dipakai' => 'boolean',
        'kedaluwarsa_at' => 'datetime',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }

    public function isValid(): bool
    {
        if ($this->sudah_dipakai) {
            return false;
        }

        if ($this->kedaluwarsa_at && now()->isAfter($this->kedaluwarsa_at)) {
            return false;
        }

        // PRD BR-04: Tautan respon hanya berlaku sampai jadwal wawancara berlangsung
        if ($this->interview && $this->interview->tanggal_waktu && now()->isAfter($this->interview->tanggal_waktu)) {
            return false;
        }

        return true;
    }
}
