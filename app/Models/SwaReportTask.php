<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SwaReportTask extends Model
{
    protected $fillable = [
        'swa_report_id',
        'source_task_id',
        'task_name',
        'task_type',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(SwaReport::class, 'swa_report_id');
    }

    public function sourceTask(): BelongsTo
    {
        return $this->belongsTo(SwaTask::class, 'source_task_id');
    }

    public function dailyValues(): HasMany
    {
        return $this->hasMany(SwaReportTaskDailyValue::class)->orderBy('work_date');
    }
}
