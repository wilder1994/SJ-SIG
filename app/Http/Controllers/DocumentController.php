<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\PersonDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $q = $request->string('q')->toString();

        $documents = PersonDocument::query()
            ->whereHas('person.contracts', fn ($query) => $query->where('contracts.id', $contract->id))
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('original_name', 'like', '%'.$q.'%')
                        ->orWhere('folder', 'like', '%'.$q.'%')
                        ->orWhereHas('person', fn ($p) => $p->where('full_name', 'like', '%'.$q.'%'));
                });
            })
            ->with('person')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('documents.index', compact('documents', 'q'));
    }

    public function download(Request $request, int $document): StreamedResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $file = PersonDocument::query()
            ->whereKey($document)
            ->whereHas('person.contracts', fn ($query) => $query->where('contracts.id', $contract->id))
            ->firstOrFail();

        $absolute = storage_path('app/'.$file->disk_path);
        abort_unless(is_file($absolute), 404);

        return response()->streamDownload(function () use ($absolute): void {
            readfile($absolute);
        }, $file->original_name);
    }
}
