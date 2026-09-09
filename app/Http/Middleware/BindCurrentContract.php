<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Repositories\Contracts\ContractRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class BindCurrentContract
{
    public function __construct(
        private readonly ContractRepositoryInterface $contracts,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $accessible = $this->contracts->listAccessible($user);
        $requested = $request->integer('contract') ?: (int) $request->session()->get('current_contract_id', 0);

        if ($user->boundContractId() !== null) {
            $contract = $this->contracts->findAccessibleById($user, $user->boundContractId());
        } elseif ($requested > 0) {
            $contract = $this->contracts->findAccessibleById($user, $requested);
        } else {
            $contract = $accessible->first();
        }

        if ($contract === null) {
            if ($user->role->seesAllClients()) {
                $request->attributes->set('currentContract', null);
                view()->share('currentContract', null);
                view()->share('accessibleContracts', $accessible);

                return $next($request);
            }

            abort(403, 'Sin cliente accesible.');
        }

        $contract->loadMissing('tenant');
        $request->session()->put('current_contract_id', $contract->id);
        $request->attributes->set('currentContract', $contract);
        view()->share('currentContract', $contract);
        view()->share('accessibleContracts', $accessible);

        return $next($request);
    }
}
