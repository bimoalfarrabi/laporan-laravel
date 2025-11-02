<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTypeField extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type_id',
        'label',
        'name',
        'type',
        'required',
        'order',
        'role_id',
    ];

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }
}