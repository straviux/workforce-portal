<?php

namespace App\Models;

use App\Models\Concerns\HasSignatorySnapshot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Certification extends Model
{
    use HasSignatorySnapshot;
    use SoftDeletes;

    protected $fillable = [
        'certification_type',
        'subject_name',
        'subject_honorific',
        'subject_firstname',
        'subject_middlename',
        'subject_lastname',
        'designation',
        'office',
        'issued_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        });

        static::updating(function (self $model) {
            $model->updated_by = Auth::id();
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
