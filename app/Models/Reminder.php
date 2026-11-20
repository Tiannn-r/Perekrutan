<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_id',
        'jenis',
        'mode',
        'subjek',
        'isi',
        'dikirim_oleh',
        'dikirim_at',
        'status_kirim',
    ];

    protected $casts = [
        'dikirim_at' => 'datetime',
    ];

    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class);
    }
}
