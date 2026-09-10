<?php

namespace App\Http\Controllers;

use App\Enums\NoveltyStatus;
use App\Http\Requests\Novelty\StoreNoveltyRequest;
use App\Models\Contract;
use App\Models\Novelty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

final class NoveltyController extends Controller
{
    public function index(Request $request): View
    {
        $contract = $request->attributes->get('currentContract');

        return view('novelties.index', [
            'novelties' => $contract instanceof Contract
                ? Novelty::query()
                    ->where('contract_id', $contract->id)
                    ->with('post')
                    ->latest()
                    ->paginate(20)
                : new LengthAwarePaginator([], 0, 20),
        ]);
    }

    public function store(StoreNoveltyRequest $request): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');

        Novelty::query()->create([
            'tenant_id' => $contract->tenant_id,
            'contract_id' => $contract->id,
            'post_id' => $request->integer('post_id') ?: null,
            'opened_by' => $request->user()->id,
            'title' => $request->string('title')->toString(),
            'body' => $request->string('body')->toString(),
            'status' => NoveltyStatus::Open,
        ]);

        return back()->with('status', 'Novedad registrada.');
    }
}
