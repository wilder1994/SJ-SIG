@extends('layouts.app')

@section('title', 'Usuarios · SJ-SIG')

@section('content')
@php
    $hasUsers = $users->isNotEmpty();
    $searching = filled(request('q'));
@endphp

@if(! $hasUsers && ! $searching)
    <x-empty-panel kicker="Sin usuarios" title="No hay usuarios" :require-client="false">
        <p class="muted">Todavía no hay cuentas de plataforma. Crea la primera para que el equipo o el cliente puedan entrar.</p>
        <a class="btn" href="{{ route('users.create') }}" style="margin-top:14px">Nuevo usuario</a>
    </x-empty-panel>
@else
<article class="card">
    <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;margin-bottom:12px">
        <form method="get" style="display:flex;gap:8px;flex:1">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Nombre, cédula o correo" style="flex:1">
            <button class="btn" type="submit">Buscar</button>
        </form>
        <a class="btn" href="{{ route('users.create') }}">Nuevo usuario</a>
    </div>
    <table class="data">
        <thead><tr><th></th><th>Identidad</th><th>Rol</th><th>Cliente</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse($users as $item)
            <tr>
                <td>
                    @if($item->photo_path)
                        <img class="avatar-sm" src="{{ route('users.photo', $item) }}" alt="">
                    @else
                        <span class="avatar-sm avatar-fallback">{{ $item->initials() }}</span>
                    @endif
                </td>
                <td>
                    {{ $item->name }}<br>
                    <span class="muted">{{ $item->document_type }} {{ $item->document_number }} · {{ $item->email }}</span>
                </td>
                <td>{{ $item->role->label() }}@if($item->job_title)<br><span class="muted">{{ $item->job_title }}</span>@endif</td>
                <td>{{ $item->tenant?->name ?? 'Todos' }}</td>
                <td>{{ $item->is_active ? 'Activo' : 'Suspendido' }}</td>
                <td><a href="{{ route('users.edit', $item) }}">Editar</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No hay coincidencias para esa búsqueda.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px">{{ $users->links() }}</div>
</article>
@endif
@endsection
