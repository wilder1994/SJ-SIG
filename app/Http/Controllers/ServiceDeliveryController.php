<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ServiceDelivery;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ServiceDeliveryController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');

        $rows = ServiceDelivery::query()
            ->where('contract_id', $contract->id)
            ->with('post')
            ->orderByDesc('period_starts_on')
            ->orderBy('post_id')
            ->get()
            ->groupBy(fn (ServiceDelivery $row) => $row->period_kind.'|'.$row->period_starts_on?->toDateString());

        return view('services.index', compact('rows'));
    }
}
