<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNonRosCertificationRequest;
use App\Http\Requests\UpdateNonRosCertificationRequest;
use App\Models\Certification;
use App\Models\Signatory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CertificationController extends Controller
{
    public function nonRosIndex(Request $request): JsonResponse
    {
        try {
            $query = Certification::query()
                ->with('creator')
                ->where('certification_type', 'non_ros')
                ->latest();

            if ($search = trim((string) $request->get('search', ''))) {
                $query->where(function ($certificationQuery) use ($search) {
                    $certificationQuery->where('subject_name', 'like', "%{$search}%")
                        ->orWhere('subject_honorific', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%")
                        ->orWhere('office', 'like', "%{$search}%")
                        ->orWhere('signatory_name', 'like', "%{$search}%");
                });
            }

            $perPage = max(1, min((int) $request->get('per_page', 15), 50));
            $paginated = $query->paginate($perPage);

            return response()->json([
                'data' => $paginated->items(),
                'filtered_total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'office_heads' => $this->officeHeads(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error loading non-ROS certifications', ['error' => $exception->getMessage()]);

            return response()->json([
                'message' => 'Could not load certification records.',
            ], 500);
        }
    }

    public function storeNonRos(StoreNonRosCertificationRequest $request): JsonResponse
    {
        try {
            $certification = Certification::query()->create(
                $this->payloadWithSignatorySnapshot($request->validated())
            );

            $certification->load('creator');

            return response()->json([
                'data' => $certification,
                'message' => 'Certification saved.',
            ], 201);
        } catch (\Throwable $exception) {
            Log::error('Error saving non-ROS certification', ['error' => $exception->getMessage()]);

            return response()->json([
                'message' => 'Could not save certification.',
            ], 500);
        }
    }

    public function updateNonRos(UpdateNonRosCertificationRequest $request, int $id): JsonResponse
    {
        try {
            $certification = Certification::query()
                ->where('certification_type', 'non_ros')
                ->findOrFail($id);

            $certification->update($this->payloadWithSignatorySnapshot($request->validated()));
            $certification->load('creator');

            return response()->json([
                'data' => $certification,
                'message' => 'Certification updated.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error updating non-ROS certification', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json([
                'message' => 'Could not update certification.',
            ], 500);
        }
    }

    private function officeHeads()
    {
        return Signatory::query()
            ->where('part', 'A')
            ->orderBy('name')
            ->get()
            ->map(function (Signatory $signatory) {
                $titles = array_values(array_filter($signatory->title ?? []));

                return [
                    'id' => $signatory->id,
                    'name' => $signatory->name,
                    'office' => $signatory->office,
                    'titles' => $titles,
                    'title' => implode(' / ', $titles),
                ];
            })
            ->values();
    }

    private function payloadWithSignatorySnapshot(array $validated): array
    {
        $officeHead = Signatory::query()
            ->where('part', 'A')
            ->findOrFail($validated['office_head_id']);

        return [
            'certification_type' => 'non_ros',
            'subject_name' => $validated['subject_name'],
            'subject_honorific' => filled($validated['subject_honorific'] ?? null)
                ? trim((string) $validated['subject_honorific'])
                : null,
            'designation' => $validated['designation'],
            'office' => $validated['office'],
            'issued_date' => $validated['issued_date'],
            'office_head_signatory_id' => $officeHead->id,
            'signatory_name' => $officeHead->name,
            'signatory_office' => $officeHead->office,
            'signatory_titles' => array_values(array_filter($officeHead->title ?? [])),
        ];
    }
}
