<?php

namespace App\Http\Controllers;

use App\Enums\ServiceModality;
use App\Http\Requests\Structure\StorePostRequest;
use App\Http\Requests\Structure\StoreSiteRequest;
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
                ? $contract->sites()->with('posts')->get()
                : collect(),
            'modalities' => ServiceModality::cases(),
        ]);
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $this->sites->execute($contract, $request->validated());

        return back()->with('status', 'Instalación creada.');
    }

    public function storePost(StorePostRequest $request, int $site): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = Site::query()
            ->whereKey($site)
            ->where('contract_id', $contract->id)
            ->first();
        abort_if($model === null, 404);

        $this->posts->execute($model, $request->validated());

        return back()->with('status', 'Puesto creado con modalidad y unidades.');
    }
}
