<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class EmployeeFundTransaction extends Model
{
    use SoftDeletes;

    protected $table = 'employee_fund_transactions';

    protected $fillable = [
        'transaction_id',
        'employee_type',
        'employee_record_id',
        'payee_name',
        'payee_address',
        'agency',
        'office',
        'responsibility_center',
        'particulars_id',
        'account_code',
        'particulars_name',
        'particulars_description',
        'amount',
        'fiscal_year',
        'disbursement_type',
        'explanation',
        'obr_type',
        'obr_no',
        'dv_no',
        'date_obligated',
        'date_from',
        'date_to',
        'transaction_status',
        'remarks',
        'upload_token',
        'upload_token_expires_at',
        'employee_id',
        'contract_ref_no',
        'swa',
        'atm_account_no',
        'monthly_compensation',
        'deduction_sss',
        'deduction_philhealth',
        'deduction_hdmf',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'swa' => 'boolean',
        'amount' => 'decimal:2',
        'monthly_compensation' => 'decimal:2',
        'deduction_sss' => 'decimal:2',
        'deduction_philhealth' => 'decimal:2',
        'deduction_hdmf' => 'decimal:2',
        'upload_token_expires_at' => 'datetime',
        'date_obligated' => 'date',
        'date_from'      => 'date',
        'date_to'        => 'date',
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

    public function responsibilityCenter()
    {
        return $this->belongsTo(ResponsibilityCenter::class, 'responsibility_center', 'id');
    }

    public function employees()
    {
        return $this->hasMany(FundTransactionEmployee::class, 'fund_transaction_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function employeeRecord()
    {
        return $this->belongsTo(Employee::class, 'employee_record_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
