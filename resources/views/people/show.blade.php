@extends('layouts.app')

@section('title', $person->full_name.' · SJ-SIG')

@section('content')
<article class="card" style="margin-bottom:12px">
    <p class="kicker">{{ $person->document_type }} {{ $person->document_number }}</p>
    <h2 class="display" style="font-size:30px;margin:4px 0 8px">{{ $person->full_name }}</h2>
    <p class="muted">{{ $person->isActive() ? 'Activo' : 'Retiro '.$person->left_on?->format('d/m/Y') }} · cargo {{ $person->job_code ?? '—' }}</p>
</article>
<section class="split">
    <article class="card">
        <p class="kicker">Afiliaciones</p>
        <table class="data">
            <tr><th>EPS</th><td>{{ $person->eps_name ?? '—' }}</td></tr>
            <tr><th>Pensión</th><td>{{ $person->afp_name ?? '—' }}</td></tr>
            <tr><th>Caja</th><td>{{ $person->compensation_fund ?? '—' }}</td></tr>
            <tr><th>ARL</th><td>{{ $person->arl_name ?? '—' }}</td></tr>
        </table>
        <p class="kicker" style="margin-top:16px">Cursos</p>
        <table class="data">
            <thead><tr><th>Título</th><th>Fecha</th></tr></thead>
            <tbody>
            @forelse($person->courses as $course)
                <tr><td>{{ $course->title }}</td><td>{{ $course->taken_on->format('d/m/Y') }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">Sin cursos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </article>
    <article class="card">
        <p class="kicker">Gestor documental</p>
        @foreach(\App\Enums\DocumentFolder::cases() as $folder)
            <p style="margin:10px 0 4px;font-weight:500">{{ $folder->label() }}</p>
            @php $files = $person->documents->filter(fn ($doc) => $doc->folder === $folder); @endphp
            @forelse($files as $file)
                <p><a href="{{ route('documents.download', $file) }}">{{ $file->original_name }}</a></p>
            @empty
                <p class="muted">Carpeta vacía</p>
            @endforelse
        @endforeach
    </article>
</section>
@endsection
