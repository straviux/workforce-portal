<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundTransactionEmployee extends Model
{
    protected $table = 'fund_transaction_employees';

    protected $fillable = [
        'fund_transaction_id',
        'employee_record_id',
        'payee_name',
        'payee_address',
        'office',
        'amount',
        'employee_id',
        'contract_ref_no',
        'swa',
        'atm_account_no',
        'monthly_compensation',
        'deduction_sss',
        'deduction_philhealth',
        'deduction_hdmf',
        'lost_hour_minutes',
    ];

    protected $casts = [
        'swa'                  => 'boolean',
        'amount'               => 'decimal:2',
        'monthly_compensation' => 'decimal:2',
        'deduction_sss'        => 'decimal:2',
        'deduction_philhealth' => 'decimal:2',
        'deduction_hdmf'       => 'decimal:2',
        'lost_hour_minutes'    => 'integer',
    ];

    public function fundTransaction()
    {
        return $this->belongsTo(EmployeeFundTransaction::class, 'fund_transaction_id');
    }

    public function employeeRecord()
    {
        return $this->belongsTo(Employee::class, 'employee_record_id');
    }
}
