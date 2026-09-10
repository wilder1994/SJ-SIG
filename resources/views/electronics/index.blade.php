@extends('layouts.app')

@section('title', 'Electrónica · SJ-SIG')

@section('content')
@if($assets->isEmpty())
    <x-empty-panel kicker="Sin electrónica" title="No hay activos electrónicos">
        <p class="muted">En este contrato todavía no hay cámaras, DVR ni otros equipos registrados.</p>
    </x-empty-panel>
@else
<div class="split">
    <article class="card">
        <p class="kicker">Infraestructura</p>
        @foreach($assets as $asset)
            <div style="padding:10px 0;border-bottom:1px solid var(--line)">
                <strong>{{ $asset->name }}</strong>
                <span class="muted"> · {{ $asset->kind->label() }} · {{ $asset->post?->name ?? 'Sin puesto' }}</span>
                @foreach($asset->maintenances->take(3) as $row)
                    <p class="muted">{{ $row->performed_on->format('d/m/Y') }} — {{ $row->summary }} @unless($row->hasEvidence()) · sin evidencia @endunless</p>
                @endforeach
            </div>
        @endforeach
    </article>
    @if(auth()->user()->role->canRegisterMaintenance())
    <article class="card">
        <p class="kicker">Registrar mantenimiento</p>
        <form method="post" action="{{ route('maintenances.store') }}" class="field" style="gap:10px">
            @csrf
            <select name="electronic_asset_id" required>
                @foreach($assets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                @endforeach
            </select>
            <input type="date" name="performed_on" required>
            <input type="date" name="next_due_on">
            <input type="text" name="summary" placeholder="Resumen" required>
            <input type="text" name="evidence_path" placeholder="Ruta de evidencia (opcional)">
            <button class="btn" type="submit">Guardar</button>
        </form>
    </article>
    @endif
</div>
@endif
@endsection
