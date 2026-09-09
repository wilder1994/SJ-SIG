<?php

namespace App\Http\Controllers;

use App\Enums\DocumentFolder;
use App\Enums\LaborHistoryDocumentType;
use App\Http\Requests\Personnel\IndexLaborHistoryRequest;
use App\Http\Requests\Personnel\MarkLaborHistoryNaRequest;
use App\Http\Requests\Personnel\StoreCourseRequest;
use App\Http\Requests\Personnel\StoreLaborHistoryBatchRequest;
use App\Http\Requests\Personnel\StorePersonDocumentRequest;
use App\Models\Contract;
use App\Models\DocumentBatch;
use App\Models\Person;
use App\Models\PersonDocument;
use App\Repositories\Contracts\PersonRepositoryInterface;
use App\Services\Personnel\IndexLaborHistoryPdfService;
use App\Services\Personnel\MarkLaborHistoryNotApplicableService;
use App\Services\Personnel\StoreCourseService;
use App\Services\Personnel\StoreLaborHistoryBatchService;
use App\Services\Personnel\StorePersonDocumentService;
use App\Support\Files\StoredFileResponder;
use App\Support\Personnel\LaborHistoryChecklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DocumentController extends Controller
{
    public function __construct(
        private readonly PersonRepositoryInterface $people,
        private readonly StorePersonDocumentService $storeDocument,
        private readonly StoreCourseService $courses,
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

        return view('documents.folder', [
            'person' => $model,
            'cargar' => $request->boolean('cargar') && (auth()->user()?->role->canUploadEvidence() ?? false),
            'historyRows' => LaborHistoryChecklist::for($model),
            'historySummary' => LaborHistoryChecklist::summary($model),
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
        abort_if($folder === DocumentFolder::HojaVida, 422, 'La Historia Laboral se carga con el indexador.');

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

        $batch = $this->historyBatch->execute($contract, $model, $file);

        return redirect()->route('documents.history.index', ['person' => $model, 'batch' => $batch]);
    }

    public function historyIndex(Request $request, int $person, int $batch): View
    {
        $model = $this->personInContract($request, $person);
        abort_unless(auth()->user()?->role->canUploadEvidence() ?? false, 403);

        $lote = $this->batchForPerson($model, $batch);

        return view('documents.index-batch', [
            'person' => $model,
            'batch' => $lote,
            'historyMeta' => [
                'page_count' => $lote->page_count,
                'types' => collect(LaborHistoryDocumentType::cases())->map(fn (LaborHistoryDocumentType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                    'name' => $type->suggestedName($model),
                    'req' => $type->requirement()->label(),
                ])->values(),
            ],
        ]);
    }

    public function storeHistoryIndex(IndexLaborHistoryRequest $request, int $person, int $batch): RedirectResponse
    {
        $model = $this->personInContract($request, $person);
        $lote = $this->batchForPerson($model, $batch);

        $slices = [];
        foreach ($request->validated('slices') as $row) {
            $from = (int) $row['page_from'];
            $to = (int) $row['page_to'];
            abort_if($from > $lote->page_count || $to > $lote->page_count, 422);

            $slices[] = [
                'type' => LaborHistoryDocumentType::from($row['document_type']),
                'display_name' => $row['display_name'],
                'page_from' => $from,
                'page_to' => $to,
            ];
        }

        $this->historyIndex->execute($lote, $model, $slices);

        return redirect()
            ->route('documents.folder', $model)
            ->with('status', 'Historia Laboral indexada: '.count($slices).' documento(s).');
    }

    public function markHistoryNa(MarkLaborHistoryNaRequest $request, int $person): RedirectResponse
    {
        $model = $this->personInContract($request, $person);
        $type = LaborHistoryDocumentType::from($request->string('document_type')->toString());
        $this->historyNa->execute($model, $type);

        return redirect()
            ->route('documents.folder', ['person' => $model, 'cargar' => 1])
            ->with('status', $type->label().' marcado como no aplica.');
    }

    public function storeCourse(StoreCourseRequest $request, int $person): RedirectResponse
    {
        $model = $this->personInContract($request, $person);
        $this->courses->execute(
            $model,
            $request->string('title')->toString(),
            $request->date('taken_on')->toDateString(),
        );

        return redirect()
            ->route('documents.folder', ['person' => $model, 'cargar' => 1])
            ->with('status', 'Curso registrado. El acta PDF se carga en la carpeta Cursos.');
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

    private function personInContract(Request $request, int $person): Person
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->people->findInContract($contract->id, $person);
        abort_if($model === null, 404);

        return $model;
    }

    private function batchForPerson(Person $person, int $batch): DocumentBatch
    {
        return DocumentBatch::query()
            ->whereKey($batch)
            ->where('person_id', $person->id)
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
