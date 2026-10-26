<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OfficeController extends Controller
{
    /**
     * Main offices with their assignments. Only two levels exist: a
     * main office (no parent) and the assignments under it (specific
     * offices/programs). Assignments never have offices under them.
     */
    public function index(): JsonResponse
    {
        return response()->json(Office::tree());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', Rule::exists('offices', 'id')],
            'office_head_signatory_id' => [
                'nullable',
                Rule::exists('signatories', 'id')->where('part', 'A'),
            ],
        ]);

        $this->guardDuplicateName($validated['name']);
        $this->guardParent($validated['parent_id'] ?? null);

        Office::create([
            'name' => trim($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
            'office_head_signatory_id' => $validated['office_head_signatory_id'] ?? null,
        ]);

        return response()->json(Office::tree());
    }

    public function update(Request $request, Office $office): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', Rule::exists('offices', 'id')],
            'office_head_signatory_id' => [
                'nullable',
                Rule::exists('signatories', 'id')->where('part', 'A'),
            ],
        ]);

        $this->guardDuplicateName($validated['name'], $office->id);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId !== null && $parentId == $office->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'An office cannot be under itself.',
            ]);
        }

        // A main office that already has assignments cannot move under
        // another main office: that would make three levels.
        if ($parentId !== null && $office->children()->exists()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Remove the assignments under ' . $office->name . ' before moving it under another main office.',
            ]);
        }

        $this->guardParent($parentId);

        $office->update([
            'name' => trim($validated['name']),
            'parent_id' => $parentId,
            'office_head_signatory_id' => $validated['office_head_signatory_id'] ?? null,
        ]);

        return response()->json(Office::tree());
    }

    public function destroy(Office $office): JsonResponse
    {
        $childCount = $office->children()->count();

        if ($childCount > 0) {
            throw ValidationException::withMessages([
                'office' => $office->name . ' still has ' . $childCount . ' assignment(s) under it. Remove them first.',
            ]);
        }

        $office->delete();

        return response()->json(Office::tree());
    }

    private function guardDuplicateName(string $name, ?int $exceptId = null): void
    {
        $query = Office::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))]);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => 'An office named ' . trim($name) . ' already exists.',
            ]);
        }
    }

    private function guardParent(?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        $parent = Office::query()->find($parentId);

        if ($parent !== null && $parent->parent_id !== null) {
            throw ValidationException::withMessages([
                'parent_id' => 'An assignment cannot have offices under it. Pick a main office as the parent.',
            ]);
        }
    }
}
