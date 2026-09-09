@extends('layouts.app')

@section('title', 'Indexar · '.$person->full_name)

@section('content')
<section class="index-workspace">
    <div class="index-side">
        <article class="card index-meta">
            <p class="kicker">{{ $folder->label() }}</p>
            <h2 class="display" style="font-size:22px;margin:4px 0 8px">Indexar lote</h2>
            <p class="muted">{{ $person->full_name }} · {{ $person->document_type }} {{ $person->document_number }}</p>
            <p class="muted">{{ $batch->original_name }} · {{ $batch->page_count }} página{{ $batch->page_count === 1 ? '' : 's' }}</p>
            <p class="muted" style="margin-top:8px">Marque las páginas que forman un documento (pueden no ser consecutivas). Clic para seleccionar; Shift+clic para un tramo. Luego asigne tipo y agrégalo a la lista.@if($historyMeta['other_fields'] ?? false) En Otros escriba el tipo; el nombre se arma con el tipo, la cédula y el nombre. No use un tipo de otra carpeta.@endif</p>
        </article>

        <article class="card index-composer">
            <form method="post" action="{{ $storeUrl }}" id="index-form">
                @csrf
                <div class="index-composer-form">
                    <p class="muted" id="page-hint">Ninguna página seleccionada.</p>
                    @if($errors->any())
                        <p class="muted" style="color:#b42318">{{ $errors->first() }}</p>
                    @endif
                    <label class="field" @if($historyMeta['other_fields'] ?? false) hidden @endif>Tipo para la selección
                        <select id="slice-type"></select>
                    </label>
                    <label class="field" @if(! ($historyMeta['other_fields'] ?? false)) hidden @endif>Tipo
                        <input id="slice-tipo" maxlength="80" placeholder="Ej. RUT Cámara de Comercio">
                    </label>
                    <label class="field">Nombre
                        <input id="slice-name" required>
                    </label>
                    <div id="course-fields" @if(! ($historyMeta['course_fields'] ?? false)) hidden @endif>
                        <label class="field">Fecha del curso
                            <input type="date" id="slice-taken-on">
                        </label>
                        <label class="field">Entidad que dicta el curso
                            <input id="slice-provider">
                        </label>
                    </div>
                    <div class="index-composer-actions">
                        <button class="btn ghost" type="button" id="add-slice">Agregar a la lista</button>
                        <button class="btn" type="submit">Guardar indexación</button>
                        <a class="btn ghost" href="{{ route('documents.folder', ['person' => $person, 'cargar' => 1]) }}">Cancelar</a>
                    </div>
                </div>
                <div id="slice-rows" class="index-slice-list"></div>
            </form>
        </article>
    </div>

    <article class="card index-pages">
        <p class="kicker">Páginas del lote</p>
        <div class="index-pages-scroll">
            <div class="page-thumbs" id="page-thumbs">
                @for($page = 1; $page <= $batch->page_count; $page++)
                    <div class="page-thumb" data-page="{{ $page }}">
                        <button type="button" class="page-thumb-hit" data-page="{{ $page }}" aria-pressed="false" title="Página {{ $page }}">
                            <canvas width="140" height="180" aria-hidden="true"></canvas>
                            <span class="page-thumb-fallback">{{ $page }}</span>
                        </button>
                        <div class="page-thumb-bar">
                            <span>{{ $page }}</span>
                            <button class="btn ghost" type="button" data-preview="{{ $previewUrl }}#page={{ $page }}" data-name="Página {{ $page }} · {{ $batch->original_name }}">Ampliar</button>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </article>
</section>

<script type="application/json" id="history-meta">@json($historyMeta)</script>
@endsection
