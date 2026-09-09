@extends('layouts.base')

@section('title', 'Ingreso · SJ-SIG')

@section('body')
<div class="login-wrap">
    <section class="login-hero">
        <div>
            <div class="brand-mark" style="font-size:34px">SJ-<span>SIG</span></div>
            <p style="max-width:28rem;margin-top:18px;color:#b8c2d4;line-height:1.55">
                Tablero de supervisión, trazabilidad y control del servicio de vigilancia. Un contrato, un universo.
            </p>
        </div>
        <div>
            <p class="kicker" style="color:var(--cyan)">Anexo 7.4</p>
            <p style="margin:8px 0 0;color:#e8eef6" class="display">Gestión sin ruido. Evidencia con fecha.</p>
        </div>
    </section>
    <section class="login-panel">
        <form method="post" action="{{ route('login.store') }}" style="width:min(380px,100%)" class="card">
            @csrf
            <p class="kicker">Acceso</p>
            <h2 class="display" style="font-size:28px;margin:6px 0 16px">Entrar al contrato</h2>
            <div class="field" style="margin-bottom:10px">
                <label for="email">Correo</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="field" style="margin-bottom:14px">
                <label for="password">Clave</label>
                <input id="password" name="password" type="password" required>
            </div>
            @error('email')
                <p style="color:var(--bad);margin-bottom:10px">{{ $message }}</p>
            @enderror
            <button class="btn" type="submit" style="width:100%">Ingresar</button>
            <p class="muted" style="margin-top:14px;font-size:12px">Demo: supervisor.a@sj-sig.test · Sig2026!</p>
        </form>
    </section>
</div>
@endsection
