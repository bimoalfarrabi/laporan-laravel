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
        'last_edited_by_user_id',
        'deleted_by_user_id',
    ];

    protected $casts = [
        'tanggal_jaga' => 'date', // Mengubah tanggal_jaga menjadi instance Carbon
    ];

    /**
     * User yang punya LaporanHarianJaga
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }

    /**
     * User yang terakhir mengedit laporanHarianJaga
     */
    public function lastEditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by_user_id');
    }

    /**
     * User yang menghapus laporanHarianJaga
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }
}
