<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'email',
        'telepon',
        'posisi',
        'berkas',
        'status',
    ];

    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    public function latestInterview()
    {
        return $this->hasOne(Interview::class)->latestOfMany();
    }

    public function statusLogs()
    {
        return $this->hasMany(StatusLog::class);
    }
}
