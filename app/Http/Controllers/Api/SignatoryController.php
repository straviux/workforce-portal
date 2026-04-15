<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Signatory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignatoryController extends Controller
{
    /**
     * Return all signatories.
     * Part A may return multiple rows; B/C/D return a single row each (or null).
     */
    public function index(): JsonResponse
    {
        $all = Signatory::orderBy('part')->orderBy('id')->get();

        return response()->json(['data' => $all->values()]);
    }

    /**
     * Upsert B, C, D signatories (single per part).
     */
    public function upsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'signatories'           => 'required|array',
            'signatories.*.part'    => 'required|in:B,C,D',
            'signatories.*.name'    => 'required|string|max:255',
            'signatories.*.office'  => 'nullable|string|max:255',
            'signatories.*.title'   => 'nullable|array',
            'signatories.*.title.*' => 'string|max:255',
        ]);

        foreach ($validated['signatories'] as $row) {
            Signatory::updateOrCreate(
                ['part' => $row['part']],
                ['name' => $row['name'], 'office' => $row['office'] ?? null, 'title' => $row['title'] ?? null]
            );
        }

        return response()->json(['message' => 'Signatories saved.']);
    }

    /**
     * Add a new Part A (Office Head) entry.
     */
    public function storeOfficeHead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'office'  => 'nullable|string|max:255',
            'title'   => 'nullable|array',
            'title.*' => 'string|max:255',
        ]);

        $signatory = Signatory::create([
            'part'   => 'A',
            'name'   => $validated['name'],
            'office' => $validated['office'] ?? null,
            'title'  => $validated['title'] ?? null,
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
            'title.*' => 'string|max:255',
        ]);

        $signatory->update([
            'name'   => $validated['name'],
            'office' => $validated['office'] ?? null,
            'title'  => $validated['title'] ?? null,
        ]);

        return response()->json(['data' => $signatory]);
    }

    /**
     * Delete a Part A entry.
     */
    public function destroyOfficeHead(int $id): JsonResponse
    {
        $signatory = Signatory::where('part', 'A')->findOrFail($id);
        $signatory->delete();

        return response()->json(['message' => 'Office head removed.']);
    }
}
