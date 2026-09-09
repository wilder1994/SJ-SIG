<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\PlatformUsers\StorePlatformUserRequest;
use App\Http\Requests\PlatformUsers\UpdatePlatformUserRequest;
use App\Models\User;
use App\Repositories\Contracts\PlatformUserRepositoryInterface;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Services\PlatformUsers\PersistPlatformUserService;
use App\Support\Files\StoredFileResponder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PlatformUserController extends Controller
{
    public function __construct(
        private readonly PlatformUserRepositoryInterface $users,
        private readonly TenantRepositoryInterface $tenants,
        private readonly PersistPlatformUserService $persist,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->role->canManageUsers() ?? false, 403);

        return view('users.index', [
            'users' => $this->users->paginate($request->string('q')->toString() ?: null),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->role->canManageUsers() ?? false, 403);

        return view('users.form', [
            'user' => null,
            'tenants' => $this->tenants->allOrdered(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(StorePlatformUserRequest $request): RedirectResponse
    {
        $this->persist->create($request->validated(), $request->file('photo'));

        return redirect()->route('users.index')->with('status', 'Usuario creado. Deberá cambiar la clave en el primer ingreso.');
    }

    public function edit(User $user): View
    {
        abort_unless(auth()->user()?->role->canManageUsers() ?? false, 403);

        return view('users.form', [
            'user' => $user,
            'tenants' => $this->tenants->allOrdered(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UpdatePlatformUserRequest $request, User $user): RedirectResponse
    {
        $this->persist->update($user, $request->validated(), $request->file('photo'));

        return redirect()->route('users.index')->with('status', 'Usuario actualizado.');
    }

    public function photo(User $user): StreamedResponse
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User, 401);
        abort_unless($this->canSeePhoto($actor, $user), 404);
        abort_if($user->photo_path === null, 404);

        $mime = match (strtolower(pathinfo($user->photo_path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return StoredFileResponder::stream($user->photo_path, basename($user->photo_path), $mime, true);
    }

    private function canSeePhoto(User $actor, User $subject): bool
    {
        if ($actor->id === $subject->id || $actor->role->seesAllClients()) {
            return true;
        }

        return $actor->tenant_id !== null && (int) $actor->tenant_id === (int) $subject->tenant_id;
    }
}
