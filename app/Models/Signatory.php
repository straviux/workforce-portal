<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    protected $table = 'signatories';

    protected $fillable = [
        'part',
        'name',
        'office',
        'title',
    ];

    protected $casts = [
        'title' => 'array',
    ];

    /**
     * Descriptions for each part.
     */
    public static array $partLabels = [
        'A' => 'Part A — Office Head (Prepared by / Verified)',
        'B' => 'Part B — Accountant (Certified correct)',
        'C' => 'Part C — Treasurer (Funds available)',
        'D' => 'Part D — Governor (Approved for payment)',
    ];
}
