<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\CreateUserRequest;
use App\Http\Resources\Security\SecurityUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\View\View;

class SecurityUserController extends Controller
{
    public function index(Request $request): View|AnonymousResourceCollection
    {
        if ($request->wantsJson()) {
            $users = User::query()->with('roles')->orderBy('username')->get();

            return SecurityUserResource::collection($users);
        }

        return view('security.users');
    }

    /**
     * Reemplaza el /register público eliminado (decisión de producto,
     * hallazgo 6.2): el alta de usuarios la hace un admin desde acá.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = User::create([
            'username' => $request->string('username')->value(),
            'password' => $request->string('password')->value(),
        ]);

        $user->assignRole($request->string('role')->value());

        return (new SecurityUserResource($user->load('roles')))->response()->setStatusCode(201);
    }

    public function updateRole(Request $request, User $user): SecurityUserResource
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        // Un solo rol por usuario, igual que el esquema legacy (users.role_id).
        $user->syncRoles([$validated['role']]);

        return new SecurityUserResource($user->load('roles'));
    }
}
