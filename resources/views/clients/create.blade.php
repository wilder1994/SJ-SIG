@extends('layouts.app')

@section('title', 'Nuevo cliente · SJ-SIG')

@section('content')
<article class="card" style="max-width:820px">
    <p class="kicker">Universo</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">Nuevo cliente</h2>
    <p class="muted">Crea el cliente, su sede principal y el inicio del servicio. Instalaciones y puestos se arman después.</p>
    <form method="post" action="{{ route('clients.store') }}" class="form-grid" style="margin-top:16px">
        @csrf
        @include('clients._form')
        <label class="field">Inicio de servicio
            <input type="date" name="starts_on" value="{{ old('starts_on', now()->toDateString()) }}">
        </label>
        <p class="muted span-2">Fecha de apertura del servicio. Controla la operación; no gestiona cobros del cliente.</p>
        @if($errors->any())
            <p class="span-2" style="color:var(--bad)">{{ $errors->first() }}</p>
        @endif
        <div class="span-2"><button class="btn" type="submit">Crear cliente</button></div>
    </form>
</article>
@endsection
