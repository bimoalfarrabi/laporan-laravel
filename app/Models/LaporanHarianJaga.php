<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanHarianJaga extends Model
{
    use HasFactory, SoftDeletes; // tambah SoftDeletes trait

    protected $table = 'laporan_harian_jaga';

    protected $fillable = [
        'user_id',
        'tanggal_jaga',
        'shift',
        'cuaca',
        'kejadian_menonjol',
        'catatan_serah_terima',
        'status',
    ];

    protected $casts = [
        'tanggal_jaga' => 'date', // Mengubah tanggal_jaga menjadi instance Carbon
    ];

    /**
     * Relasi ke model User (pengguna yang membuat laporan)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }
}
