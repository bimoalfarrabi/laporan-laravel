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
        'approved_by_user_id',
        'approved_at',
        'rejected_by_user_id',
        'rejected_at',
    ];

    protected $casts = [
        'data' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::forceDeleting(function (Report $report) {
            // Get the schema to identify file fields
            $schema = $report->reportType->fields_schema ?? [];

            foreach ($schema as $field) {
                // Check if the field is a file type and if a file path exists in the data
                if ($field['type'] === 'file' && !empty($report->data[$field['name']])) {
                    $filePath = $report->data[$field['name']];
                    // Use Storage facade to delete the file from the public disk
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($filePath);
                }
            }
        });
    }
}
