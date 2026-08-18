<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\RoleRequest;
use App\Http\Resources\Security\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): View|AnonymousResourceCollection
    {
        if ($request->wantsJson()) {
            $roles = Role::query()->with('permissions')->orderBy('name')->get();

            return RoleResource::collection($roles);
        }

        return view('security.roles');
    }

    public function store(RoleRequest $request): JsonResponse
    {
        $role = Role::create([
            'name' => $request->string('name')->value(),
            'guard_name' => 'web',
        ]);

        return (new RoleResource($role->load('permissions')))->response()->setStatusCode(201);
    }

    public function update(RoleRequest $request, Role $role): RoleResource
    {
        $role->update(['name' => $request->string('name')->value()]);

        return new RoleResource($role->load('permissions'));
    }

    public function destroy(Role $role): JsonResponse
    {
        $usersCount = DB::table('model_has_roles')->where('role_id', $role->id)->count();

        if ($usersCount > 0) {
            return response()->json([
                'message' => 'No se puede eliminar: hay usuarios con este rol asignado. Cámbiales el rol primero.',
            ], 422);
        }

        $role->delete();

        return response()->json(status: 204);
    }

    public function updatePermissions(Request $request, Role $role): RoleResource
    {
        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return new RoleResource($role->load('permissions'));
    }
}
