<?php

namespace App\Http\Middleware;

use App\Models\Contract;
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
        $contract = $this->resolveContract($request, $user, $accessible);

        if ($contract === null) {
            $request->session()->forget('current_contract_id');
            $request->attributes->set('currentContract', null);
            view()->share('currentContract', null);
            view()->share('accessibleContracts', $accessible);

            if ($user->role->seesAllClients()) {
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

    /** @param  \Illuminate\Support\Collection<int, Contract>  $accessible */
    private function resolveContract(Request $request, User $user, $accessible): ?Contract
    {
        $requested = $request->integer('contract') ?: (int) $request->session()->get('current_contract_id', 0);

        if ($user->boundContractId() !== null) {
            return $this->contracts->findAccessibleById($user, $user->boundContractId());
        }

        if ($requested > 0) {
            $chosen = $this->contracts->findAccessibleById($user, $requested);
            if ($chosen instanceof Contract) {
                return $chosen;
            }
        }

        $first = $accessible->first();

        return $first instanceof Contract ? $first : null;
    }
}
