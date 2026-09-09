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

        $requested = $request->integer('contract') ?: (int) $request->session()->get('current_contract_id', 0);

        if ($user->boundContractId() !== null) {
            $contract = $this->contracts->findAccessibleById($user, $user->boundContractId());
        } elseif ($requested > 0) {
            $contract = $this->contracts->findAccessibleById($user, $requested);
        } else {
            $contract = $this->contracts->listAccessible($user)->first();
        }

        if ($contract === null) {
            abort(403, 'Sin contrato accesible.');
        }

        $contract->loadMissing('tenant');
        $request->session()->put('current_contract_id', $contract->id);
        $request->attributes->set('currentContract', $contract);
        view()->share('currentContract', $contract);
        view()->share('accessibleContracts', $this->contracts->listAccessible($user));

        return $next($request);
    }
}
