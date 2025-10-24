<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes; // Tambahkan SoftDeletes

    protected $table = 'reports'; // Nama tabel di database

    protected $fillable = [
        'report_type_id',
        'user_id',
        'data',
        'status',
        'last_edited_by_user_id',
        'deleted_by_user_id',
    ];

    protected $casts = [
        'data' => 'array', // Mengubah kolom 'data' (JSON) menjadi array PHP secara otomatis
    ];

    /**
     * Get the ReportType that owns the Report.
     */
    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    /**
     * Get the user who created the Report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who last edited the Report.
     */
    public function lastEditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by_user_id');
    }

    /**
     * Get the user who deleted the Report.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }
}
