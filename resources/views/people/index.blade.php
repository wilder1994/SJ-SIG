@extends('layouts.app')

@section('title', 'Personal · SJ-SIG')

@section('content')
@php
    $hasPeople = $people->isNotEmpty();
    $searching = filled(request('q'));
@endphp

@if(! $hasPeople && ! $searching)
    <x-empty-panel kicker="Sin personal" title="No hay personal registrado">
        <p class="muted">En este contrato todavía no hay vigilantes. Crea uno o importa la plantilla SJ-SIG.</p>
        @if(auth()->user()->role->canUploadEvidence())
            <a class="btn" href="{{ route('people.create') }}" style="margin-top:14px">Nuevo empleado</a>
        @endif
    </x-empty-panel>
@else
<div class="split">
    <article class="card">
        <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;margin-bottom:12px">
            <form method="get" style="display:flex;gap:8px;flex:1">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Nombre o cédula" style="flex:1">
                <button class="btn" type="submit">Buscar</button>
            </form>
            @if(auth()->user()->role->canUploadEvidence())
                <a class="btn" href="{{ route('people.create') }}">Nuevo empleado</a>
            @endif
        </div>
        <table class="data">
            <thead><tr><th>Identificación</th><th>Nombre</th><th>EPS</th><th></th></tr></thead>
            <tbody>
            @forelse($people as $person)
                <tr>
                    <td>{{ $person->document_type }} {{ $person->document_number }}</td>
                    <td>{{ $person->full_name }}</td>
                    <td>{{ $person->eps_name ?? '—' }}</td>
                    <td><a href="{{ route('people.show', $person) }}">Ficha</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No hay coincidencias para esa búsqueda.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px">{{ $people->links() }}</div>
    </article>
    @if(auth()->user()->role->canImportPersonnel())
    <article class="card">
        <p class="kicker">Carga masiva</p>
        <h2 class="display" style="font-size:22px;margin:6px 0 10px">Plantilla SJ-SIG</h2>
        <p class="muted">Fila 1 encabezado, fila 2 ayuda, desde la 3 trabajadores. Los PDF se cargan en Documentos → carpeta del vigilante.</p>
        <form method="post" action="{{ route('people.import') }}" enctype="multipart/form-data" style="margin-top:14px" class="field">
            @csrf
            <input type="file" name="workbook" accept=".xlsx,.xls" required>
            <button class="btn" type="submit">Importar</button>
        </form>
        @if(session('import'))
            <p style="margin-top:12px">Altas {{ session('import')['created'] }} · Actualizaciones {{ session('import')['updated'] }}</p>
            @foreach(session('import')['errors'] as $error)
                <p class="muted">Fila {{ $error['row'] }}: {{ $error['message'] }}</p>
            @endforeach
        @endif
    </article>
    @endif
</div>
@endif
@endsection
