<?php

namespace App\Http\Controllers;

use App\Enums\DocumentFolder;
use App\Http\Requests\Personnel\StoreCourseRequest;
use App\Http\Requests\Personnel\StorePersonDocumentRequest;
use App\Models\Contract;
use App\Models\PersonDocument;
use App\Repositories\Contracts\PersonRepositoryInterface;
use App\Services\Personnel\StoreCourseService;
use App\Services\Personnel\StorePersonDocumentService;
use App\Support\Files\StoredFileResponder;
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
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->people->findInContract($contract->id, $person);
        abort_if($model === null, 404);

        return view('documents.folder', [
            'person' => $model,
            'cargar' => $request->boolean('cargar') && (auth()->user()?->role->canUploadEvidence() ?? false),
        ]);
    }

    public function store(StorePersonDocumentRequest $request, int $person): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->people->findInContract($contract->id, $person);
        abort_if($model === null, 404);

        $file = $request->file('file');
        abort_if($file === null, 422);

        $this->storeDocument->execute(
            $contract,
            $model,
            DocumentFolder::from($request->string('folder')->toString()),
            $file,
            $request->filled('expires_on') ? $request->date('expires_on')->toDateString() : null,
        );

        return redirect()
            ->route('documents.folder', ['person' => $model, 'cargar' => 1])
            ->with('status', 'Documento cargado en la carpeta del vigilante.');
    }

    public function storeCourse(StoreCourseRequest $request, int $person): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $model = $this->people->findInContract($contract->id, $person);
        abort_if($model === null, 404);

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

        return StoredFileResponder::stream($file->disk_path, $file->original_name, (string) $file->mime, true);
    }

    public function download(Request $request, int $document): StreamedResponse
    {
        $file = $this->locate($request, $document);

        return StoredFileResponder::stream($file->disk_path, $file->original_name, (string) $file->mime, false);
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
