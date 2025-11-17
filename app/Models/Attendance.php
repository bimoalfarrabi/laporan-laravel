<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'time_in',
        'time_out',
        'photo_in_path',
        'photo_out_path',
        'latitude_in',
        'longitude_in',
        'latitude_out',
        'longitude_out',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
