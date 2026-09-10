<?php

namespace App\Http\Controllers;

use App\Http\Requests\Parafiscal\StoreParafiscalRequest;
use App\Models\CompanyParafiscal;
use App\Models\Contract;
use App\Services\Parafiscal\StoreParafiscalService;
use App\Support\Files\StoredFileResponder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ParafiscalController extends Controller
{
    public function __construct(
        private readonly StoreParafiscalService $storeParafiscal,
    ) {}

    public function index(Request $request): View
    {
        $contract = $request->attributes->get('currentContract');

        return view('parafiscals.index', [
            'items' => $contract instanceof Contract
                ? CompanyParafiscal::query()
                    ->where('contract_id', $contract->id)
                    ->orderByDesc('period')
                    ->get()
                : collect(),
        ]);
    }

    public function store(StoreParafiscalRequest $request): RedirectResponse
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');
        $file = $request->file('file');
        abort_if($file === null, 422);

        $this->storeParafiscal->execute($contract, $request->string('period')->toString(), $file);

        return back()->with('status', 'Parafiscal de empresa cargado para el periodo.');
    }

    public function preview(Request $request, int $parafiscal): StreamedResponse
    {
        $item = $this->locate($request, $parafiscal);

        return StoredFileResponder::stream($item->disk_path, $item->original_name, $this->mime($item), true);
    }

    public function download(Request $request, int $parafiscal): StreamedResponse
    {
        $item = $this->locate($request, $parafiscal);

        return StoredFileResponder::stream($item->disk_path, $item->original_name, $this->mime($item), false);
    }

    private function locate(Request $request, int $id): CompanyParafiscal
    {
        /** @var Contract $contract */
        $contract = $request->attributes->get('currentContract');

        return CompanyParafiscal::query()
            ->where('contract_id', $contract->id)
            ->whereKey($id)
            ->firstOrFail();
    }

    private function mime(CompanyParafiscal $item): string
    {
        $ext = strtolower(pathinfo($item->disk_path, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };
    }
}
