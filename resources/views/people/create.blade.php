@extends('layouts.app')

@section('title', 'Nuevo empleado · SJ-SIG')

@section('content')
<article class="card" style="max-width:760px">
    <p class="kicker">Alta unitaria</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">Nuevo empleado</h2>
    <p class="muted">Para un ingreso puntual. HV, certificados y afiliaciones PDF se cargan después en Documentos → Ver carpeta → Cargar documentos.</p>
    <form method="post" action="{{ route('people.store') }}" class="form-grid" style="margin-top:16px">
        @csrf
        <label class="field">Tipo documento
            <select name="document_type" required>
                @foreach(['C','CE','N','TI','PT'] as $type)
                    <option value="{{ $type }}" @selected(old('document_type','C')===$type)>{{ $type }}</option>
                @endforeach
            </select>
        </label>
        <label class="field">Cédula
            <input name="document_number" value="{{ old('document_number') }}" required>
        </label>
        <label class="field span-2">Nombre completo
            <input name="full_name" value="{{ old('full_name') }}" required>
        </label>
        <label class="field">Correo
            <input type="email" name="email" value="{{ old('email') }}">
        </label>
        <label class="field">Teléfono
            <input name="phone" value="{{ old('phone') }}">
        </label>
        <label class="field">Cargo
            <input name="job_code" value="{{ old('job_code') }}">
        </label>
        <label class="field">Fecha ingreso
            <input type="date" name="hired_on" value="{{ old('hired_on') }}">
        </label>
        <label class="field">EPS
            <input name="eps_name" value="{{ old('eps_name') }}">
        </label>
        <label class="field">Pensión
            <input name="afp_name" value="{{ old('afp_name') }}">
        </label>
        <label class="field">Caja
            <input name="compensation_fund" value="{{ old('compensation_fund') }}">
        </label>
        <label class="field">ARL
            <input name="arl_name" value="{{ old('arl_name') }}">
        </label>
        <div class="span-2" style="display:flex;gap:8px">
            <button class="btn" type="submit">Crear y abrir ficha</button>
            <a class="btn ghost" href="{{ route('people.index') }}">Cancelar</a>
        </div>
    </form>
</article>
@endsection
