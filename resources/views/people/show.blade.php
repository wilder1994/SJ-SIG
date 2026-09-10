@extends('layouts.app')

@section('title', $person->full_name.' · SJ-SIG')

@section('content')
@php
    $canEdit = auth()->user()->role->canUploadEvidence();
    $fact = fn (?string $value) => filled($value) ? $value : '—';
    $day = fn ($carbon) => $carbon?->format('d/m/Y') ?? '—';
    $codeName = function (?string $name, ?string $code) {
        if (! filled($name) && ! filled($code)) {
            return '—';
        }

        return filled($code) ? $name.' · '.$code : $name;
    };
@endphp
<article class="card person-sheet">
    <div class="person-head">
        <div class="person-hero-id">
            @include('people._avatar', [
                'person' => $person,
                'canEdit' => $canEdit,
                'autosubmit' => true,
                'action' => route('people.photo.store', $person),
            ])
            <div>
                <p class="kicker">{{ $person->document_type }} {{ $person->document_number }}</p>
                <h2 class="display" style="font-size:26px;margin:4px 0 6px">{{ $person->full_name }}</h2>
                <p class="muted">{{ $person->isActive() ? 'Activo' : 'Retiro '.$person->left_on?->format('d/m/Y') }} · cargo {{ $person->job_code ?? '—' }}</p>
            </div>
        </div>
        <div class="person-actions">
            @if($canEdit)
                <a class="btn" href="{{ route('people.edit', $person) }}">Editar</a>
            @endif
            <a class="btn ghost" href="{{ route('documents.folder', $person) }}">Carpeta ({{ $person->documents->count() }})</a>
            <a class="btn ghost" href="{{ route('people.index') }}">Volver</a>
        </div>
    </div>

    <section class="person-block">
        <p class="kicker">Identidad</p>
        <dl class="person-facts">
            <div><dt>Nacimiento</dt><dd>{{ $day($person->birth_date) }}</dd></div>
            <div><dt>Expedición</dt><dd>{{ $day($person->document_issued_on) }}</dd></div>
            <div><dt>Lugar de expedición</dt><dd>{{ $fact($person->document_issue_place) }}</dd></div>
            <div><dt>Tipo de sangre</dt><dd>{{ $fact($person->blood_type) }}</dd></div>
            <div><dt>Sexo</dt><dd>{{ $fact($person->sex) }}</dd></div>
            <div><dt>Escolaridad</dt><dd>{{ $fact($person->education) }}</dd></div>
            <div><dt>Estado civil</dt><dd>{{ $fact($person->marital_status) }}</dd></div>
            <div><dt>Hijos</dt><dd>{{ $person->children_count ?? '—' }}</dd></div>
        </dl>
    </section>

    <section class="person-block">
        <p class="kicker">Contacto y residencia</p>
        <dl class="person-facts">
            <div><dt>Teléfono</dt><dd>{{ $fact($person->phone) }}</dd></div>
            <div class="is-long"><dt>Correo</dt><dd>{{ $fact($person->email) }}</dd></div>
            <div><dt>Residencia</dt><dd>{{ $fact($person->residence_city) }}</dd></div>
            <div class="is-wide"><dt>Dirección</dt><dd>{{ $fact($person->address) }}</dd></div>
        </dl>
    </section>

    <section class="person-block">
        <p class="kicker">Vinculación laboral</p>
        <dl class="person-facts">
            <div><dt>Cargo</dt><dd>{{ $fact($person->job_code) }}</dd></div>
            <div><dt>Vinculación</dt><dd>{{ $fact($person->engagement_type) }}</dd></div>
            <div><dt>Cotizante</dt><dd>{{ $fact($person->contributor_type) }}</dd></div>
            <div><dt>Tipo de contrato</dt><dd>{{ $fact($person->labor_contract_type) }}</dd></div>
            <div><dt>Ingreso</dt><dd>{{ $day($person->hired_on) }}</dd></div>
            <div><dt>Vencimiento</dt><dd>{{ $day($person->labor_contract_ends_on) }}</dd></div>
            <div><dt>Retiro</dt><dd>{{ $day($person->left_on) }}</dd></div>
        </dl>
    </section>

    <section class="person-block">
        <p class="kicker">Seguridad social</p>
        <dl class="person-facts">
            <div><dt>EPS</dt><dd>{{ $codeName($person->eps_name, $person->eps_code) }}</dd></div>
            <div><dt>Pensión</dt><dd>{{ $codeName($person->afp_name, $person->afp_code) }}</dd></div>
            <div><dt>Caja</dt><dd>{{ $fact($person->compensation_fund) }}</dd></div>
            <div><dt>ARL</dt><dd>{{ $codeName($person->arl_name, $person->arl_risk_level) }}</dd></div>
        </dl>
    </section>

    <p class="muted" style="margin:14px 0 0">Los certificados PDF, HV y cursos están en la carpeta documental, no en esta ficha.</p>
</article>
@endsection
