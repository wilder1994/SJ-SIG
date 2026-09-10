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
        $contract = $request->attributes->get('currentContract');

        $rows = $contract instanceof Contract
            ? ServiceDelivery::query()
                ->where('contract_id', $contract->id)
                ->with('post.site')
                ->orderByDesc('period_starts_on')
                ->orderBy('post_id')
                ->get()
                ->groupBy(fn (ServiceDelivery $row) => $row->period_kind.'|'.$row->period_starts_on?->toDateString())
            : collect();

        return view('services.index', compact('rows'));
    }
}
