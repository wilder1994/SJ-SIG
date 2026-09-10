@extends('layouts.app')

@section('title', 'Nueva instalación · SJ-SIG')

@section('content')
<article class="card" style="max-width:880px">
    <p class="kicker">Nueva instalación</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">Crear instalación</h2>
    <p class="muted">Sede, puestos, cargos y cómo inicia el servicio. Los cambios posteriores quedan en la bitácora.</p>
    <form method="post" action="{{ route('sites.store') }}" class="form-grid" style="margin-top:16px" data-site-form>
        @csrf
        @include('sites._form')
        <label class="field span-2">Cómo inicia el servicio
            <textarea name="service_start" rows="4" placeholder="Iniciamos con tres vigilantes en portería…">{{ old('service_start') }}</textarea>
        </label>
        <label class="field">Vigente desde
            <input type="date" name="effective_on" value="{{ old('effective_on', now()->toDateString()) }}">
        </label>
        <label class="field">Solicitado / autorizado por
            <input name="requested_by" value="{{ old('requested_by') }}" placeholder="Nombre de quien autoriza el inicio">
        </label>
        <div class="span-2" style="display:flex;gap:8px">
            <button class="btn" type="submit">Crear instalación</button>
            <a class="btn ghost" href="{{ route('sites.index') }}">Volver</a>
        </div>
    </form>
</article>
@endsection
