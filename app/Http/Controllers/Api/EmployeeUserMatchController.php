<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmEmployeeUserMatchesRequest;
use App\Services\EmployeeUserMatchingService;
use Illuminate\Http\JsonResponse;

class EmployeeUserMatchController extends Controller
{
    public function __construct(private readonly EmployeeUserMatchingService $service) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->suggestMatches(),
        ]);
    }

    public function store(ConfirmEmployeeUserMatchesRequest $request): JsonResponse
    {
        $result = $this->service->confirmMatches($request->validated('matches'));

        return response()->json([
            'message' => "{$result['linked']} employee(s) linked to a user account.",
            ...$result,
        ]);
    }
}
