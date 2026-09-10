<?php

namespace App\Http\Controllers;

use App\Http\Requests\Maintenance\StoreMaintenanceRequest;
use App\Models\Contract;
use App\Models\ElectronicAsset;
use App\Models\Maintenance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MaintenanceController extends Controller
{
    public function index(Request $request): View
    {
        $contract = $request->attributes->get('currentContract');

        $assets = $contract instanceof Contract
            ? ElectronicAsset::query()
                ->where('contract_id', $contract->id)
                ->with(['post', 'maintenances' => fn ($q) => $q->latest('performed_on')])
                ->orderBy('name')
                ->get()
            : collect();

        return view('electronics.index', compact('assets'));
    }

    public function store(StoreMaintenanceRequest $request): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $asset = ElectronicAsset::query()
            ->where('contract_id', $contract->id)
            ->whereKey($request->integer('electronic_asset_id'))
            ->firstOrFail();

        Maintenance::query()->create([
            'tenant_id' => $contract->tenant_id,
            'electronic_asset_id' => $asset->id,
            'performed_on' => $request->date('performed_on'),
            'next_due_on' => $request->date('next_due_on'),
            'summary' => $request->string('summary')->toString(),
            'evidence_path' => $request->string('evidence_path')->toString() ?: null,
        ]);

        return back()->with('status', 'Mantenimiento registrado.');
    }
}
