<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CalendarEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_date',
        'title',
        'event_type',
        'description',
        'is_system',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->created_by = $model->created_by ?? Auth::id();
            $model->updated_by = Auth::id();
        });

        static::updating(function (self $model) {
            $model->updated_by = Auth::id();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
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
