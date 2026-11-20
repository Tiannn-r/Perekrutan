<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'tahap',
        'kategori',
        'tanggal_waktu',
        'durasi_menit',
        'interviewer',
        'link_meeting',
        'lokasi',
        'status',
        'jumlah_reschedule',
        'batas_respon',
        'catatan',
    ];

    protected $casts = [
        'tanggal_waktu' => 'datetime',
        'batas_respon' => 'datetime',
        'jumlah_reschedule' => 'integer',
        'durasi_menit' => 'integer',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function token()
    {
        return $this->hasOne(ResponseToken::class);
    }

    public function rescheduleRequests()
    {
        return $this->hasMany(RescheduleRequest::class);
    }

    public function latestRescheduleRequest()
    {
        return $this->hasOne(RescheduleRequest::class)->latestOfMany();
    }

    public function statusLogs()
    {
        return $this->hasMany(StatusLog::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }
}
