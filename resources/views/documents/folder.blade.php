@extends('layouts.app')

@section('title', 'Carpeta · '.$person->full_name)

@section('content')
@php
    $canUpload = auth()->user()->role->canUploadEvidence();
@endphp
<article class="card person-hero">
    <div class="person-hero-id">
        @include('people._avatar', [
            'person' => $person,
            'canEdit' => $canUpload,
            'autosubmit' => true,
            'action' => route('people.photo.store', $person),
        ])
        <div>
            <p class="kicker">Carpeta del vigilante</p>
            <h2 class="display" style="font-size:28px;margin:4px 0 0">{{ $person->full_name }}</h2>
            <p class="muted">{{ $person->document_type }} {{ $person->document_number }}</p>
        </div>
    </div>
    <div style="display:flex;gap:8px">
        <a class="btn ghost" href="{{ route('documents.index') }}">Volver</a>
        @if($canUpload)
            <button class="btn" type="button" data-open-upload>Cargar documentos</button>
        @endif
    </div>
</article>

<section>
    <p class="kicker" style="margin-bottom:10px">Carpetas</p>
    <div class="folder-grid">
        @foreach($indexedChecklists as $block)
            <article class="folder-card">
                <div class="folder-card-icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24">
                        <path fill="#e8b923" d="M3 7.25A1.75 1.75 0 0 1 4.75 5.5H9l1.7 1.7h8.55A1.75 1.75 0 0 1 21 8.95v9.3A1.75 1.75 0 0 1 19.25 20H4.75A1.75 1.75 0 0 1 3 18.25v-11Z"/>
                        <path fill="#f5c84a" d="M3 9.5h18v8.75A1.75 1.75 0 0 1 19.25 20H4.75A1.75 1.75 0 0 1 3 18.25V9.5Z"/>
                    </svg>
                </div>
                <div class="folder-card-body">
                    <p class="folder-card-name">{{ $block['folder']->label() }}</p>
                    <p class="muted">{{ \App\Support\Personnel\FolderChecklist::countLabel($block['summary']) }}</p>
                </div>
                <button class="folder-link" type="button" data-open-folder="{{ $block['panel'] }}">Abrir</button>
            </article>
        @endforeach
    </div>
</section>

<div class="preview-layer" id="folder-layer" hidden>
    @foreach($indexedChecklists as $block)
        @php
            $isCourse = $block['folder'] === \App\Enums\DocumentFolder::Cursos;
            $isOther = $block['folder'] === \App\Enums\DocumentFolder::Otros;
            $files = array_values(array_filter($block['rows'], fn ($row) => $row['status'] === 'loaded' && $row['document']));
            $pending = array_values(array_filter(
                $block['rows'],
                fn ($row) => $row['status'] !== 'loaded' && ! (method_exists($row['type'], 'isRepeatable') && $row['type']->isRepeatable()),
            ));
        @endphp
        <div class="folder-frame" data-folder-pane="{{ $block['panel'] }}" hidden>
            <div class="preview-bar">
                <span class="folder-modal-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#e8b923" d="M3 7.25A1.75 1.75 0 0 1 4.75 5.5H9l1.7 1.7h8.55A1.75 1.75 0 0 1 21 8.95v9.3A1.75 1.75 0 0 1 19.25 20H4.75A1.75 1.75 0 0 1 3 18.25v-11Z"/>
                        <path fill="#f5c84a" d="M3 9.5h18v8.75A1.75 1.75 0 0 1 19.25 20H4.75A1.75 1.75 0 0 1 3 18.25V9.5Z"/>
                    </svg>
                    {{ $block['folder']->label() }}
                    <span class="muted" style="color:#b8c2d4;font-weight:400"> · {{ \App\Support\Personnel\FolderChecklist::countLabel($block['summary']) }}</span>
                </span>
                <button class="btn ghost" type="button" data-close-folder style="color:#e8eef6;border-color:rgba(88,196,255,.35)">Cerrar</button>
            </div>
            <div class="folder-modal-body">
                <input type="search" class="folder-search" placeholder="Buscar documento" data-folder-search>
                <div class="folder-doc-list">
                    @forelse($files as $row)
                        @php $doc = $row['document']; @endphp
                        <div class="folder-doc-row" data-doc-search="{{ mb_strtolower($doc->label().' '.$row['type']->label()) }}">
                            <span class="drop-icon drop-icon-pdf" aria-hidden="true">PDF</span>
                            <div>
                                <p>{{ $doc->label() }}</p>
                                @if($isCourse)
                                    <p class="muted">{{ $doc->provider ?: '—' }} · {{ $doc->taken_on?->format('d/m/Y') ?: '—' }}</p>
                                @endif
                            </div>
                            <div class="folder-doc-actions">
                                <button class="folder-link" type="button" data-preview="{{ route('documents.preview', $doc) }}" data-name="{{ $doc->label() }}">Ver</button>
                                <a class="folder-link" href="{{ route('documents.download', $doc) }}">Descargar</a>
                                @if($canUpload && $doc->canDelete())
                                    <form method="post" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('¿Eliminar este PDF? Solo puede hacerlo durante 12 horas.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="folder-link is-danger" type="submit">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="muted" data-empty-files>No hay PDF en esta carpeta.</p>
                    @endforelse
                </div>
                @if($canUpload && ! $isOther && count($pending))
                    <p class="kicker" style="margin:16px 0 8px">Pendientes</p>
                    @foreach($pending as $row)
                        <div class="folder-doc-row is-pending" data-doc-search="{{ mb_strtolower($row['type']->label()) }}">
                            <div>
                                <p>{{ $row['type']->label() }}</p>
                                <p class="muted">{{ $row['status'] === 'na' ? 'N/A' : 'Falta' }} · {{ $row['type']->requirement()->label() }}</p>
                            </div>
                            @if($row['status'] !== 'na' && $block['na'])
                                <form method="post" action="{{ route($block['na'], $person) }}">
                                    @csrf
                                    <input type="hidden" name="document_type" value="{{ $row['type']->value }}">
                                    <button class="folder-link" type="submit">No aplica</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach
</div>

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
