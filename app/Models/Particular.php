<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Particular extends Model
{
    protected $table = 'particulars';

    protected $fillable = [
        'responsibility_center_id',
        'name',
        'account_code',
        'allotment',
        'date_approved',
        'date_expired',
    ];

    protected $casts = [
        'allotment' => 'decimal:2',
        'date_approved' => 'date',
        'date_expired' => 'date',
    ];

    public function responsibilityCenter()
    {
        return $this->belongsTo(ResponsibilityCenter::class, 'responsibility_center_id');
    }
}
