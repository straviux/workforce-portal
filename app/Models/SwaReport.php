<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SwaReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'module_type',
        'subject_type',
        'subject_id',
        'generated_by',
        'office_head_signatory_id',
        'signatory_name',
        'signatory_office',
        'signatory_titles',
        'period_start_date',
        'period_end_date',
        'work_days',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'work_days' => 'array',
        'signatory_titles' => 'array',
        'period_start_date' => 'date',
        'period_end_date' => 'date',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->generated_by = $model->generated_by ?? Auth::id();
            $model->created_by = $model->created_by ?? Auth::id();
            $model->updated_by = Auth::id();
        });

        static::updating(function (self $model) {
            $model->updated_by = Auth::id();
        });
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(SwaReportTask::class)->orderBy('sort_order');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by')->select(['id', 'name']);
    }

    public function officeHeadSignatory(): BelongsTo
    {
        return $this->belongsTo(Signatory::class, 'office_head_signatory_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
