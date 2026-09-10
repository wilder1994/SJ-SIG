<?php

namespace App\Http\Controllers;

use App\Enums\GuardRole;
use App\Enums\ServiceModality;
use App\Http\Requests\Structure\StorePostRequest;
use App\Http\Requests\Structure\StoreSiteRequest;
use App\Http\Requests\Structure\UpdateSiteRequest;
use App\Models\Contract;
use App\Models\Site;
use App\Services\Structure\PersistPostService;
use App\Services\Structure\PersistSiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SiteController extends Controller
{
    public function __construct(
        private readonly PersistSiteService $sites,
        private readonly PersistPostService $posts,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->role->canAccessHr() ?? false, 403);

        $contract = $request->attributes->get('currentContract');

        return view('sites.index', [
            'sites' => $contract instanceof Contract
                ? $contract->sites()->with(['posts.staffings'])->orderBy('code')->get()
                : collect(),
            'canManage' => auth()->user()?->role->canManageStructure() ?? false,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->role->canManageStructure() ?? false, 403);

        return view('sites.create', $this->formCatalog());
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $site = $this->sites->execute($contract, $request->validated());

        return redirect()->route('sites.show', $site)->with('status', 'Instalación creada.');
    }

    public function show(Request $request, int $site): View
    {
        abort_unless(auth()->user()?->role->canAccessHr() ?? false, 403);

        $model = $this->siteInContract($request, $site);
        $model->load(['posts.staffings', 'serviceEvents.author']);

        return view('sites.show', [
            'site' => $model,
            'canManage' => auth()->user()?->role->canManageStructure() ?? false,
        ]);
    }

    public function edit(Request $request, int $site): View
    {
        abort_unless(auth()->user()?->role->canManageStructure() ?? false, 403);

        $model = $this->siteInContract($request, $site);
        $model->load(['posts.staffings']);

        return view('sites.edit', [
            'site' => $model,
            ...$this->formCatalog(),
        ]);
    }

    public function update(UpdateSiteRequest $request, int $site): RedirectResponse
    {
        $model = $this->siteInContract($request, $site);
        $this->sites->update($model, $request->validated());

        return redirect()->route('sites.show', $model)->with('status', 'Instalación actualizada.');
    }

    public function storePost(StorePostRequest $request, int $site): RedirectResponse
    {
        $model = $this->siteInContract($request, $site);
        $payload = $request->validated();
        $this->posts->execute($model, [
            'name' => $payload['name'],
            'shift_hours' => $payload['shift_hours'],
            'code' => strtoupper($payload['code']),
            'staffings' => $payload['staffings'] ?? [[
                'role' => GuardRole::Vigilante->value,
                'slots' => (int) ($payload['guard_slots'] ?? 1),
            ]],
        ]);

        return back()->with('status', 'Puesto creado con modalidad y unidades.');
    }

    /** @return array{modalities: list<ServiceModality>, roles: list<GuardRole>} */
    private function formCatalog(): array
    {
        return [
            'modalities' => ServiceModality::cases(),
            'roles' => GuardRole::cases(),
        ];
    }

    private function siteInContract(Request $request, int $site): Site
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = Site::query()
            ->whereKey($site)
            ->where('contract_id', $contract->id)
            ->first();
        abort_if($model === null, 404);

        return $model;
    }
}
