<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignatoryDocumentDefault extends Model
{
    protected $table = 'signatory_document_defaults';

    /**
     * Document types with a centrally configured signatory preset. The
     * 'fund' type has no backend snapshot write (fund prints are rendered
     * client-side), but it shares this configuration through the API.
     */
    public const DOCUMENT_TYPES = ['swa', 'dtr', 'certification', 'fund'];

    protected $fillable = [
        'document_type',
        'office_head_signatory_id',
        'signatory_titles',
        'signatory_name_underline',
        'signatory_show_designation',
        'signatory_show_office',
        'signatory_info_order',
    ];

    protected $casts = [
        'signatory_titles' => 'array',
        'signatory_name_underline' => 'boolean',
        'signatory_show_designation' => 'boolean',
        'signatory_show_office' => 'boolean',
    ];

    public function officeHeadSignatory(): BelongsTo
    {
        return $this->belongsTo(Signatory::class, 'office_head_signatory_id');
    }

    /**
     * The API shape for one document type (D in the signatory-defaults
     * contract). office_head reflects the stored configuration only: null
     * when the row has no office head, even if Part A heads exist (the
     * generation-time fallback to the first Part A head is not applied
     * here, so the Settings UI edits stored state, not derived state).
     */
    public function toApiArray(): array
    {
        $head = $this->officeHeadSignatory;

        return [
            'document_type' => $this->document_type,
            'office_head_signatory_id' => $this->office_head_signatory_id,
            'office_head' => $head ? [
                'id' => $head->id,
                'name' => $head->name,
                'office' => $head->office,
                'titles' => array_values(array_filter($head->title ?? [])),
            ] : null,
            'signatory_titles' => $this->signatory_titles,
            'signatory_name_underline' => (bool) $this->signatory_name_underline,
            'signatory_show_designation' => (bool) $this->signatory_show_designation,
            'signatory_show_office' => (bool) $this->signatory_show_office,
            'signatory_info_order' => $this->signatory_info_order,
        ];
    }

    /**
     * The full GET/PUT response payload: every document type present,
     * null where no default row exists yet.
     */
    public static function apiPayload(): array
    {
        $rows = static::query()
            ->with('officeHeadSignatory')
            ->whereIn('document_type', self::DOCUMENT_TYPES)
            ->get()
            ->keyBy('document_type');

        $defaults = [];

        foreach (self::DOCUMENT_TYPES as $type) {
            $defaults[$type] = $rows->has($type) ? $rows->get($type)->toApiArray() : null;
        }

        return ['defaults' => $defaults];
    }

    /**
     * The office signatory resolution matches against, for a document
     * subject: an employee's office of assignment when one is set, else
     * the employee's regular office; any other subject's office. Null
     * when the subject carries no office at all.
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

    private static function officesMatch(?string $a, ?string $b): bool
    {
        $a = trim((string) $a);
        $b = trim((string) $b);

        return $a !== '' && $b !== '' && mb_strtolower($a) === mb_strtolower($b);
    }

    /**
     * Resolve a document type to the 8 shared snapshot columns
     * (HasSignatorySnapshot), frozen into the document at creation time.
     *
     * Name, office and titles come from the live Signatory row at
     * generation time, never from client input. The office head is
     * picked by office first: when $office is given, the first Part A
     * office head whose office matches it (case-insensitive, trimmed).
     * Only when no head matches (or no office is given) does the
     * configured default head apply, then the first Part A office head
     * (fallback when no default row exists, or when the configured head
     * was removed).
     * - Titles are the configured subset intersected with the head's
     *   current titles when the configured head is the resolved head;
     *   otherwise (no configured subset, or a different head resolved)
     *   all of the resolved head's current titles.
     * - Display flags come from the default row when one exists, else
     *   underline false, show designation true, show office true,
     *   designation_first.
     *
     * Returns null when no Part A office head exists at all; callers must
     * refuse generation in that case (there is no signatory to freeze).
     */
    public static function resolveSnapshot(string $type, ?string $office = null): ?array
    {
        $default = static::query()
            ->with('officeHeadSignatory')
            ->where('document_type', $type)
            ->first();

        $office = trim((string) $office);

        $head = null;

        if ($office !== '') {
            $head = Signatory::query()
                ->where('part', 'A')
                ->orderBy('id')
                ->get()
                ->first(fn (Signatory $candidate) => self::officesMatch($candidate->office, $office));
        }

        $head ??= $default?->officeHeadSignatory
            ?? Signatory::query()->where('part', 'A')->orderBy('id')->first();

        if ($head === null) {
            return null;
        }

        $liveTitles = collect($head->title ?? [])
            ->map(fn ($title) => trim((string) $title))
            ->filter()
            ->values();

        if (
            $default !== null
            && $default->office_head_signatory_id == $head->id
            && $default->signatory_titles !== null
        ) {
            $titles = $liveTitles
                ->filter(fn ($title) => collect($default->signatory_titles)->contains($title))
                ->values()
                ->all();
        } else {
            $titles = $liveTitles->all();
        }

        return [
            'office_head_signatory_id' => $head->id,
            'signatory_name' => $head->name,
            'signatory_office' => $head->office,
            'signatory_titles' => $titles,
            'signatory_name_underline' => $default !== null
                ? (bool) $default->signatory_name_underline
                : false,
            'signatory_show_designation' => $default !== null
                ? (bool) $default->signatory_show_designation
                : true,
            'signatory_show_office' => $default !== null
                ? (bool) $default->signatory_show_office
                : true,
            'signatory_info_order' => $default !== null
                && $default->signatory_info_order === 'office_first'
                ? 'office_first'
                : 'designation_first',
        ];
    }
}
