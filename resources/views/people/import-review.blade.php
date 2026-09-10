@extends('layouts.app')

@section('title', 'Revisar plantilla · SJ-SIG')

@section('content')
@php
    $valid = $analysis['valid'];
    $statusLabel = [
        'create' => 'Alta',
        'update' => 'Actualiza',
        'same' => 'Sin cambios',
        'error' => 'Error',
    ];
    $statusClass = [
        'create' => 'badge-ok',
        'update' => 'badge-warn',
        'same' => '',
        'error' => 'badge-bad',
    ];
@endphp
<article class="card">
    <p class="kicker">Carga masiva</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">Revisar plantilla</h2>
    <p class="muted">{{ $filename }} · Fila 1 encabezado, fila 2 ayuda. Las filas con error no se importan.</p>
    @if($errors->has('workbook'))
        <p style="color:var(--bad);margin-top:10px">{{ $errors->first('workbook') }}</p>
    @endif
    <p style="margin:14px 0 0">
        <span class="badge badge-ok">{{ $analysis['created'] }} altas</span>
        <span class="badge badge-warn">{{ $analysis['updated'] }} actualizaciones</span>
        <span class="badge">{{ $analysis['unchanged'] }} sin cambios</span>
        <span class="badge badge-bad">{{ $analysis['errors'] }} errores</span>
    </p>
</article>

<article class="card" style="margin-top:12px">
    <table class="data">
        <thead>
            <tr>
                <th>Fila</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
        @forelse($analysis['rows'] as $row)
            <tr>
                <td>{{ $row['row'] }}</td>
                <td>{{ $row['document_number'] !== '' ? $row['document_number'] : '—' }}</td>
                <td>{{ $row['full_name'] !== '' ? $row['full_name'] : '—' }}</td>
                <td><span class="badge {{ $statusClass[$row['status']] }}">{{ $statusLabel[$row['status']] }}</span></td>
                <td>
                    @if($row['message'])
                        <p style="margin:0">{{ $row['message'] }}</p>
                    @endif
                    @foreach($row['warnings'] as $warning)
                        <p class="muted" style="margin:4px 0 0">{{ $warning }}</p>
                    @endforeach
                    @foreach($row['changes'] as $change)
                        <p class="import-diff" style="margin:4px 0 0">
                            <strong>{{ $change['label'] }}:</strong>
                            {{ $change['from'] }} → {{ $change['to'] }}
                        </p>
                    @endforeach
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">El archivo no tiene filas de trabajadores para revisar.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="drop-actions" style="margin-top:16px">
        <a class="btn ghost" href="{{ route('people.index') }}">Volver</a>
        <form method="post" action="{{ route('people.import') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <button class="btn" type="submit" @disabled($valid === 0)>
                Importar {{ $valid }} {{ $valid === 1 ? 'fila válida' : 'filas válidas' }}
            </button>
        </form>
    </div>
</article>
@endsection
