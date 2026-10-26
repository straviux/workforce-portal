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
     * Document scopes a Part A title can render in. A title ticks all
     * scopes that apply; fund titles split by document (CWA vs Payroll)
     * because the Part A block prints in both.
     */
    public const TITLE_SCOPES = ['swa', 'dtr', 'certification', 'cwa', 'payroll'];

    public const TITLE_SCOPE_LABELS = [
        'swa' => 'SWA',
        'dtr' => 'DTR',
        'certification' => 'Certification',
        'cwa' => 'CWA',
        'payroll' => 'Payroll',
    ];

    /**
     * This head's titles as plain texts for a document scope, or all of
     * them when no scope is given (admin views, or parts whose titles
     * are not scoped). Part A titles are stored as { text, scopes }
     * objects; legacy plain-string titles render everywhere, and an
     * unknown scope renders everything rather than nothing.
     */
    public function titlesForScope(?string $scope = null): array
    {
        $entries = collect($this->title ?? [])
            ->map(function ($title) {
                if (is_string($title)) {
                    return ['text' => trim($title), 'scopes' => self::TITLE_SCOPES];
                }

                return [
                    'text' => trim((string) ($title['text'] ?? '')),
                    'scopes' => is_array($title['scopes'] ?? null) && $title['scopes'] !== []
                        ? array_values(array_intersect($title['scopes'], self::TITLE_SCOPES))
                        : self::TITLE_SCOPES,
                ];
            })
            ->filter(fn (array $entry) => $entry['text'] !== '');

        if ($scope !== null && in_array($scope, self::TITLE_SCOPES, true)) {
            $entries = $entries->filter(fn (array $entry) => in_array($scope, $entry['scopes'], true));
        }

        return $entries->map(fn (array $entry) => $entry['text'])->values()->all();
    }

    /**
     * Descriptions for each part. Single source of truth for part labels:
     * the Settings UI and fund-print flows consume these via the
     * signatories API response instead of hardcoding their own copies.
     */
    public static array $partLabels = [
        'A' => 'Part A — Office Head (Prepared by / Verified)',
        'B' => 'Part B — Accountant (Certified correct)',
        'C' => 'Part C — Treasurer (Funds available)',
        'D' => 'Part D — Governor (Approved for payment)',
    ];

    /**
     * Parts that must each hold exactly one row for fund documents to print.
     */
    public const SINGLE_ROW_PARTS = ['B', 'C', 'D'];

    /**
     * Whether the signatory configuration is complete: at least one Part A
     * office head plus exactly one row for each of Parts B, C and D.
     *
     * Returns ['complete' => bool, 'missing' => string[]] where 'missing'
     * lists the part codes with no row ('A' when no office head exists).
     * A duplicate B/C/D row also counts as incomplete: it means the
     * configuration predates (or bypassed) the uniqueness enforcement and
     * the live fund-print lookup would silently pick only the first row.
     */
    public static function configurationStatus(): array
    {
        $counts = static::query()
            ->selectRaw('part, COUNT(*) as total')
            ->whereIn('part', ['A', 'B', 'C', 'D'])
            ->groupBy('part')
            ->pluck('total', 'part');

        $missing = [];

        if (($counts['A'] ?? 0) < 1) {
            $missing[] = 'A';
        }

        foreach (self::SINGLE_ROW_PARTS as $part) {
            if (($counts[$part] ?? 0) !== 1) {
                $missing[] = $part;
            }
        }

        return ['complete' => $missing === [], 'missing' => $missing];
    }

    public static function configurationComplete(): bool
    {
        return static::configurationStatus()['complete'];
    }
}
