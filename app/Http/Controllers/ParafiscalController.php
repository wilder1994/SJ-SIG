<?php

namespace App\Http\Controllers;

use App\Models\CompanyParafiscal;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ParafiscalController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');

        return view('parafiscals.index', [
            'items' => CompanyParafiscal::query()
                ->where('contract_id', $contract->id)
                ->orderByDesc('period')
                ->get(),
        ]);
    }
}
