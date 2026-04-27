<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\ManagedUserResource;
use App\Models\User;
use App\Services\ScholarshipUserImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->with('roles')
            ->latest();

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('office', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($role = trim((string) $request->get('role', ''))) {
            $query->role($role);
        }

        $perPage = max(1, min((int) $request->get('per_page', 15), 100));
        $paginated = $query->paginate($perPage);

        return response()->json([
            'data' => ManagedUserResource::collection($paginated->getCollection())->resolve($request),
            'total' => User::count(),
            'filtered_total' => $paginated->total(),
            'per_page' => $paginated->perPage(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'role_options' => Role::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($roleItem) => [
                    'id' => $roleItem->id,
                    'name' => $roleItem->name,
                ])
                ->values(),
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $roleNames = $validated['role_names'];
        unset($validated['role_names']);

        $user = User::create($validated);
        $user->syncRoles($roleNames);
        $user->load('roles');

        return response()->json([
            'data' => (new ManagedUserResource($user))->resolve($request),
            'message' => 'User created.',
        ], 201);
    }

    public function importScholarship(Request $request, ScholarshipUserImportService $importService): JsonResponse
    {
        try {
            $summary = $importService->import();

            return response()->json([
                'message' => 'Scholarship users imported.',
                'summary' => $summary,
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Could not import scholarship users.',
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::query()->with('roles')->findOrFail($id);

        $validated = $request->validated();
        $roleNames = $validated['role_names'];
        unset($validated['role_names']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->syncRoles($roleNames);
        $user->load('roles');

        return response()->json([
            'data' => (new ManagedUserResource($user))->resolve($request),
            'message' => 'User updated.',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ((int) $request->user()?->id === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your current account.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted.',
        ]);
    }
}
