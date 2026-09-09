@extends('layouts.app')

@section('title', 'Carpeta · '.$person->full_name)

@section('content')
<article class="card" style="margin-bottom:12px;display:flex;justify-content:space-between;gap:12px;align-items:flex-end;flex-wrap:wrap">
    <div>
        <p class="kicker">Carpeta del vigilante</p>
        <h2 class="display" style="font-size:28px;margin:4px 0 0">{{ $person->full_name }}</h2>
        <p class="muted">{{ $person->document_type }} {{ $person->document_number }}</p>
    </div>
    <div style="display:flex;gap:8px">
        <a class="btn ghost" href="{{ route('documents.index') }}">Listado</a>
        @if(auth()->user()->role->canUploadEvidence() && ! $cargar)
            <a class="btn" href="{{ route('documents.folder', ['person' => $person, 'cargar' => 1]) }}">Cargar documentos</a>
        @endif
        @if($cargar)
            <a class="btn ghost" href="{{ route('documents.folder', $person) }}">Solo consulta</a>
        @endif
    </div>
</article>

@if($cargar)
<article class="card" style="margin-bottom:12px">
    <p class="kicker">Carga (usuario interno)</p>
    <p class="muted" style="margin-bottom:12px">Historia Laboral se indexa por tipo. El resto de carpetas se sube archivo a archivo. El escáner queda pendiente.</p>

    <p style="margin:0 0 4px;font-weight:500">Historia Laboral</p>
    <p class="muted">Suba un PDF (una o varias páginas). En el siguiente paso asigna tipo y nombre a cada rango.</p>
    <form method="post" action="{{ route('documents.history.batch', $person) }}" enctype="multipart/form-data" style="display:flex;gap:8px;margin:8px 0 16px;align-items:center">
        @csrf
        <input type="file" name="file" accept=".pdf" required style="flex:1">
        <button class="btn" type="submit">Subir PDF</button>
        <button class="btn ghost" type="button" disabled title="Pendiente: decisión de agente de escáner">Escanear</button>
    </form>

    <form method="post" action="{{ route('documents.courses.store', $person) }}" class="form-grid" style="margin-bottom:16px">
        @csrf
        <label class="field">Curso · título
            <input name="title" required>
        </label>
        <label class="field">Fecha
            <input type="date" name="taken_on" required>
        </label>
        <div class="span-2"><button class="btn" type="submit">Registrar curso</button></div>
    </form>

    @foreach(\App\Enums\DocumentFolder::cases() as $folder)
        @continue($folder === \App\Enums\DocumentFolder::HojaVida)
        <p style="margin:10px 0 4px;font-weight:500">{{ $folder->label() }}</p>
        <p class="muted">{{ $folder->hint() }}</p>
        <form method="post" action="{{ route('documents.store', $person) }}" enctype="multipart/form-data" style="display:flex;gap:8px;margin:6px 0 12px;align-items:center">
            @csrf
            <input type="hidden" name="folder" value="{{ $folder->value }}">
            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required style="flex:1">
            <button class="btn" type="submit">Subir</button>
        </form>
    @endforeach
</article>
@endif

<section class="split">
    <article class="card">
        <p class="kicker">Cursos</p>
        <table class="data">
            <thead><tr><th>Título</th><th>Fecha</th></tr></thead>
            <tbody>
            @forelse($person->courses as $course)
                <tr><td>{{ $course->title }}</td><td>{{ $course->taken_on->format('d/m/Y') }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">Sin cursos registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </article>
    <article class="card">
        <p class="kicker">Archivos</p>
        <div class="history-head">
            <div>
                <p style="margin:0;font-weight:500">Historia Laboral</p>
                <p class="muted">{{ $historySummary['loaded'] }}/{{ $historySummary['total'] }} indexados · {{ $historySummary['required_loaded'] }}/{{ $historySummary['required'] }} obligatorios</p>
            </div>
            <button class="btn ghost" type="button" data-toggle-panel="history-list">Listado</button>
        </div>
        <div id="history-list" hidden>
            <table class="data" style="margin-top:10px">
                <thead><tr><th>Tipo</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                @foreach($historyRows as $row)
                    <tr>
                        <td>
                            {{ $row['type']->label() }}
                            <span class="muted"> · {{ $row['type']->requirement()->label() }}</span>
                        </td>
                        <td>
                            @if($row['status'] === 'loaded')
                                Cargado
                            @elseif($row['status'] === 'na')
                                N/A
                            @else
                                Falta
                            @endif
                        </td>
                        <td style="white-space:nowrap">
                            @if($row['status'] === 'loaded' && $row['document'])
                                <button class="btn ghost icon-eye" type="button" data-preview="{{ route('documents.preview', $row['document']) }}" data-name="{{ $row['document']->label() }}" title="Ver" aria-label="Ver">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <a class="btn ghost" href="{{ route('documents.download', $row['document']) }}">Descargar</a>
                            @elseif($cargar && $row['status'] !== 'na')
                                <form method="post" action="{{ route('documents.history.na', $person) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="document_type" value="{{ $row['type']->value }}">
                                    <button class="btn ghost" type="submit">No aplica</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @foreach(\App\Enums\DocumentFolder::cases() as $folder)
            @continue($folder === \App\Enums\DocumentFolder::HojaVida)
            <p style="margin:16px 0 4px;font-weight:500">{{ $folder->label() }}</p>
            @php $files = $person->documents->filter(fn ($doc) => $doc->folder === $folder); @endphp
            @forelse($files as $file)
                <div class="file-row">
                    <span>{{ $file->original_name }}</span>
                    <span>
                        <button class="btn ghost" type="button" data-preview="{{ route('documents.preview', $file) }}" data-name="{{ $file->original_name }}">Ver</button>
                        <a class="btn ghost" href="{{ route('documents.download', $file) }}">Descargar</a>
                    </span>
                </div>
            @empty
                <p class="muted">Vacía</p>
            @endforelse
        @endforeach
    </article>
</section>
@endsection
