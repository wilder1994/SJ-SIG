@extends('layouts.app')

@section('title', 'Indexar · '.$person->full_name)

@section('content')
<article class="card" style="margin-bottom:12px">
    <p class="kicker">Historia Laboral</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">Indexar lote</h2>
    <p class="muted">{{ $person->full_name }} · {{ $person->document_type }} {{ $person->document_number }} · {{ $batch->original_name }} · {{ $batch->page_count }} página{{ $batch->page_count === 1 ? '' : 's' }}</p>
    <p class="muted" style="margin-top:8px">Seleccione un rango de páginas, el tipo de documento y confirme el nombre. Puede agregar varios cortes del mismo PDF.</p>
</article>

<article class="card">
    <p class="kicker">Páginas</p>
    <div class="page-chips" id="page-chips">
        @for($page = 1; $page <= $batch->page_count; $page++)
            <button type="button" class="page-chip" data-page="{{ $page }}">{{ $page }}</button>
        @endfor
    </div>
    <p class="muted" id="range-hint" style="margin:8px 0 14px">Clic en la primera y la última página del documento.</p>

    <form method="post" action="{{ route('documents.history.store', ['person' => $person, 'batch' => $batch]) }}" id="index-form">
        @csrf
        <div id="slice-rows"></div>
        <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap">
            <button class="btn ghost" type="button" id="add-slice">Agregar a la lista</button>
            <button class="btn" type="submit">Guardar indexación</button>
            <a class="btn ghost" href="{{ route('documents.folder', ['person' => $person, 'cargar' => 1]) }}">Cancelar</a>
        </div>
    </form>
</article>

<script type="application/json" id="history-meta">@json($historyMeta)</script>
@endsection
