<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        'employee_no',
        'first_name',
        'middle_name',
        'last_name',
        'name_extension',
        'address',
        'office',
        'designation',
        'agency',
        'amount',
        'responsibility_center_id',
        'employee_type',
        'contract_ref_no',
        'swa',
        'atm_account_no',
        'monthly_compensation',
        'deduction_sss',
        'deduction_philhealth',
        'deduction_hdmf',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} " . ($this->middle_name ? "{$this->middle_name} " : '') . "{$this->last_name}" . ($this->name_extension ? " {$this->name_extension}" : ''));
    }

    protected $casts = [
        'swa'                  => 'boolean',
        'is_active'            => 'boolean',
        'amount'               => 'decimal:2',
        'monthly_compensation' => 'decimal:2',
        'deduction_sss'        => 'decimal:2',
        'deduction_philhealth' => 'decimal:2',
        'deduction_hdmf'       => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        });
        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }

    public function responsibilityCenter()
    {
        return $this->belongsTo(ResponsibilityCenter::class, 'responsibility_center_id');
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
