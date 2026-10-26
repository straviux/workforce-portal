<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\DtrReport;
use App\Models\Signatory;
use App\Models\SwaReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SignatoryController extends Controller
{
    /**
     * Return all signatories, plus the part labels (single source of truth,
     * from Signatory::$partLabels) and the configuration-completeness check
     * so clients never hardcode labels or re-derive completeness.
     * Part A may return multiple rows; B/C/D return a single row each (or null).
     */
    public function index(): JsonResponse
    {
        $all = Signatory::orderBy('part')->orderBy('id')->get();

        return response()->json([
            'data' => $all->values(),
            'part_labels' => Signatory::$partLabels,
            'configuration' => Signatory::configurationStatus(),
        ]);
    }

    /**
     * Upsert B, C, D signatories (single per part).
     *
     * Uniqueness for B/C/D is enforced by the database (unique index on the
     * generated bcd_part column); the distinct rule below additionally
     * rejects a payload that names the same part twice, which updateOrCreate
     * would otherwise silently collapse into one row.
     */
    public function upsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'signatories'           => 'required|array',
            'signatories.*.part'    => 'required|in:B,C,D|distinct',
            'signatories.*.name'    => 'required|string|max:255',
            'signatories.*.office'  => 'nullable|string|max:255',
            'signatories.*.title'   => 'nullable|array',
            'signatories.*.title.*' => 'string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['signatories'] as $row) {
                Signatory::updateOrCreate(
                    ['part' => $row['part']],
                    ['name' => $row['name'], 'office' => $row['office'] ?? null, 'title' => $row['title'] ?? null]
                );
            }
        });

        return response()->json([
            'message' => 'Signatories saved.',
            'configuration' => Signatory::configurationStatus(),
        ]);
    }

    /**
     * Add a new Part A (Office Head) entry. Titles are { text, scopes }
     * objects: each title ticks the documents it renders in.
     */
    public function storeOfficeHead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'office'  => 'nullable|string|max:255',
            'title'   => 'nullable|array',
            'title.*' => 'array',
            'title.*.text' => 'required|string|max:255',
            'title.*.scopes' => 'required|array|min:1',
            'title.*.scopes.*' => Rule::in(Signatory::TITLE_SCOPES),
        ]);

        $signatory = Signatory::create([
            'part'   => 'A',
            'name'   => $validated['name'],
            'office' => $validated['office'] ?? null,
            'title'  => self::normalizeTitles($validated['title'] ?? null),
        ]);

        return response()->json(['data' => $signatory], 201);
    }

    /**
     * Update a Part A entry.
     */
    public function updateOfficeHead(Request $request, int $id): JsonResponse
    {
        $signatory = Signatory::where('part', 'A')->findOrFail($id);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'office'  => 'nullable|string|max:255',
            'title'   => 'nullable|array',
            'title.*' => 'array',
            'title.*.text' => 'required|string|max:255',
            'title.*.scopes' => 'required|array|min:1',
            'title.*.scopes.*' => Rule::in(Signatory::TITLE_SCOPES),
        ]);

        $signatory->update([
            'name'   => $validated['name'],
            'office' => $validated['office'] ?? null,
            'title'  => self::normalizeTitles($validated['title'] ?? null),
        ]);

        return response()->json(['data' => $signatory]);
    }

    /** Trim texts, drop blank titles, keep scopes in canonical order. */
    private static function normalizeTitles(?array $titles): ?array
    {
        if ($titles === null) {
            return null;
        }

        $normalized = [];

        foreach ($titles as $title) {
            $text = trim((string) ($title['text'] ?? ''));

            if ($text === '') {
                continue;
            }

            $scopes = array_values(array_intersect(
                $title['scopes'] ?? [],
                Signatory::TITLE_SCOPES
            ));

            $normalized[] = ['text' => $text, 'scopes' => $scopes];
        }

        return $normalized;
    }

    /**
     * Delete a Part A entry.
     *
     * Refused when any document still references the office head: deleting
     * would null the live FK (nullOnDelete) while the frozen snapshot text
     * stays, silently rewriting the document's provenance on the next
     * re-save. References are counted including soft-deleted documents,
     * since their history is still on file.
     */
    public function destroyOfficeHead(int $id): JsonResponse
    {
        $signatory = Signatory::where('part', 'A')->findOrFail($id);

        $references = [
            'certification' => Certification::withTrashed()->where('office_head_signatory_id', $id)->count(),
            'SWA report' => SwaReport::withTrashed()->where('office_head_signatory_id', $id)->count(),
            'DTR report' => DtrReport::withTrashed()->where('office_head_signatory_id', $id)->count(),
        ];
        $references = array_filter($references);

        if ($references !== []) {
            $detail = collect($references)
                ->map(fn (int $count, string $label) => "{$count} {$label}" . ($count === 1 ? '' : 's'))
                ->join(', ');

            return response()->json([
                'message' => "Cannot remove this office head: it is still referenced by {$detail}. "
                    . 'Reassign or delete those documents first.',
            ], 422);
        }

        $signatory->delete();

        return response()->json(['message' => 'Office head removed.']);
    }
}
