<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'interview_id',
        'penerima',
        'tipe',
        'status_kirim',
        'pesan_error',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }
}
