<?php

namespace App\Http\Controllers;

use App\Enums\DocumentFolder;
use App\Http\Requests\Personnel\IndexLaborHistoryRequest;
use App\Http\Requests\Personnel\MarkLaborHistoryNaRequest;
use App\Http\Requests\Personnel\StoreLaborHistoryBatchRequest;
use App\Http\Requests\Personnel\StorePersonDocumentRequest;
use App\Models\Contract;
use App\Models\DocumentBatch;
use App\Models\Person;
use App\Models\PersonDocument;
use App\Repositories\Contracts\PersonRepositoryInterface;
use App\Services\Personnel\IndexLaborHistoryPdfService;
use App\Services\Personnel\MarkLaborHistoryNotApplicableService;
use App\Services\Personnel\StoreLaborHistoryBatchService;
use App\Services\Personnel\StorePersonDocumentService;
use App\Support\Files\StoredFileResponder;
use App\Support\Personnel\FolderChecklist;
use App\Support\Personnel\IndexedFolder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DocumentController extends Controller
{
    public function __construct(
        private readonly PersonRepositoryInterface $people,
        private readonly StorePersonDocumentService $storeDocument,
        private readonly StoreLaborHistoryBatchService $historyBatch,
        private readonly IndexLaborHistoryPdfService $historyIndex,
        private readonly MarkLaborHistoryNotApplicableService $historyNa,
    ) {}

    public function index(Request $request): View
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $q = $request->string('q')->toString();

        return view('documents.index', [
            'people' => $this->people->paginateForContract($contract->id, $q !== '' ? $q : null),
            'q' => $q,
        ]);
    }

    public function folder(Request $request, int $person): View
    {
        $model = $this->personInContract($request, $person);

        $indexedChecklists = [];
        foreach ([DocumentFolder::HojaVida, DocumentFolder::Certificados, DocumentFolder::Cursos, DocumentFolder::Afiliaciones] as $folder) {
            $indexedChecklists[] = [
                'folder' => $folder,
                'rows' => FolderChecklist::for($model, $folder),
                'summary' => FolderChecklist::summary($model, $folder),
                'panel' => $folder->value.'-list',
                'na' => $folder->naRoute(),
            ];
        }

        return view('documents.folder', [
            'person' => $model,
            'cargar' => $request->boolean('cargar') && (auth()->user()?->role->canUploadEvidence() ?? false),
            'indexedChecklists' => $indexedChecklists,
        ]);
    }

    public function store(StorePersonDocumentRequest $request, int $person): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->personInContract($request, $person);
        $file = $request->file('file');
        abort_if($file === null, 422);

        $folder = DocumentFolder::from($request->string('folder')->toString());
        abort_if($folder->isIndexed(), 422, 'Esta carpeta se carga con el indexador.');

        $this->storeDocument->execute(
            $contract,
            $model,
            $folder,
            $file,
            $request->filled('expires_on') ? $request->date('expires_on')->toDateString() : null,
        );

        return redirect()
            ->route('documents.folder', ['person' => $model, 'cargar' => 1])
            ->with('status', 'Documento cargado en la carpeta del vigilante.');
    }

    public function storeHistoryBatch(StoreLaborHistoryBatchRequest $request, int $person): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->personInContract($request, $person);
        $file = $request->file('file');
        abort_if($file === null, 422);

        $batch = $this->historyBatch->execute($contract, $model, $file, DocumentFolder::HojaVida);

        return redirect()->route('documents.history.index', ['person' => $model, 'batch' => $batch]);
    }

    public function storeAffiliationBatch(StoreLaborHistoryBatchRequest $request, int $person): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->personInContract($request, $person);
        $file = $request->file('file');
        abort_if($file === null, 422);

        $batch = $this->historyBatch->execute($contract, $model, $file, DocumentFolder::Afiliaciones);

        return redirect()->route('documents.affiliations.index', ['person' => $model, 'batch' => $batch]);
    }

    public function storeCertificateBatch(StoreLaborHistoryBatchRequest $request, int $person): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->personInContract($request, $person);
        $file = $request->file('file');
        abort_if($file === null, 422);

        $batch = $this->historyBatch->execute($contract, $model, $file, DocumentFolder::Certificados);

        return redirect()->route('documents.certificates.index', ['person' => $model, 'batch' => $batch]);
    }

    public function storeCourseBatch(StoreLaborHistoryBatchRequest $request, int $person): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->personInContract($request, $person);
        $file = $request->file('file');
        abort_if($file === null, 422);

        $batch = $this->historyBatch->execute($contract, $model, $file, DocumentFolder::Cursos);

        return redirect()->route('documents.courses.index', ['person' => $model, 'batch' => $batch]);
    }

    public function historyIndex(Request $request, int $person, int $batch): View
    {
        return $this->showIndexer($request, $person, $batch, DocumentFolder::HojaVida);
    }

    public function affiliationIndex(Request $request, int $person, int $batch): View
    {
        return $this->showIndexer($request, $person, $batch, DocumentFolder::Afiliaciones);
    }

    public function certificateIndex(Request $request, int $person, int $batch): View
    {
        return $this->showIndexer($request, $person, $batch, DocumentFolder::Certificados);
    }

    public function courseIndex(Request $request, int $person, int $batch): View
    {
        return $this->showIndexer($request, $person, $batch, DocumentFolder::Cursos);
    }

    public function storeHistoryIndex(IndexLaborHistoryRequest $request, int $person, int $batch): RedirectResponse
    {
        return $this->persistIndex($request, $person, $batch, DocumentFolder::HojaVida);
    }

    public function storeAffiliationIndex(IndexLaborHistoryRequest $request, int $person, int $batch): RedirectResponse
    {
        return $this->persistIndex($request, $person, $batch, DocumentFolder::Afiliaciones);
    }

    public function storeCertificateIndex(IndexLaborHistoryRequest $request, int $person, int $batch): RedirectResponse
    {
        return $this->persistIndex($request, $person, $batch, DocumentFolder::Certificados);
    }

    public function storeCourseIndex(IndexLaborHistoryRequest $request, int $person, int $batch): RedirectResponse
    {
        return $this->persistIndex($request, $person, $batch, DocumentFolder::Cursos);
    }

    public function markHistoryNa(MarkLaborHistoryNaRequest $request, int $person): RedirectResponse
    {
        return $this->markIndexedNa($request, $person, DocumentFolder::HojaVida);
    }

    public function markAffiliationNa(MarkLaborHistoryNaRequest $request, int $person): RedirectResponse
    {
        return $this->markIndexedNa($request, $person, DocumentFolder::Afiliaciones);
    }

    public function markCertificateNa(MarkLaborHistoryNaRequest $request, int $person): RedirectResponse
    {
        return $this->markIndexedNa($request, $person, DocumentFolder::Certificados);
    }

    public function markCourseNa(MarkLaborHistoryNaRequest $request, int $person): RedirectResponse
    {
        return $this->markIndexedNa($request, $person, DocumentFolder::Cursos);
    }

    public function previewBatch(Request $request, int $person, int $batch): StreamedResponse
    {
        return $this->streamBatch($request, $person, $batch, DocumentFolder::HojaVida);
    }

    public function previewAffiliationBatch(Request $request, int $person, int $batch): StreamedResponse
    {
        return $this->streamBatch($request, $person, $batch, DocumentFolder::Afiliaciones);
    }

    public function previewCertificateBatch(Request $request, int $person, int $batch): StreamedResponse
    {
        return $this->streamBatch($request, $person, $batch, DocumentFolder::Certificados);
    }

    public function previewCourseBatch(Request $request, int $person, int $batch): StreamedResponse
    {
        return $this->streamBatch($request, $person, $batch, DocumentFolder::Cursos);
    }

    public function preview(Request $request, int $document): StreamedResponse
    {
        $file = $this->locate($request, $document);
        abort_unless($file->hasFile(), 404);

        return StoredFileResponder::stream($file->disk_path, $file->label(), (string) $file->mime, true);
    }

    public function download(Request $request, int $document): StreamedResponse
    {
        $file = $this->locate($request, $document);
        abort_unless($file->hasFile(), 404);

        return StoredFileResponder::stream($file->disk_path, $file->label(), (string) $file->mime, false);
    }

    private function showIndexer(Request $request, int $person, int $batch, DocumentFolder $folder): View
    {
        $model = $this->personInContract($request, $person);
        abort_unless(auth()->user()?->role->canUploadEvidence() ?? false, 403);
        $lote = $this->batchForPerson($model, $batch, $folder);

        $preview = route((string) $folder->previewRoute(), ['person' => $model, 'batch' => $lote]);
        $store = route((string) $folder->storeIndexRoute(), ['person' => $model, 'batch' => $lote]);

        return view('documents.index-batch', [
            'person' => $model,
            'batch' => $lote,
            'folder' => $folder,
            'storeUrl' => $store,
            'previewUrl' => $preview,
            'historyMeta' => [
                'page_count' => $lote->page_count,
                'preview_url' => $preview,
                'course_fields' => $folder === DocumentFolder::Cursos,
                'types' => collect(IndexedFolder::types($folder))->map(fn ($type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                    'name' => $type->suggestedName($model),
                    'req' => $type->requirement()->label(),
                    'other' => method_exists($type, 'isRepeatable') && $type->isRepeatable(),
                ])->values(),
            ],
        ]);
    }

    private function persistIndex(IndexLaborHistoryRequest $request, int $person, int $batch, DocumentFolder $folder): RedirectResponse
    {
        $model = $this->personInContract($request, $person);
        $lote = $this->batchForPerson($model, $batch, $folder);

        $slices = [];
        foreach ($request->validated('slices') as $row) {
            $pages = [];
            foreach ($row['pages'] as $page) {
                $page = (int) $page;
                abort_if($page > $lote->page_count, 422);
                if (! in_array($page, $pages, true)) {
                    $pages[] = $page;
                }
            }

            $slices[] = [
                'type' => IndexedFolder::resolve($folder, $row['document_type']),
                'display_name' => $row['display_name'],
                'pages' => $pages,
                'taken_on' => $row['taken_on'] ?? null,
                'provider' => $row['provider'] ?? null,
            ];
        }

        $this->historyIndex->execute($lote, $model, $slices);

        return redirect()
            ->route('documents.folder', $model)
            ->with('status', $folder->label().' indexada: '.count($slices).' documento(s).');
    }

    private function markIndexedNa(MarkLaborHistoryNaRequest $request, int $person, DocumentFolder $folder): RedirectResponse
    {
        $model = $this->personInContract($request, $person);
        $type = IndexedFolder::resolve($folder, $request->string('document_type')->toString());
        $this->historyNa->execute($model, $folder, $type);

        return redirect()
            ->route('documents.folder', ['person' => $model, 'cargar' => 1])
            ->with('status', $type->label().' marcado como no aplica.');
    }

    private function streamBatch(Request $request, int $person, int $batch, DocumentFolder $folder): StreamedResponse
    {
        $model = $this->personInContract($request, $person);
        abort_unless(auth()->user()?->role->canUploadEvidence() ?? false, 403);
        $lote = $this->batchForPerson($model, $batch, $folder);

        return StoredFileResponder::stream($lote->disk_path, $lote->original_name, (string) $lote->mime, true);
    }

    private function personInContract(Request $request, int $person): Person
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->people->findInContract($contract->id, $person);
        abort_if($model === null, 404);

        return $model;
    }

    private function batchForPerson(Person $person, int $batch, ?DocumentFolder $folder = null): DocumentBatch
    {
        return DocumentBatch::query()
            ->whereKey($batch)
            ->where('person_id', $person->id)
            ->when($folder !== null, fn ($query) => $query->where('folder', $folder))
            ->firstOrFail();
    }

    private function locate(Request $request, int $document): PersonDocument
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');

        return PersonDocument::query()
            ->whereKey($document)
            ->whereHas('person.contracts', fn ($query) => $query->where('contracts.id', $contract->id))
            ->firstOrFail();
    }
}
