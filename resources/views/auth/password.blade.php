@extends('layouts.base')

@section('title', 'Cambiar clave · SJ-SIG')

@section('body')
<div class="login-panel" style="min-height:100vh">
    <form method="post" action="{{ route('password.update') }}" class="card" style="width:min(400px,100%)">
        @csrf
        <p class="kicker">Primer ingreso</p>
        <h2 class="display" style="font-size:26px;margin:6px 0 12px">Defina su clave</h2>
        <p class="muted" style="margin-bottom:14px">Por seguridad debe cambiar la clave temporal antes de entrar.</p>
        <label class="field" style="margin-bottom:10px">Nueva clave
            <span class="password-wrap">
                <input type="password" name="password" required minlength="8">
                <button type="button" class="btn ghost password-toggle" data-password-toggle>Ver</button>
            </span>
        </label>
        <label class="field" style="margin-bottom:14px">Confirmar
            <span class="password-wrap">
                <input type="password" name="password_confirmation" required minlength="8">
                <button type="button" class="btn ghost password-toggle" data-password-toggle>Ver</button>
            </span>
        </label>
        @error('password')
            <p style="color:var(--bad);margin-bottom:10px">{{ $message }}</p>
        @enderror
        <button class="btn" type="submit" style="width:100%">Guardar e ingresar</button>
    </form>
</div>
@endsection
