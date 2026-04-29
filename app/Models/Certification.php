<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Certification extends Model
{
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
        'office_head_signatory_id',
        'signatory_name',
        'signatory_office',
        'signatory_titles',
        'signatory_name_underline',
        'signatory_show_designation',
        'signatory_show_office',
        'signatory_info_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'signatory_titles' => 'array',
        'signatory_name_underline' => 'boolean',
        'signatory_show_designation' => 'boolean',
        'signatory_show_office' => 'boolean',
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

    public function officeHeadSignatory()
    {
        return $this->belongsTo(Signatory::class, 'office_head_signatory_id');
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
