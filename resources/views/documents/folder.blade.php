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
<section class="upload-deck" style="margin-bottom:12px">
    <p class="kicker">Carga (usuario interno)</p>
    <p class="muted" style="margin-bottom:12px">Arrastre, pegue o seleccione. Historia Laboral, Contratación, Certificados, Cursos y capacitación y Afiliaciones pasan al indexador. El escáner queda pendiente.</p>
    <div class="drop-grid">
        @foreach(\App\Enums\DocumentFolder::cases() as $folder)
            @continue(! $folder->isIndexed())
            <form class="drop-card" method="post" action="{{ route($folder->batchRoute(), $person) }}" enctype="multipart/form-data" data-dropzone data-accept=".pdf,application/pdf" data-max="51200">
                @csrf
                <p class="drop-card-title">{{ $folder->label() }}</p>
                <p class="muted drop-card-hint">{{ $folder->hint() }}</p>
                <input class="drop-input" type="file" name="file" accept=".pdf" required>
                <div class="drop-empty">
                    <span class="drop-icon drop-icon-pdf" aria-hidden="true">PDF</span>
                    <p>Arrastre, pegue o seleccione un PDF</p>
                </div>
                <div class="drop-ready" hidden>
                    <div class="drop-file">
                        <span class="drop-icon drop-icon-pdf" data-file-icon aria-hidden="true">PDF</span>
                        <div>
                            <p data-file-name></p>
                            <p class="muted" data-file-size></p>
                        </div>
                    </div>
                </div>
                <p class="muted drop-error" hidden></p>
                <div class="drop-actions">
                    <button class="btn ghost" type="button" data-drop-clear hidden>Quitar</button>
                    <button class="btn" type="submit" disabled>Indexar</button>
                    @if($folder === \App\Enums\DocumentFolder::HojaVida)
                        <button class="btn ghost" type="button" disabled title="Pendiente: decisión de agente de escáner">Escanear</button>
                    @endif
                </div>
            </form>
        @endforeach

        @foreach(\App\Enums\DocumentFolder::cases() as $folder)
            @continue($folder->isIndexed())
            <form class="drop-card" method="post" action="{{ route('documents.store', $person) }}" enctype="multipart/form-data" data-dropzone data-accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" data-max="51200">
                @csrf
                <input type="hidden" name="folder" value="{{ $folder->value }}">
                <p class="drop-card-title">{{ $folder->label() }}</p>
                <p class="muted drop-card-hint">{{ $folder->hint() }}</p>
                <input class="drop-input" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
                <div class="drop-empty">
                    <span class="drop-icon drop-icon-pdf" aria-hidden="true">PDF</span>
                    <p>Arrastre, pegue o seleccione</p>
                </div>
                <div class="drop-ready" hidden>
                    <div class="drop-file">
                        <span class="drop-icon drop-icon-pdf" data-file-icon aria-hidden="true">PDF</span>
                        <div>
                            <p data-file-name></p>
                            <p class="muted" data-file-size></p>
                        </div>
                    </div>
                </div>
                <p class="muted drop-error" hidden></p>
                <div class="drop-actions">
                    <button class="btn ghost" type="button" data-drop-clear hidden>Quitar</button>
                    <button class="btn" type="submit" disabled>Subir</button>
                </div>
            </form>
        @endforeach
    </div>
</section>
@endif

<section>
    <article class="card">
        <p class="kicker">Archivos</p>
        @foreach($indexedChecklists as $block)
            <div class="history-head" @if(! $loop->first) style="margin-top:16px" @endif>
                <div>
                    <p style="margin:0;font-weight:500">{{ $block['folder']->label() }}</p>
                    <p class="muted">{{ $block['summary']['loaded'] }}/{{ $block['summary']['total'] }} indexados · {{ $block['summary']['required_loaded'] }}/{{ $block['summary']['required'] }} obligatorios</p>
                </div>
                <button class="btn ghost" type="button" data-toggle-panel="{{ $block['panel'] }}">Listado</button>
            </div>
            <div id="{{ $block['panel'] }}" hidden>
                @php $isCourse = $block['folder'] === \App\Enums\DocumentFolder::Cursos; @endphp
                <table class="data" style="margin-top:10px">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            @if($isCourse)
                                <th>Entidad</th>
                                <th>Fecha</th>
                            @endif
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($block['rows'] as $row)
                        <tr>
                            <td>
                                @if($isCourse && $row['document'] && method_exists($row['type'], 'isRepeatable') && $row['type']->isRepeatable())
                                    {{ $row['document']->label() }}
                                @else
                                    {{ $row['type']->label() }}
                                @endif
                                <span class="muted"> · {{ $row['type']->requirement()->label() }}</span>
                            </td>
                            @if($isCourse)
                                <td>{{ $row['document']?->provider ?: '—' }}</td>
                                <td>{{ $row['document']?->taken_on?->format('d/m/Y') ?: '—' }}</td>
                            @endif
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
                                @elseif($cargar && $row['status'] !== 'na' && ! (method_exists($row['type'], 'isRepeatable') && $row['type']->isRepeatable()))
                                    <form method="post" action="{{ route($block['na'], $person) }}" style="display:inline">
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
        @endforeach

        @foreach(\App\Enums\DocumentFolder::cases() as $folder)
            @continue($folder->isIndexed())
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
