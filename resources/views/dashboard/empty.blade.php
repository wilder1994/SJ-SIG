@extends('layouts.app')

@section('title', 'Tablero · SJ-SIG')

@section('content')
<article class="card">
    <p class="kicker">Sin clientes</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">Cree el primer cliente</h2>
    <p class="muted">Aún no hay universos. Administración da de alta el cliente y luego asigna usuarios.</p>
    @if(auth()->user()->role->canManageClients())
        <a class="btn" href="{{ route('clients.create') }}" style="margin-top:14px">Nuevo cliente</a>
    @endif
</article>
@endsection
