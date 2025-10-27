<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reports';

    protected $fillable = [
        'report_type_id',
        'user_id',
        'data',
        'status',
        'last_edited_by_user_id',
        'deleted_by_user_id',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lastEditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by_user_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }
}
