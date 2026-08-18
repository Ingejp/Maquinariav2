<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\CreateUserRequest;
use App\Http\Resources\Security\SecurityUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
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

        // Hallazgo A09 OWASP: registrar altas de usuario (acción sensible).
        Log::info('Usuario creado', [
            'actor_id' => auth()->id(),
            'new_user_id' => $user->id,
            'username' => $user->username,
            'role' => $request->string('role')->value(),
        ]);

        return (new SecurityUserResource($user->load('roles')))->response()->setStatusCode(201);
    }

    public function updateRole(Request $request, User $user): SecurityUserResource
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        // Un solo rol por usuario, igual que el esquema legacy (users.role_id).
        $user->syncRoles([$validated['role']]);

        // Hallazgo A09 OWASP: registrar cambios de rol (acción sensible).
        Log::info('Rol de usuario actualizado', [
            'actor_id' => auth()->id(),
            'target_user_id' => $user->id,
            'username' => $user->username,
            'role' => $validated['role'],
        ]);

        return new SecurityUserResource($user->load('roles'));
    }
}
