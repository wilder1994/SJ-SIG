<?php

namespace App\Http\Controllers;

use App\Http\Requests\Personnel\ImportPersonnelRequest;
use App\Models\Contract;
use App\Repositories\Contracts\PersonRepositoryInterface;
use App\Services\Personnel\ImportPersonnelWorkbookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PersonController extends Controller
{
    public function __construct(
        private readonly PersonRepositoryInterface $people,
        private readonly ImportPersonnelWorkbookService $importer,
    ) {}

    public function index(Request $request): View
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');

        return view('people.index', [
            'people' => $this->people->paginateForContract($contract->id, $request->string('q')->toString() ?: null),
        ]);
    }

    public function show(Request $request, int $person): View
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->people->findInContract($contract->id, $person);
        abort_if($model === null, 404);

        return view('people.show', ['person' => $model]);
    }

    public function import(ImportPersonnelRequest $request): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $path = $request->file('workbook')?->getRealPath();
        abort_if($path === false || $path === null, 422);

        $result = $this->importer->execute($contract, $path);

        return back()->with('import', $result);
    }
}
