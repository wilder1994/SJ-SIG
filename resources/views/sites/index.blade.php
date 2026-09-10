@extends('layouts.app')

@section('title', 'Instalaciones · SJ-SIG')

@section('content')
@if(! $currentContract)
    <x-empty-panel kicker="Sin instalaciones" title="No hay instalaciones">
        <p class="muted">Aún no hay un cliente activo. Crea el primero en Clientes; después podrás usar este módulo.</p>
        @if(auth()->user()->role->canManageClients())
            <a class="btn" href="{{ route('clients.create') }}" style="margin-top:14px">Nuevo cliente</a>
        @endif
    </x-empty-panel>
@else
<article class="card">
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;margin-bottom:12px">
        <div>
            <p class="kicker">Estructura del cliente</p>
            <h2 class="display" style="font-size:24px;margin:4px 0 6px">{{ $sites->isEmpty() ? 'No hay instalaciones' : 'Instalaciones y puestos' }}</h2>
            <p class="muted">Capacidad contratada: puestos, modalidad y unidades por cargo. No es el listado de personas.</p>
        </div>
        @if($canManage)
            <a class="btn" href="{{ route('sites.create') }}">Crear instalación</a>
        @endif
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Código</th>
                <th>Instalación</th>
                <th>Ciudad</th>
                <th>Puestos</th>
                <th>Unidades</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($sites as $site)
            <tr>
                <td>{{ $site->code }}</td>
                <td>{{ $site->name }}</td>
                <td>{{ $site->city ?? '—' }}</td>
                <td>{{ $site->postsLabel() }}</td>
                <td>{{ $site->unitsLabel() }}</td>
                <td>
                    <a href="{{ route('sites.show', $site) }}">Ver</a>
                    @if($canManage)
                        · <a href="{{ route('sites.edit', $site) }}">Editar</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="muted">En este cliente todavía no hay plantas, bodegas ni puestos.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</article>
@endif
@endsection
