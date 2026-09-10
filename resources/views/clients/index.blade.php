@extends('layouts.app')

@section('title', 'Clientes · SJ-SIG')

@section('content')
@php
    $hasClients = $clients->isNotEmpty();
    $searching = filled(request('q'));
@endphp

@if(! $hasClients && ! $searching)
    <x-empty-panel kicker="Sin clientes" title="No hay clientes" :require-client="false">
        <p class="muted">Aún no hay universos. Crea el primero y luego asigna usuarios, instalaciones y personal.</p>
        <a class="btn" href="{{ route('clients.create') }}" style="margin-top:14px">Nuevo cliente</a>
    </x-empty-panel>
@else
<article class="card">
    <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;margin-bottom:12px">
        <form method="get" style="display:flex;gap:8px;flex:1">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Nombre o NIT" style="flex:1">
            <button class="btn" type="submit">Buscar</button>
        </form>
        <a class="btn" href="{{ route('clients.create') }}">Nuevo cliente</a>
    </div>
    <table class="data">
        <thead><tr><th>Cliente</th><th>NIT</th><th>Usuarios</th><th></th></tr></thead>
        <tbody>
        @forelse($clients as $client)
            <tr>
                <td>{{ $client->name }}</td>
                <td>{{ $client->nit ?? '—' }}</td>
                <td>{{ $client->users_count }}</td>
                <td><a href="{{ route('clients.edit', $client) }}">Editar</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">No hay coincidencias para esa búsqueda.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px">{{ $clients->links() }}</div>
</article>
@endif
@endsection
