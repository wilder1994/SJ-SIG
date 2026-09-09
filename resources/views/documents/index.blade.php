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
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Carpetas</th>
                <th>Documentos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        @forelse($people as $person)
            <tr>
                <td><span class="muted">{{ $person->document_type }}</span> {{ $person->document_number }}</td>
                <td>{{ $person->full_name }}</td>
                <td>{{ (int) $person->folders_count }}/{{ $folderTotal }}</td>
                <td>{{ $person->documents_count }}</td>
                <td>
                    <a class="folder-link" href="{{ route('documents.folder', $person) }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#e8b923" d="M3 7.25A1.75 1.75 0 0 1 4.75 5.5H9l1.7 1.7h8.55A1.75 1.75 0 0 1 21 8.95v9.3A1.75 1.75 0 0 1 19.25 20H4.75A1.75 1.75 0 0 1 3 18.25v-11Z"/>
                            <path fill="#f5c84a" d="M3 9.5h18v8.75A1.75 1.75 0 0 1 19.25 20H4.75A1.75 1.75 0 0 1 3 18.25V9.5Z"/>
                        </svg>
                        Ver carpeta
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">Sin personal en este contrato.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px">{{ $people->links() }}</div>
</article>
@endsection
