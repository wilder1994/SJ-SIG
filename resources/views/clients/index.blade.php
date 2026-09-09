@extends('layouts.app')

@section('title', 'Clientes · SJ-SIG')

@section('content')
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
            <tr><td colspan="4" class="muted">Sin clientes. Cree el primero para abrir un universo.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px">{{ $clients->links() }}</div>
</article>
@endsection
