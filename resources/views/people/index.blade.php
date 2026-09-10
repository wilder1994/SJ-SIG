@extends('layouts.app')

@section('title', 'Personal · SJ-SIG')

@section('content')
@php
    $hasPeople = $people->isNotEmpty();
    $searching = filled(request('q'));
    $canCreate = auth()->user()->role->canUploadEvidence();
    $canImport = auth()->user()->role->canImportPersonnel();
@endphp

@if(! $currentContract)
    <x-empty-panel kicker="Sin personal" title="No hay personal registrado" />
@elseif(! $hasPeople && ! $searching)
    <article class="card" style="max-width:820px">
        <p class="kicker">Sin personal</p>
        <h2 class="display" style="font-size:26px;margin:4px 0 8px">No hay personal registrado</h2>
        <p class="muted">En este contrato todavía no hay vigilantes. Crea uno o importa la plantilla SJ-SIG.</p>
        @if($canCreate)
            <a class="btn" href="{{ route('people.create') }}" style="margin-top:14px">Nuevo empleado</a>
        @endif
        @if($canImport)
            <div style="margin-top:18px">
                @include('people._import')
            </div>
        @endif
    </article>
@else
<div class="split">
    <article class="card">
        <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;margin-bottom:12px">
            <form method="get" style="display:flex;gap:8px;flex:1">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Nombre o cédula" style="flex:1">
                <button class="btn" type="submit">Buscar</button>
            </form>
            @if($canCreate)
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
    @if($canImport)
        @include('people._import')
    @endif
</div>
@endif
@endsection
