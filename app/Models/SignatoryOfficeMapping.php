<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignatoryOfficeMapping extends Model
{
    protected $table = 'signatory_office_mappings';

    protected $fillable = [
        'office',
        'office_head_signatory_id',
    ];

    public function officeHeadSignatory(): BelongsTo
    {
        return $this->belongsTo(Signatory::class, 'office_head_signatory_id');
    }

    public function toApiArray(): array
    {
        $head = $this->officeHeadSignatory;

        return [
            'id' => $this->id,
            'office' => $this->office,
            'office_head_signatory_id' => $this->office_head_signatory_id,
            'office_head' => $head ? [
                'id' => $head->id,
                'name' => $head->name,
                'office' => $head->office,
                'titles' => array_values(array_filter($head->title ?? [])),
            ] : null,
        ];
    }

    /**
     * The live Part A head explicitly assigned to an office, or null when
     * no assignment exists for it (or the assigned head was removed).
     * Matching is case-insensitive on trimmed office names.
     */
    public static function mappedHeadForOffice(string $office): ?Signatory
    {
        $office = trim($office);

        if ($office === '') {
            return null;
        }

        $mapping = static::query()
            ->whereRaw('LOWER(office) = ?', [mb_strtolower($office)])
            ->first();

        return $mapping?->officeHeadSignatory;
    }
}
