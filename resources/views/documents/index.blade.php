@extends('layouts.app')

@section('title', 'Documentos · SJ-SIG')

@section('content')
<article class="card">
    <p class="kicker">Repositorio del contrato</p>
    <h2 class="display" style="font-size:24px;margin:4px 0 10px">Carpetas del personal</h2>
    <form method="get" style="display:flex;gap:8px;margin-bottom:12px">
        <input type="search" name="q" value="{{ $q }}" placeholder="Nombre o cédula" style="flex:1">
        <button class="btn" type="submit">Buscar</button>
    </form>
    <table class="data">
        <thead><tr><th>Identificación</th><th>Nombre</th><th>Archivos</th><th></th></tr></thead>
        <tbody>
        @forelse($people as $person)
            <tr>
                <td>{{ $person->document_type }} {{ $person->document_number }}</td>
                <td>{{ $person->full_name }}</td>
                <td>{{ $person->documents_count }}</td>
                <td><a href="{{ route('documents.folder', $person) }}">Ver carpeta</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">Sin personal en este contrato.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px">{{ $people->links() }}</div>
</article>
@endsection
