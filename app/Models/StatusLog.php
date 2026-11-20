<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'candidate_id',
        'interview_id',
        'dari_status',
        'ke_status',
        'aktor',
        'keterangan',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }
}
