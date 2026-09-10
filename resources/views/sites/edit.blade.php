@extends('layouts.app')

@section('title', 'Editar '.$site->name.' · SJ-SIG')

@section('content')
<article class="card" style="max-width:880px">
    <p class="kicker">{{ $site->code }}</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">Editar {{ $site->name }}</h2>
    <p class="muted">Si cambia instalación, puesto o personal, deje el motivo. No se pisa el histórico.</p>
    <form method="post" action="{{ route('sites.update', $site) }}" class="form-grid" style="margin-top:16px" data-site-form>
        @csrf
        @method('PUT')
        @include('sites._form')
        <div class="span-2 card" style="padding:12px;margin-top:4px">
            <p class="kicker">Motivo del cambio</p>
            <p class="muted">El sistema detecta si el cambio es en la instalación (nombre, dirección), en el puesto (nombre, modalidad) o en el personal (cargo y unidades). El motivo es obligatorio en esos casos.</p>
            <label class="field" style="margin-top:10px">Motivo del cambio
                <textarea name="change_reason" rows="4" placeholder="Solicitud del señor Pepito Pérez. Los guardas hicieron el último turno hasta…">{{ old('change_reason') }}</textarea>
            </label>
        </div>
        <div class="span-2" style="display:flex;gap:8px">
            <button class="btn" type="submit">Guardar cambios</button>
            <a class="btn ghost" href="{{ route('sites.show', $site) }}">Cancelar</a>
        </div>
    </form>
</article>
@endsection
