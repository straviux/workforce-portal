<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'office_head_signatory_id',
    ];

    /** A null parent means a main office; a set parent means an assignment (a specific office/program) under that main office. */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function officeHeadSignatory(): BelongsTo
    {
        return $this->belongsTo(Signatory::class, 'office_head_signatory_id');
    }

    public function toApiArray(bool $withChildren = true): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'office_head_signatory_id' => $this->office_head_signatory_id,
            'office_head' => $this->officeHeadSignatory ? [
                'id' => $this->officeHeadSignatory->id,
                'name' => $this->officeHeadSignatory->name,
                'office' => $this->officeHeadSignatory->office,
                'titles' => array_values(array_filter($this->officeHeadSignatory->title ?? [])),
            ] : null,
            'children' => $withChildren
                ? $this->children->map(fn (self $child) => $child->toApiArray(false))->all()
                : [],
        ];
    }

    /** Main offices with their assignments, each ordered by name, for the Offices page and the signatory payload. */
    public static function tree(): array
    {
        return static::query()
            ->with(['officeHeadSignatory', 'children.officeHeadSignatory'])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get()
            ->map(fn (self $office) => $office->toApiArray())
            ->all();
    }

    /**
     * The live Part A head for an office name, or null when the office
     * is unknown or carries no head anywhere up its chain: the office's
     * own head first, then its main office's head. Matching is
     * case-insensitive on trimmed names.
     */
    public static function headForOffice(string $office): ?Signatory
    {
        $office = trim($office);

        if ($office === '') {
            return null;
        }

        $record = static::query()
            ->with(['officeHeadSignatory', 'parent.officeHeadSignatory'])
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($office)])
            ->first();

        if ($record === null) {
            return null;
        }

        return $record->officeHeadSignatory ?? $record->parent?->officeHeadSignatory;
    }
}
