<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RescheduleRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_id',
        'alasan',
        'saran_waktu',
        'status_persetujuan',
        'ditangani_oleh',
        'diajukan_at',
        'ditangani_at',
    ];

    protected $casts = [
        'diajukan_at' => 'datetime',
        'ditangani_at' => 'datetime',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }
}
