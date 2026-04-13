<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResponsibilityCenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponsibilityCenterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ResponsibilityCenter::orderBy('name');

        if ($fiscalYear = $request->get('fiscal_year')) {
            $query->where('fiscal_year', $fiscalYear);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function particulars(int $id): JsonResponse
    {
        $rc = ResponsibilityCenter::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $rc->particulars()->orderBy('name')->get(),
        ]);
    }
}
