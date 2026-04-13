<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponsibilityCenter extends Model
{
    protected $table = 'responsibility_centers';

    protected $fillable = [
        'code',
        'name',
        'fiscal_year',
    ];

    public function particulars()
    {
        return $this->hasMany(Particular::class, 'responsibility_center_id');
    }
}
