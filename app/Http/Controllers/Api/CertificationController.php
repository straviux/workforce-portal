<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNonRosCertificationRequest;
use App\Http\Requests\UpdateNonRosCertificationRequest;
use App\Models\Certification;
use App\Models\Signatory;
use App\Models\SignatoryDocumentDefault;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
                        ->orWhere('subject_firstname', 'like', "%{$search}%")
                        ->orWhere('subject_middlename', 'like', "%{$search}%")
                        ->orWhere('subject_lastname', 'like', "%{$search}%")
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
        } catch (ValidationException $exception) {
            throw $exception;
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
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Error updating non-ROS certification', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json([
                'message' => 'Could not update certification.',
            ], 500);
        }
    }

    public function destroyNonRos(int $id): JsonResponse
    {
        try {
            $certification = Certification::query()
                ->where('certification_type', 'non_ros')
                ->findOrFail($id);

            $certification->delete();

            return response()->json([
                'message' => 'Certification deleted.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error deleting non-ROS certification', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json([
                'message' => 'Could not delete certification.',
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
        // Client-supplied signatory fields are ignored: the snapshot comes
        // from the Offices module (the office's assigned head, else its
        // main office's head), falling back to the centrally configured
        // certification preset (or the first Part A office head when no
        // preset exists).
        $signatory = SignatoryDocumentDefault::resolveSnapshot('certification', trim((string) ($validated['office'] ?? '')) ?: null);

        if ($signatory === null) {
            throw ValidationException::withMessages([
                'office_head_id' => 'No office head signatory is configured. Add one in Settings before saving a certification.',
            ]);
        }

        $subjectFirstName = trim((string) $validated['subject_firstname']);
        $subjectMiddleName = filled($validated['subject_middlename'] ?? null)
            ? trim((string) $validated['subject_middlename'])
            : null;
        $subjectLastName = trim((string) $validated['subject_lastname']);

        return [
            'certification_type' => 'non_ros',
            'subject_name' => $this->buildSubjectName($subjectFirstName, $subjectMiddleName, $subjectLastName),
            'subject_honorific' => filled($validated['subject_honorific'] ?? null)
                ? trim((string) $validated['subject_honorific'])
                : null,
            'subject_firstname' => $subjectFirstName,
            'subject_middlename' => $subjectMiddleName,
            'subject_lastname' => $subjectLastName,
            'designation' => $validated['designation'],
            'office' => $validated['office'],
            'issued_date' => $validated['issued_date'],
            ...$signatory,
        ];
    }

    private function buildSubjectName(string $subjectFirstName, ?string $subjectMiddleName, string $subjectLastName): string
    {
        return collect([$subjectFirstName, $subjectMiddleName, $subjectLastName])
            ->filter(fn($value) => filled($value))
            ->implode(' ');
    }
}
