<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SignatoryDocumentDefault;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SignatoryDefaultController extends Controller
{
    /**
     * Central signatory presets, one per document type. Types with no
     * configured row come back as null so clients can distinguish
     * "not configured" (generation falls back to the first Part A head)
     * from a configured preset.
     */
    public function index(): JsonResponse
    {
        return response()->json(SignatoryDocumentDefault::apiPayload());
    }

    /**
     * Upsert only the document types present in the payload; absent types
     * are left untouched. Within a provided type, only the fields present
     * in that type's object are written, so a partial update never resets
     * the fields it omits. Client-supplied signatory fields on document
     * generation are ignored; this endpoint is the only writer of presets.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'defaults' => ['required', 'array'],
            'defaults.*' => ['nullable', 'array'],
            'defaults.*.office_head_signatory_id' => [
                'nullable',
                Rule::exists('signatories', 'id')->where('part', 'A'),
            ],
            'defaults.*.signatory_titles' => ['nullable', 'array'],
            'defaults.*.signatory_titles.*' => ['string', 'max:255'],
            'defaults.*.signatory_name_underline' => ['nullable', 'boolean'],
            'defaults.*.signatory_show_designation' => ['nullable', 'boolean'],
            'defaults.*.signatory_show_office' => ['nullable', 'boolean'],
            'defaults.*.signatory_info_order' => ['nullable', Rule::in(['designation_first', 'office_first'])],
        ]);

        $unknown = array_diff(
            array_keys($validated['defaults']),
            SignatoryDocumentDefault::DOCUMENT_TYPES
        );

        if ($unknown !== []) {
            throw ValidationException::withMessages([
                'defaults' => 'Unknown document type(s): ' . implode(', ', $unknown) . '.',
            ]);
        }

        $updatable = [
            'office_head_signatory_id',
            'signatory_titles',
            'signatory_name_underline',
            'signatory_show_designation',
            'signatory_show_office',
            'signatory_info_order',
        ];

        DB::transaction(function () use ($validated, $updatable) {
            foreach ($validated['defaults'] as $type => $fields) {
                $attributes = [];

                foreach ($updatable as $field) {
                    if (array_key_exists($field, $fields ?? [])) {
                        $attributes[$field] = $fields[$field];
                    }
                }

                SignatoryDocumentDefault::updateOrCreate(
                    ['document_type' => $type],
                    $attributes
                );
            }
        });

        return response()->json(SignatoryDocumentDefault::apiPayload());
    }
}
