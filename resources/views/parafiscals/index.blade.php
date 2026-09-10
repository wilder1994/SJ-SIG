@extends('layouts.app')

@section('title', 'Parafiscales · SJ-SIG')

@section('content')
@if($items->isEmpty())
    <x-empty-panel kicker="Sin parafiscales" title="No hay parafiscales">
        <p class="muted">En este contrato todavía no hay planilla PILA ni otro soporte de empresa.</p>
        @if(auth()->user()->role->canUploadEvidence())
            <form method="post" action="{{ route('parafiscals.store') }}" enctype="multipart/form-data" class="field" style="gap:10px;margin-top:16px;text-align:left">
                @csrf
                <label class="field">Periodo
                    <input type="month" name="period" value="{{ now()->format('Y-m') }}" required>
                </label>
                <label class="field">PDF
                    <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
                </label>
                <button class="btn" type="submit">Subir expediente</button>
            </form>
        @endif
    </x-empty-panel>
@else
<div class="split">
    <article class="card">
        <p class="kicker">Empresa · por periodo</p>
        <h2 class="display" style="font-size:24px;margin:4px 0 8px">Expediente parafiscal</h2>
        <p class="muted">Planilla PILA u otro soporte legal de SJ, mes a mes. No es de la persona: es de la empresa, visible en este contrato.</p>
        <table class="data">
            <thead><tr><th>Periodo</th><th>Documento</th><th></th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->period }}</td>
                    <td>{{ $item->original_name }}</td>
                    <td>
                        <button class="btn ghost" type="button" data-preview="{{ route('parafiscals.preview', $item) }}" data-name="{{ $item->original_name }}">Ver</button>
                        <a href="{{ route('parafiscals.download', $item) }}">Descargar</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </article>
    @if(auth()->user()->role->canUploadEvidence())
    <article class="card">
        <p class="kicker">Cargar periodo</p>
        <form method="post" action="{{ route('parafiscals.store') }}" enctype="multipart/form-data" class="field" style="gap:10px">
            @csrf
            <label class="field">Periodo
                <input type="month" name="period" value="{{ now()->format('Y-m') }}" required>
            </label>
            <label class="field">PDF
                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
            </label>
            <button class="btn" type="submit">Subir expediente</button>
        </form>
    </article>
    @endif
</div>
@endif
@endsection
