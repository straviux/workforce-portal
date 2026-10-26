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
     * The office a document resolves against, for a document subject:
     * an employee's office of assignment when one is set, else the
     * employee's regular office; any other subject's office. Null when
     * the subject carries no office at all.
     */
    public static function effectiveOffice(Model $subject): ?string
    {
        if ($subject instanceof Employee) {
            $office = trim((string) ($subject->office_of_assignment ?? ''))
                ?: trim((string) ($subject->office ?? ''));
        } else {
            $office = trim((string) ($subject->office ?? ''));
        }

        return $office === '' ? null : $office;
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

    /**
     * Resolve an office name to the 8 shared snapshot columns
     * (HasSignatorySnapshot), frozen into the document at creation time.
     *
     * Name, office and titles come from the live Signatory row at
     * generation time, never from client input. The office head is
     * picked in this order:
     * 1. The head of the document's office in the Offices module: the
     *    assignment's own head, else its main office's head.
     * 2. The first Part A office head (fallback when the office is
     *    unknown or carries no head).
     * Titles are always all of the resolved head's current titles, and
     * the display flags are fixed (no underline, show designation and
     * office, designation first): per-document-type defaults were
     * deleted, so there is nothing left to configure per type.
     *
     * Returns null when no Part A office head exists at all; callers must
     * refuse generation in that case (there is no signatory to freeze).
     */
    public static function resolveSnapshot(?string $office = null): ?array
    {
        $office = trim((string) $office);

        $head = $office !== '' ? self::headForOffice($office) : null;
        $head ??= Signatory::query()->where('part', 'A')->orderBy('id')->first();

        if ($head === null) {
            return null;
        }

        $titles = collect($head->title ?? [])
            ->map(fn ($title) => trim((string) $title))
            ->filter()
            ->values()
            ->all();

        return [
            'office_head_signatory_id' => $head->id,
            'signatory_name' => $head->name,
            'signatory_office' => $head->office,
            'signatory_titles' => $titles,
            'signatory_name_underline' => false,
            'signatory_show_designation' => true,
            'signatory_show_office' => true,
            'signatory_info_order' => 'designation_first',
        ];
    }
}
