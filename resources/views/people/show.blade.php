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
        <p class="kicker">Ficha</p>
        <table class="data">
            <tr><th>Correo</th><td>{{ $person->email ?? '—' }}</td></tr>
            <tr><th>Teléfono</th><td>{{ $person->phone ?? '—' }}</td></tr>
            <tr><th>Ingreso</th><td>{{ $person->hired_on?->format('d/m/Y') ?? '—' }}</td></tr>
            <tr><th>EPS</th><td>{{ $person->eps_name ?? '—' }}</td></tr>
            <tr><th>Pensión</th><td>{{ $person->afp_name ?? '—' }}</td></tr>
            <tr><th>Caja</th><td>{{ $person->compensation_fund ?? '—' }}</td></tr>
            <tr><th>ARL</th><td>{{ $person->arl_name ?? '—' }}</td></tr>
        </table>
        <p class="muted" style="margin-top:12px">Los certificados PDF, HV y cursos están en la carpeta documental, no en esta ficha.</p>
    </article>
    <article class="card">
        <p class="kicker">Expediente</p>
        <p class="display" style="font-size:36px;margin:8px 0 4px">{{ $person->documents->count() }}</p>
        <p class="muted">archivos en carpeta</p>
        <a class="btn" href="{{ route('documents.folder', $person) }}" style="margin-top:14px">Abrir carpeta</a>
    </article>
</section>
@endsection
