@extends('layouts.app')

@section('title', 'Equipo de operaciones · SJ-SIG')

@section('content')
<article class="card">
    <p class="kicker">Personal SJ en este cliente</p>
    <h2 class="display" style="font-size:24px;margin:4px 0 10px">Equipo de operaciones</h2>
    <p class="muted" style="margin-bottom:14px">Jefes, coordinadores, analistas y supervisores de patrulla asignados a este servicio. Gestión humana no aparece en este listado.</p>
    <div class="ops-grid">
        @forelse($members as $member)
            <article class="ops-card">
                @if($member->photo_path)
                    <img class="avatar-lg" src="{{ route('users.photo', $member) }}" alt="">
                @else
                    <span class="avatar-lg avatar-fallback">{{ $member->initials() }}</span>
                @endif
                <div>
                    <strong>{{ $member->name }}</strong>
                    <p class="muted">{{ $member->job_title ?: $member->role->label() }}</p>
                    <p class="muted">{{ $member->document_type }} {{ $member->document_number }}</p>
                    <p>{{ $member->email }}</p>
                </div>
            </article>
        @empty
            <p class="muted">Sin personal de operaciones asignado a este cliente.</p>
        @endforelse
    </div>
</article>
@endsection
