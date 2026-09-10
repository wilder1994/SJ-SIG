@extends('layouts.app')

@section('title', 'Clientes · SJ-SIG')

@section('content')
@php
    $hasClients = $clients->isNotEmpty();
    $searching = filled(request('q'));
    $perPage = $perPage ?? 25;
@endphp

@if(! $hasClients && ! $searching)
    <x-empty-panel kicker="Sin clientes" title="No hay clientes" :require-client="false">
        <p class="muted">Aún no hay universos. Crea el primero y luego asigna usuarios, instalaciones y personal.</p>
        <a class="btn" href="{{ route('clients.create') }}" style="margin-top:14px">Nuevo cliente</a>
    </x-empty-panel>
@else
    <section class="people-board">
        <article class="card">
            <div class="people-toolbar">
                <form method="get" class="people-search">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Nombre, NIT o ciudad">
                    <button class="btn" type="submit">Buscar</button>
                </form>
                <a class="btn" href="{{ route('clients.create') }}">Nuevo cliente</a>
            </div>
            <div class="people-scroll">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>NIT</th>
                            <th>Ciudad</th>
                            <th>Estructura</th>
                            <th>Instalaciones</th>
                            <th>Personal</th>
                            <th>Usuarios</th>
                            <th>Teléfono</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($clients as $client)
                        @php
                            $contract = $client->primaryContract;
                            $isCurrent = $currentContract && $contract && $currentContract->id === $contract->id;
                        @endphp
                        <tr>
                            <td class="is-wrap">
                                {{ $client->displayName() }}
                                @if($client->legalLabel())
                                    <span class="muted"> · {{ $client->legalLabel() }}</span>
                                @endif
                                @if($isCurrent)
                                    <span class="muted"> · activo</span>
                                @endif
                            </td>
                            <td>{{ $client->nit ?: '—' }}</td>
                            <td>{{ $client->city ?: '—' }}</td>
                            <td>{{ $client->structureShortLabel() }}</td>
                            <td>{{ $contract?->sites_count ?? 0 }}</td>
                            <td>{{ $contract?->people_count ?? 0 }}</td>
                            <td>{{ $client->users_count }}</td>
                            <td>{{ $client->phone ?: '—' }}</td>
                            <td>
                                @if($contract)
                                    <a href="{{ route('dashboard', ['contract' => $contract->id]) }}">Entrar</a>
                                @endif
                                <a href="{{ route('clients.edit', $client) }}"@if($contract) style="margin-left:10px"@endif>Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="muted">No hay coincidencias para esa búsqueda.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="people-foot">
                <p class="muted">
                    @if($clients->total() === 0)
                        0 clientes
                    @else
                        Mostrando {{ $clients->firstItem() }}–{{ $clients->lastItem() }} de {{ $clients->total() }}
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
                <div>{{ $clients->links() }}</div>
            </div>
        </article>
    </section>
@endif
@endsection
