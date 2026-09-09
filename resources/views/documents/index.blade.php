@extends('layouts.app')

@section('title', 'Documentos · SJ-SIG')

@section('content')
<article class="card">
    <form method="get" style="display:flex;gap:8px;margin-bottom:12px">
        <input type="search" name="q" value="{{ $q }}" placeholder="Carpeta, archivo o persona" style="flex:1">
        <button class="btn" type="submit">Buscar</button>
    </form>
    <table class="data">
        <thead><tr><th>Persona</th><th>Carpeta</th><th>Archivo</th></tr></thead>
        <tbody>
        @forelse($documents as $doc)
            <tr>
                <td>{{ $doc->person->full_name }}</td>
                <td>{{ $doc->folder->label() }}</td>
                <td><a href="{{ route('documents.download', $doc) }}">{{ $doc->original_name }}</a></td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">Sin documentos.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px">{{ $documents->links() }}</div>
</article>
@endsection
