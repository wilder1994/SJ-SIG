@extends('layouts.app')

@section('title', 'Carpeta · '.$person->full_name)

@section('content')
@php
    $canUpload = auth()->user()->role->canUploadEvidence();
@endphp
<article class="card" style="margin-bottom:12px;display:flex;justify-content:space-between;gap:12px;align-items:flex-end;flex-wrap:wrap">
    <div>
        <p class="kicker">Carpeta del vigilante</p>
        <h2 class="display" style="font-size:28px;margin:4px 0 0">{{ $person->full_name }}</h2>
        <p class="muted">{{ $person->document_type }} {{ $person->document_number }}</p>
    </div>
    <div style="display:flex;gap:8px">
        <a class="btn ghost" href="{{ route('documents.index') }}">Listado</a>
        @if($canUpload)
            <button class="btn" type="button" data-open-upload>Cargar documentos</button>
        @endif
    </div>
</article>

<section>
    <article class="card">
        <p class="kicker">Archivos</p>
        @foreach($indexedChecklists as $block)
            <div class="history-head" @if(! $loop->first) style="margin-top:16px" @endif>
                <div>
                    <p style="margin:0;font-weight:500">{{ $block['folder']->label() }}</p>
                    @if($block['folder'] === \App\Enums\DocumentFolder::Otros)
                        <p class="muted">{{ $block['summary']['loaded'] }}/{{ $block['summary']['total'] }} soportes</p>
                    @else
                        <p class="muted">{{ $block['summary']['loaded'] }}/{{ $block['summary']['total'] }} indexados · {{ $block['summary']['required_loaded'] }}/{{ $block['summary']['required'] }} obligatorios</p>
                    @endif
                </div>
                <button class="btn ghost" type="button" data-toggle-panel="{{ $block['panel'] }}">Listado</button>
            </div>
            <div id="{{ $block['panel'] }}" hidden>
                @php
                    $isCourse = $block['folder'] === \App\Enums\DocumentFolder::Cursos;
                    $isOther = $block['folder'] === \App\Enums\DocumentFolder::Otros;
                @endphp
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
                                @if(($isCourse || $isOther) && $row['document'] && method_exists($row['type'], 'isRepeatable') && $row['type']->isRepeatable())
                                    {{ $row['document']->label() }}
                                @else
                                    {{ $row['type']->label() }}
                                @endif
                                @unless($isOther)
                                    <span class="muted"> · {{ $row['type']->requirement()->label() }}</span>
                                @endunless
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
                                @elseif($canUpload && $row['status'] !== 'na' && ! (method_exists($row['type'], 'isRepeatable') && $row['type']->isRepeatable()))
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

    </article>
</section>

@if($canUpload)
<div class="preview-layer" id="upload-layer" hidden @if($cargar) data-open @endif>
    <div class="upload-frame">
        <div class="preview-bar">
            <span>Cargar documentos</span>
            <button class="btn ghost" type="button" data-close-upload style="color:#e8eef6;border-color:rgba(88,196,255,.35)">Cerrar</button>
        </div>
        <div class="upload-body">
            <p class="muted" style="margin:0 0 12px">Un PDF por lote. En el indexador elige carpeta y tipo por grupo de páginas. El escáner queda pendiente.</p>
            <form class="drop-card" method="post" action="{{ route('documents.batch.create', $person) }}" enctype="multipart/form-data" data-dropzone data-accept=".pdf,application/pdf" data-max="51200">
                @csrf
                <p class="drop-card-title">Indexar lote</p>
                <p class="muted drop-card-hint">Arrastre, pegue o seleccione un PDF. Luego asigne cada página a una carpeta y un tipo.</p>
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
                    <button class="btn ghost" type="button" disabled title="Pendiente: decisión de agente de escáner">Escanear</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
