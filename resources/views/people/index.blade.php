@extends('layouts.app')

@section('title', 'Personal · SJ-SIG')

@section('content')
@php
    $hasPeople = $people->isNotEmpty();
    $searching = filled(request('q'));
    $canCreate = auth()->user()->role->canUploadEvidence();
    $canImport = auth()->user()->role->canImportPersonnel();
    $perPage = $perPage ?? 25;
@endphp

@if(! $currentContract)
    <x-empty-panel kicker="Sin personal" title="No hay personal registrado" />
@elseif(! $hasPeople && ! $searching)
    <article class="card" style="max-width:820px">
        <p class="kicker">Sin personal</p>
        <h2 class="display" style="font-size:26px;margin:4px 0 8px">No hay personal registrado</h2>
        <p class="muted">En este contrato todavía no hay vigilantes. Crea uno o importa la plantilla SJ-SIG.</p>
        <div class="people-toolbar" style="margin-top:14px;justify-content:flex-start">
            @if($canCreate)
                <a class="btn" href="{{ route('people.create') }}">Nuevo empleado</a>
            @endif
            @if($canImport)
                <button class="btn ghost" type="button" data-open-upload>Carga masiva</button>
            @endif
        </div>
    </article>
    @include('people._import-modal')
@else
    <section class="people-board">
        <article class="card">
            <div class="people-toolbar">
                <form method="get" class="people-search">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Nombre o cédula">
                    <button class="btn" type="submit">Buscar</button>
                </form>
                @if($canCreate)
                    <a class="btn" href="{{ route('people.create') }}">Nuevo empleado</a>
                @endif
                @if($canImport)
                    <button class="btn ghost" type="button" data-open-upload>Carga masiva</button>
                @endif
            </div>
            <div class="people-scroll">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Identificación</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Cargo</th>
                            <th>Ingreso</th>
                            <th>Teléfono</th>
                            <th>EPS</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($people as $person)
                        <tr>
                            <td>{{ $person->document_type }} {{ $person->document_number }}</td>
                            <td>{{ $person->full_name }}</td>
                            <td>{{ $person->isActive() ? 'Activo' : 'Retiro' }}</td>
                            <td>{{ $person->job_code ?: '—' }}</td>
                            <td>{{ $person->hired_on?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $person->phone ?: '—' }}</td>
                            <td>{{ $person->eps_name ?: '—' }}</td>
                            <td><a href="{{ route('people.show', $person) }}">Ficha</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="muted">No hay coincidencias para esa búsqueda.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="people-foot">
                <p class="muted">
                    @if($people->total() === 0)
                        0 personas
                    @else
                        Mostrando {{ $people->firstItem() }}–{{ $people->lastItem() }} de {{ $people->total() }}
                    @endif
                </p>
                <form method="get" class="people-page-size">
                    @if(filled(request('q')))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <label class="muted">Por página
                        <select name="per_page" onchange="this.form.submit()">
                            @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </label>
                </form>
                <div>{{ $people->links() }}</div>
            </div>
        </article>
    </section>
    @include('people._import-modal')
@endif
@endsection
