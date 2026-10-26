<?php

namespace App\Models\Concerns;

use App\Models\Signatory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared Part A (office head) snapshot columns.
 *
 * A snapshot freezes the chosen office head's name, office and titles onto
 * the document at save time, while office_head_signatory_id keeps the live
 * link back to the signatories row. All document models that carry a
 * snapshot use this trait so the column list and casts cannot drift apart.
 */
trait HasSignatorySnapshot
{
    public function initializeHasSignatorySnapshot(): void
    {
        $this->fillable = array_values(array_unique(array_merge($this->fillable, [
            'office_head_signatory_id',
            'signatory_name',
            'signatory_office',
            'signatory_titles',
            'signatory_name_underline',
            'signatory_show_designation',
            'signatory_show_office',
            'signatory_info_order',
        ])));

        $this->mergeCasts([
            'signatory_titles' => 'array',
            'signatory_name_underline' => 'boolean',
            'signatory_show_designation' => 'boolean',
            'signatory_show_office' => 'boolean',
        ]);
    }

    public function officeHeadSignatory(): BelongsTo
    {
        return $this->belongsTo(Signatory::class, 'office_head_signatory_id');
    }
}
