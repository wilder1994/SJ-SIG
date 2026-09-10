@extends('layouts.app')

@section('title', 'Tablero · SJ-SIG')

@section('content')
@php
    $health = $snapshot->documentaryHealth;
    $healthTone = $health >= 80 ? 'ok' : ($health >= 50 ? 'warn' : 'bad');
@endphp

<section class="dash-hero">
    <article class="card dash-map-card" data-overview-wrap>
        <div class="dash-map-bar">
            <div>
                <p class="kicker">Ubicaciones</p>
                <h2 class="display" style="font-size:22px;margin:4px 0 0">Cliente e instalaciones</h2>
            </div>
            <div class="dash-map-tools">
                <p class="muted" style="margin:0">
                    <span class="map-legend"><span class="map-dot client"></span> Cliente</span>
                    <span class="map-legend"><span class="map-dot site"></span> Instalaciones</span>
                </p>
                <div class="map-type" role="group" aria-label="Tipo de mapa">
                    <button class="is-on" type="button" data-map-type-btn="hybrid">Satélite</button>
                    <button type="button" data-map-type-btn="roadmap">Calle</button>
                </div>
            </div>
        </div>
        @if(count($snapshot->mapPoints) === 0)
            <p class="muted">Todavía no hay coordenadas. Georreferencia el cliente o las instalaciones para verlas en el mapa.</p>
        @endif
        <div
            class="overview-map"
            data-overview-map
            data-points='@json($snapshot->mapPoints)'
        ></div>
    </article>

    <aside class="dash-side">
        <article class="card dash-kpi">
            <p class="kicker">Personal activo</p>
            <div class="kpi-value">{{ $snapshot->activePeople }}</div>
            <p class="muted">Vigilantes del contrato, sin fecha de retiro.</p>
        </article>
        <article class="card dash-kpi">
            <p class="kicker">Puestos sin servicio del mes</p>
            <div class="kpi-value">{{ $snapshot->postsWithoutMonthService }}</div>
            <p class="muted">Huecos del ítem 6. El detalle está en la tabla de abajo.</p>
        </article>
        <article class="card dash-kpi">
            <p class="kicker">Parafiscales en expediente</p>
            <div class="kpi-value">{{ $snapshot->parafiscalMonthsOnFile }}</div>
            <p class="muted">Periodos PILA cargados para este contrato.</p>
        </article>
        <article class="card dash-health">
            <p class="kicker">Salud afiliatoria</p>
            <div class="health-ring health-{{ $healthTone }}" style="--pct: {{ $health }}">
                <div class="health-ring-inner">
                    <strong>{{ $health }}%</strong>
                </div>
            </div>
            <p class="muted">Personal activo con EPS, pensión y caja. No incluye carpetas de HV.</p>
        </article>
    </aside>
</section>

<section class="dash-ops">
    <article class="card">
        <p class="kicker">Servicios del mes · por puesto</p>
        <table class="data">
            <thead><tr><th>Puesto</th><th>Cantidad</th></tr></thead>
            <tbody>
            @forelse($snapshot->servicesByPost as $row)
                <tr><td>{{ $row['post'] }}</td><td>{{ $row['quantity'] }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">Sin registros del mes.</td></tr>
            @endforelse
            </tbody>
        </table>
    </article>
    <article class="card">
        <p class="kicker">Documentos por vencer (30 días)</p>
        <table class="data">
            <thead><tr><th>Archivo</th><th>Días</th></tr></thead>
            <tbody>
            @forelse($snapshot->expiringDocuments as $row)
                <tr><td>{{ $row['name'] }}</td><td>{{ $row['days'] }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">Nada en el horizonte.</td></tr>
            @endforelse
            </tbody>
        </table>
    </article>
    <article class="card">
        <p class="kicker">Mantenimientos a vigilar</p>
        <table class="data">
            <thead><tr><th>Activo</th><th>Próxima</th><th>Evidencia</th></tr></thead>
            <tbody>
            @forelse($snapshot->maintenanceWatch as $row)
                <tr>
                    <td>{{ $row['asset'] }}</td>
                    <td>{{ $row['due'] }}</td>
                    <td>@if($row['evidence']) <span class="dot ok"></span> Sí @else <span class="dot bad"></span> No @endif</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">Sin mantenimientos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </article>
    <article class="card">
        <p class="kicker">Novedades abiertas</p>
        <table class="data">
            <thead><tr><th>Caso</th><th>Puesto</th></tr></thead>
            <tbody>
            @forelse($snapshot->openNovelties as $row)
                <tr><td>{{ $row['title'] }}<div class="muted">{{ $row['opened'] }}</div></td><td>{{ $row['post'] ?? '—' }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">Cola limpia.</td></tr>
            @endforelse
            </tbody>
        </table>
    </article>
</section>
@endsection
