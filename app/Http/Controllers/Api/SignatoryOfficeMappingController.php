<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SignatoryDocumentDefault;
use App\Models\SignatoryOfficeMapping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SignatoryOfficeMappingController extends Controller
{
    /**
     * Replace the whole office-assignment set in one transaction. The
     * Settings UI edits the list locally and saves once, so a full
     * replace keeps the stored set exactly equal to what the admin sees.
     * A row without a head, or two rows for the same office (compared
     * case-insensitively), is rejected rather than stored half-done.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mappings' => ['required', 'array'],
            'mappings.*.office' => ['required', 'string', 'max:255'],
            'mappings.*.office_head_signatory_id' => [
                'required',
                Rule::exists('signatories', 'id')->where('part', 'A'),
            ],
        ]);

        $seen = [];

        foreach ($validated['mappings'] as $mapping) {
            $key = mb_strtolower(trim($mapping['office']));

            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    'mappings' => 'Duplicate office assignment: ' . trim($mapping['office']) . '.',
                ]);
            }

            $seen[$key] = true;
        }

        DB::transaction(function () use ($validated) {
            SignatoryOfficeMapping::query()->delete();

            foreach ($validated['mappings'] as $mapping) {
                SignatoryOfficeMapping::create([
                    'office' => trim($mapping['office']),
                    'office_head_signatory_id' => $mapping['office_head_signatory_id'],
                ]);
            }
        });

        return response()->json(SignatoryDocumentDefault::apiPayload());
    }
}
