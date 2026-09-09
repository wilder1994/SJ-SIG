<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateForcedPasswordRequest;
use App\Services\PlatformUsers\PersistPlatformUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class ForcedPasswordController extends Controller
{
    public function edit(): View
    {
        abort_unless(auth()->user()?->must_change_password ?? false, 404);

        return view('auth.password');
    }

    public function update(UpdateForcedPasswordRequest $request, PersistPlatformUserService $persist): RedirectResponse
    {
        abort_unless(auth()->user()?->must_change_password ?? false, 404);
        $persist->changePassword(auth()->user(), $request->string('password')->toString());

        return redirect()->route('dashboard')->with('status', 'Clave actualizada.');
    }
}
