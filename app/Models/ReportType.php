<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReportType extends Model
{
    use HasFactory;

    protected $table = 'report_types';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'fields_schema',
        'is_active',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'fields_schema' => 'array', // mengubah JSON menjadi array otomatis
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reportType) {
            $reportType->slug = Str::slug($reportType->name);
        });

        static::updating(function ($reportType) {
            if ($reportType->isDirty('name')) {
                $reportType->slug = Str::slug($reportType->name);
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
