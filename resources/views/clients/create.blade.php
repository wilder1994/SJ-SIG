@extends('layouts.app')

@section('title', 'Nuevo cliente · SJ-SIG')

@section('content')
<article class="card" style="max-width:640px">
    <p class="kicker">Universo</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">Nuevo cliente</h2>
    <p class="muted">Crea el cliente y su servicio. Instalaciones y puestos se arman después, dentro de este universo.</p>
    <form method="post" action="{{ route('clients.store') }}" class="form-grid" style="margin-top:16px">
        @csrf
        <label class="field span-2">Razón social / nombre
            <input name="name" value="{{ old('name') }}" required>
        </label>
        <label class="field">NIT
            <input name="nit" value="{{ old('nit') }}">
        </label>
        <label class="field">Inicio de servicio
            <input type="date" name="starts_on" value="{{ old('starts_on', now()->toDateString()) }}">
        </label>
        <div class="span-2"><button class="btn" type="submit">Crear cliente</button></div>
    </form>
</article>
@endsection
