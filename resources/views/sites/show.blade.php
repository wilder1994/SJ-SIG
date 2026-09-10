@extends('layouts.app')

@section('title', $site->name.' · SJ-SIG')

@section('content')
<article class="card" style="margin-bottom:12px">
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap">
        <div>
            <p class="kicker">{{ $site->code }}{{ $site->city ? ' · '.$site->city : '' }}{{ $site->department ? ' · '.$site->department : '' }}</p>
            <h2 class="display" style="font-size:26px;margin:4px 0 6px">{{ $site->name }}</h2>
            @if($site->address)
                <p class="muted">{{ $site->address }}</p>
            @endif
            <p class="muted">Dotación vigente: {{ $site->unitsLabel() }}</p>
        </div>
        <div style="display:flex;gap:8px">
            <a class="btn ghost" href="{{ route('sites.index') }}">Volver</a>
            @if($canManage)
                <a class="btn" href="{{ route('sites.edit', $site) }}">Editar</a>
            @endif
        </div>
    </div>
    @if($site->hasCoordinates())
        @php
            $mapPoints = [[
                'kind' => 'site',
                'label' => $site->name,
                'address' => $site->address,
                'lat' => $site->lat,
                'lng' => $site->lng,
            ]];
        @endphp
        <div
            class="overview-map"
            style="min-height:240px;margin-top:14px"
            data-overview-map
            data-points='@json($mapPoints)'
        ></div>
    @endif
</article>

<article class="card" style="margin-bottom:12px">
    <p class="kicker">Puestos</p>
    <table class="data">
        <thead><tr><th>Puesto</th><th>Modalidad</th><th>Unidades</th></tr></thead>
        <tbody>
        @forelse($site->posts as $post)
            <tr>
                <td>{{ $post->code }} · {{ $post->name }}</td>
                <td>{{ $post->shift_hours->label() }}</td>
                <td>{{ $post->unitsLabel() }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">Sin puestos en esta instalación.</td></tr>
        @endforelse
        </tbody>
    </table>
</article>

<article class="card">
    <p class="kicker">Bitácora del servicio</p>
    <p class="muted">Inicio y cambios en instalación, puesto o personal. No se borra al editar la ficha.</p>
    @forelse($site->serviceEvents as $event)
        <div class="service-event">
            <p class="kicker">{{ $event->scopesLabel() ?? $event->kind->label() }} · {{ $event->effective_on?->format('d/m/Y') }}</p>
            @if($event->from_summary || $event->to_summary)
                <p>{{ $event->from_summary && $event->to_summary && $event->from_summary !== $event->to_summary ? $event->from_summary.' → '.$event->to_summary : ($event->to_summary ?: $event->from_summary) }}</p>
            @endif
            @if($event->requested_by)
                <p class="muted">Solicitado por {{ $event->requested_by }}</p>
            @endif
            @if($event->last_shift_on)
                <p class="muted">Último turno de la dotación anterior: {{ $event->last_shift_on->format('d/m/Y') }}</p>
            @endif
            @if($event->reason)
                <p>{{ $event->reason }}</p>
            @endif
            <p class="muted">Registró {{ $event->author?->name ?? 'SJ-SIG' }} · {{ $event->created_at?->format('d/m/Y H:i') }}</p>
        </div>
    @empty
        <p class="muted">Todavía no hay asientos de inicio ni de cambio.</p>
    @endforelse
</article>
@endsection
