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
