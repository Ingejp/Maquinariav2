<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\PermissionRequest;
use App\Http\Resources\Security\PermissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request): View|AnonymousResourceCollection
    {
        if ($request->wantsJson()) {
            $permissions = Permission::query()->orderBy('name')->get();

            return PermissionResource::collection($permissions);
        }

        return view('security.permissions');
    }

    public function store(PermissionRequest $request): JsonResponse
    {
        $permission = Permission::create([
            'name' => $request->string('name')->value(),
            'guard_name' => 'web',
        ]);

        return (new PermissionResource($permission))->response()->setStatusCode(201);
    }

    public function update(PermissionRequest $request, Permission $permission): PermissionResource
    {
        $permission->update(['name' => $request->string('name')->value()]);

        return new PermissionResource($permission);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $rolesCount = DB::table('role_has_permissions')->where('permission_id', $permission->id)->count();

        if ($rolesCount > 0) {
            return response()->json([
                'message' => 'No se puede eliminar: hay roles usando este permiso. Quítalo de esos roles primero.',
            ], 422);
        }

        $permission->delete();

        return response()->json(status: 204);
    }
}
