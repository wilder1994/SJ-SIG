@extends('layouts.app')

@section('title', 'Servicios · SJ-SIG')

@section('content')
@if($rows->isEmpty())
    <x-empty-panel kicker="Sin servicios" title="No hay servicios registrados">
        <p class="muted">En este contrato todavía no hay conteos de servicio por puesto (semana, mes o vigencia).</p>
    </x-empty-panel>
@else
<article class="card">
    <p class="kicker">Ítem 6</p>
    <h2 class="display" style="font-size:24px;margin:4px 0 12px">Cantidad prestada por puesto</h2>
    @foreach($rows as $key => $group)
        @php [$kind, $start] = explode('|', $key); @endphp
        <p style="margin:14px 0 6px;font-weight:500">{{ $kind }} · {{ $start }}</p>
        <table class="data">
            <thead><tr><th>Puesto</th><th>Modalidad</th><th>Unidades</th><th>Prestados</th></tr></thead>
            <tbody>
            @foreach($group as $row)
                <tr>
                    <td>{{ $row->post->site?->name }} · {{ $row->post->name }}</td>
                    <td>{{ $row->post->shift_hours?->label() ?? '—' }}</td>
                    <td>{{ $row->post->guard_slots }}</td>
                    <td>{{ $row->quantity }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach
</article>
@endif
@endsection
