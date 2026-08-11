<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtrReportDailyValue extends Model
{
    protected $fillable = [
        'dtr_report_id',
        'work_date',
        'am_arrival',
        'am_departure',
        'pm_arrival',
        'pm_departure',
        'undertime_hours',
        'undertime_minutes',
        'travel_label',
    ];

    protected $casts = [
        'work_date' => 'date',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(DtrReport::class, 'dtr_report_id');
    }
}
