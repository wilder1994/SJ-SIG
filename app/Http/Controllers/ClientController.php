<?php

namespace App\Http\Controllers;

use App\Http\Requests\Clients\StoreClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use App\Models\Tenant;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Services\Clients\CreateClientService;
use App\Services\Clients\UpdateClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ClientController extends Controller
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
        private readonly CreateClientService $creator,
        private readonly UpdateClientService $updater,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->role->canManageClients() ?? false, 403);

        return view('clients.index', [
            'clients' => $this->tenants->paginate($request->string('q')->toString() ?: null),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->role->canManageClients() ?? false, 403);

        return view('clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $this->creator->execute($request->clientPayload());

        return redirect()->route('clients.index')->with('status', 'Cliente creado. Ya puede asignar usuarios y armar su estructura.');
    }

    public function edit(Tenant $client): View
    {
        abort_unless(auth()->user()?->role->canManageClients() ?? false, 403);

        return view('clients.edit', ['client' => $client]);
    }

    public function update(UpdateClientRequest $request, Tenant $client): RedirectResponse
    {
        $this->updater->execute($client, $request->clientPayload());

        return redirect()->route('clients.index')->with('status', 'Cliente actualizado.');
    }
}
