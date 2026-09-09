@extends('layouts.app')

@section('title', 'Parafiscales · SJ-SIG')

@section('content')
<article class="card">
    <p class="kicker">Empresa · por periodo</p>
    <h2 class="display" style="font-size:24px;margin:4px 0 12px">Expediente parafiscal</h2>
    <table class="data">
        <thead><tr><th>Periodo</th><th>Documento</th></tr></thead>
        <tbody>
        @forelse($items as $item)
            <tr><td>{{ $item->period }}</td><td>{{ $item->original_name }}</td></tr>
        @empty
            <tr><td colspan="2" class="muted">Sin parafiscales de empresa en este contrato.</td></tr>
        @endforelse
        </tbody>
    </table>
</article>
@endsection
