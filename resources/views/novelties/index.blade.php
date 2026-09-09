@extends('layouts.app')

@section('title', 'Novedades · SJ-SIG')

@section('content')
<div class="split">
    <article class="card">
        <table class="data">
            <thead><tr><th>Estado</th><th>Caso</th><th>Puesto</th></tr></thead>
            <tbody>
            @forelse($novelties as $item)
                <tr>
                    <td>{{ $item->status->label() }}</td>
                    <td>{{ $item->title }}<div class="muted">{{ $item->created_at->format('d/m/Y H:i') }}</div></td>
                    <td>{{ $item->post?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">Sin novedades.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px">{{ $novelties->links() }}</div>
    </article>
    @if(auth()->user()->role->canMutateNovelties())
    <article class="card">
        <p class="kicker">Seguimiento</p>
        <form method="post" action="{{ route('novelties.store') }}" class="field" style="gap:10px">
            @csrf
            <input name="title" placeholder="Título" required>
            <textarea name="body" rows="5" placeholder="Descripción" required></textarea>
            <button class="btn" type="submit">Abrir novedad</button>
        </form>
    </article>
    @endif
</div>
@endsection
