<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\PlatformUserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class OperationsTeamController extends Controller
{
    public function __construct(
        private readonly PlatformUserRepositoryInterface $users,
    ) {}

    public function __invoke(Request $request): View
    {
        abort_unless(auth()->user()?->role->canAccessOpsTeam() ?? false, 403);
        $contract = $request->attributes->get('currentContract');
        abort_if($contract === null, 404);

        return view('operations.index', [
            'members' => $this->users->operationsForTenant((int) $contract->tenant_id),
        ]);
    }
}
