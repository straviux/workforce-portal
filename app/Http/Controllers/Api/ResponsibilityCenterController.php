<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Particular;
use App\Models\ResponsibilityCenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponsibilityCenterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ResponsibilityCenter::with('particulars')->orderBy('code');

        if ($fiscalYear = $request->get('fiscal_year')) {
            $query->where('fiscal_year', $fiscalYear);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'        => 'required|string|unique:responsibility_centers,code',
            'name'        => 'required|string',
            'fiscal_year' => 'nullable|string',
        ]);

        $rc = ResponsibilityCenter::create($validated);

        return response()->json($rc->load('particulars'), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $rc = ResponsibilityCenter::findOrFail($id);

        $validated = $request->validate([
            'code'        => 'required|string|unique:responsibility_centers,code,' . $id,
            'name'        => 'required|string',
            'fiscal_year' => 'nullable|string',
        ]);

        $rc->update($validated);

        return response()->json($rc->load('particulars'));
    }

    public function destroy(int $id): JsonResponse
    {
        $rc = ResponsibilityCenter::findOrFail($id);
        $rc->particulars()->delete();
        $rc->delete();

        return response()->json(['message' => 'Responsibility Center deleted successfully']);
    }

    public function particulars(int $id): JsonResponse
    {
        $rc = ResponsibilityCenter::findOrFail($id);

        return response()->json($rc->particulars()->orderBy('name')->get());
    }

    public function storeParticular(Request $request, int $id): JsonResponse
    {
        $rc = ResponsibilityCenter::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string',
            'account_code' => 'required|string',
            'allotment'    => 'required|numeric|min:0',
            'date_approved' => 'nullable|date',
            'date_expired'  => 'nullable|date',
        ]);

        $particular = $rc->particulars()->create($validated);

        return response()->json($particular, 201);
    }

    public function updateParticular(Request $request, int $id, int $particulerId): JsonResponse
    {
        $rc = ResponsibilityCenter::findOrFail($id);
        $particular = $rc->particulars()->findOrFail($particulerId);

        $validated = $request->validate([
            'name'         => 'required|string',
            'account_code' => 'required|string',
            'allotment'    => 'required|numeric|min:0',
            'date_approved' => 'nullable|date',
            'date_expired'  => 'nullable|date',
        ]);

        $particular->update($validated);

        return response()->json($particular);
    }

    public function destroyParticular(int $id, int $particulerId): JsonResponse
    {
        $rc = ResponsibilityCenter::findOrFail($id);
        $particular = $rc->particulars()->findOrFail($particulerId);
        $particular->delete();

        return response()->json(['message' => 'Particular deleted successfully']);
    }
}
