@extends('layouts.app')

@section('title', 'Perfil · SJ-SIG')

@section('content')
<article class="card" style="max-width:640px">
    <p class="kicker">Mi perfil</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">{{ $user->name }}</h2>
    <p class="muted">Solo consulta. Cualquier cambio lo hace Administración.</p>
    <div class="avatar-picker" style="margin:16px 0">
        @if($user->photo_path)
            <img class="avatar-lg" src="{{ route('users.photo', $user) }}" alt="">
        @else
            <span class="avatar-lg avatar-fallback">{{ $user->initials() }}</span>
        @endif
    </div>
    <table class="data">
        <tr><th>Cédula</th><td>{{ $user->document_type }} {{ $user->document_number }}</td></tr>
        <tr><th>Correo</th><td>{{ $user->email }}</td></tr>
        <tr><th>Teléfono</th><td>{{ $user->phone ?? '—' }}</td></tr>
        <tr><th>Cargo</th><td>{{ $user->job_title ?? '—' }}</td></tr>
        <tr><th>Rol</th><td>{{ $user->role->label() }}</td></tr>
        <tr><th>Cliente</th><td>{{ $user->tenant?->name ?? 'Todos los clientes' }}</td></tr>
        <tr><th>Estado</th><td>{{ $user->is_active ? 'Activo' : 'Suspendido' }}</td></tr>
    </table>
</article>
@endsection
