<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\AccessRoleResource;
use App\Models\User;
use App\Support\SystemRoles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Role::query()
            ->where('guard_name', 'web')
            ->with('permissions')
            ->orderBy('name');

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhereHas('permissions', function ($permissionQuery) use ($search) {
                        $permissionQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = max(1, min((int) $request->get('per_page', 15), 100));
        $paginated = $query->paginate($perPage);

        return response()->json([
            'data' => AccessRoleResource::collection($paginated->getCollection())->resolve($request),
            'total' => Role::query()->where('guard_name', 'web')->count(),
            'filtered_total' => $paginated->total(),
            'per_page' => $paginated->perPage(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'permission_options' => Permission::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ])
                ->values(),
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role->load('permissions');

        return response()->json([
            'data' => (new AccessRoleResource($role))->resolve($request),
            'message' => 'Role created.',
        ], 201);
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->with('permissions')
            ->findOrFail($id);

        $validated = $request->validated();

        if (SystemRoles::isLocked($role->name) && $validated['name'] !== $role->name) {
            return response()->json([
                'message' => 'System roles cannot be renamed or deleted.',
            ], 422);
        }

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role->load('permissions');

        return response()->json([
            'data' => (new AccessRoleResource($role))->resolve($request),
            'message' => 'Role updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->findOrFail($id);

        if (SystemRoles::isLocked($role->name)) {
            return response()->json([
                'message' => 'System roles cannot be renamed or deleted.',
            ], 422);
        }

        if (User::query()->role($role->name)->exists()) {
            return response()->json([
                'message' => 'Remove this role from assigned users before deleting it.',
            ], 422);
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'message' => 'Role deleted.',
        ]);
    }
}
