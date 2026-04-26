<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwaReportTaskDailyValue extends Model
{
    protected $table = 'swa_report_task_daily_values';

    protected $fillable = [
        'swa_report_task_id',
        'work_date',
        'numeric_value',
        'mark_value',
    ];

    protected $casts = [
        'work_date' => 'date',
        'numeric_value' => 'decimal:2',
    ];

    public function reportTask(): BelongsTo
    {
        return $this->belongsTo(SwaReportTask::class, 'swa_report_task_id');
    }
}
