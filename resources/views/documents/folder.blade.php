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
    <p class="muted" style="margin-bottom:10px">HV, certificados, actas de curso, certificados de EPS/pensión/caja y otros. Los títulos de curso se registran aquí.</p>
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
        @foreach(\App\Enums\DocumentFolder::cases() as $folder)
            <p style="margin:12px 0 4px;font-weight:500">{{ $folder->label() }}</p>
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
