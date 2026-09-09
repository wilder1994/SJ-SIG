<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\Dashboard\BuildContractDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(Request $request, BuildContractDashboardService $dashboard): View
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $contract->loadMissing('tenant');

        return view('dashboard.index', [
            'snapshot' => $dashboard->execute($contract),
        ]);
    }
}
