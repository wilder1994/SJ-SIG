<?php

namespace App\Http\Controllers;

use App\Http\Requests\Personnel\ConfirmPersonnelImportRequest;
use App\Http\Requests\Personnel\ImportPersonnelRequest;
use App\Http\Requests\Personnel\StorePersonPhotoRequest;
use App\Http\Requests\Personnel\StorePersonRequest;
use App\Models\Contract;
use App\Models\Person;
use App\Repositories\Contracts\PersonRepositoryInterface;
use App\Services\Personnel\CreatePersonService;
use App\Services\Personnel\ImportPersonnelWorkbookService;
use App\Services\Personnel\PersonnelImportDraftStore;
use App\Services\Personnel\StorePersonPhotoService;
use App\Support\Files\StoredFileResponder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PersonController extends Controller
{
    public function __construct(
        private readonly PersonRepositoryInterface $people,
        private readonly ImportPersonnelWorkbookService $importer,
        private readonly PersonnelImportDraftStore $drafts,
        private readonly CreatePersonService $creator,
        private readonly StorePersonPhotoService $photos,
    ) {}

    public function index(Request $request): View
    {
        $contract = $request->attributes->get('currentContract');
        $search = $request->string('q')->toString() ?: null;

        return view('people.index', [
            'people' => $contract instanceof Contract
                ? $this->people->paginateForContract($contract->id, $search)
                : new LengthAwarePaginator([], 0, 24),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->role->canUploadEvidence() ?? false, 403);

        return view('people.create');
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $person = $this->creator->execute($contract, $request->personPayload(), $request->file('photo'));

        return redirect()->route('people.show', $person)->with('status', 'Empleado registrado. Los PDF se cargan en Documentos → carpeta.');
    }

    public function photo(Request $request, int $person): StreamedResponse
    {
        $model = $this->personInContract($request, $person);
        abort_if(! is_string($model->photo_path) || $model->photo_path === '', 404);

        $mime = match (strtolower(pathinfo($model->photo_path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return StoredFileResponder::stream($model->photo_path, basename($model->photo_path), $mime, true);
    }

    public function storePhoto(StorePersonPhotoRequest $request, int $person): RedirectResponse
    {
        $model = $this->personInContract($request, $person);
        $file = $request->file('photo');
        abort_if($file === null, 422);
        $this->photos->execute($model, $file);

        return back()->with('status', 'Foto del vigilante actualizada.');
    }

    public function show(Request $request, int $person): View
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->people->findInContract($contract->id, $person);
        abort_if($model === null, 404);

        return view('people.show', ['person' => $model]);
    }

    public function preview(ImportPersonnelRequest $request): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $file = $request->file('workbook');
        abort_if($file === null, 422);

        $token = $this->drafts->put((int) $request->user()->id, $contract->id, $file);
        $request->session()->put('personnel_import_token', $token);

        return redirect()->route('people.import.review');
    }

    public function review(Request $request): View|RedirectResponse
    {
        abort_unless($request->user()?->role->canImportPersonnel() ?? false, 403);

        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $loaded = $this->draftFor($request, $contract);
        if ($loaded === null) {
            return redirect()->route('people.index')->with('status', 'Vuelva a cargar el Excel para revisar la plantilla.');
        }

        [$token, $draft] = $loaded;

        try {
            $analysis = $this->importer->preview($contract, $draft['absolute']);
        } catch (RuntimeException $exception) {
            $this->drafts->forget((int) $request->user()->id, $token);
            $request->session()->forget('personnel_import_token');

            return redirect()->route('people.index')->withErrors(['workbook' => $exception->getMessage()]);
        }

        return view('people.import-review', [
            'token' => $token,
            'filename' => $draft['filename'],
            'analysis' => $analysis,
        ]);
    }

    public function import(ConfirmPersonnelImportRequest $request): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $loaded = $this->draftFor($request, $contract);
        if ($loaded === null) {
            return redirect()->route('people.index')->with('status', 'La revisión expiró. Vuelva a cargar el Excel.');
        }

        [$token, $draft] = $loaded;

        try {
            $result = $this->importer->commit($contract, $draft['absolute']);
        } catch (RuntimeException $exception) {
            return redirect()->route('people.import.review')->withErrors(['workbook' => $exception->getMessage()]);
        }

        $this->drafts->forget((int) $request->user()->id, $token);
        $request->session()->forget('personnel_import_token');

        $errors = count($result['errors']);

        return redirect()->route('people.index')->with('status', sprintf(
            'Importación lista. Altas %d · Actualizaciones %d · Sin cambios %d · Errores %d.',
            $result['created'],
            $result['updated'],
            $result['unchanged'],
            $errors,
        ));
    }

    /**
     * @return array{0: string, 1: array{contract_id: int, path: string, filename: string, absolute: string}}|null
     */
    private function draftFor(Request $request, Contract $contract): ?array
    {
        $token = (string) ($request->input('token') ?: $request->session()->get('personnel_import_token') ?: '');
        if ($token === '') {
            return null;
        }

        $draft = $this->drafts->get((int) $request->user()->id, $token);
        if ($draft === null || $draft['contract_id'] !== $contract->id) {
            return null;
        }

        return [$token, $draft];
    }

    private function personInContract(Request $request, int $person): Person
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->people->findInContract($contract->id, $person);
        abort_if($model === null, 404);

        return $model;
    }
}
