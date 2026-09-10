@extends('layouts.app')

@section('title', 'Tablero · SJ-SIG')

@section('content')
<x-empty-panel kicker="Sin clientes" title="Cree el primer cliente" :require-client="false">
    <p class="muted">Aún no hay universos. Administración da de alta el cliente y luego asigna usuarios.</p>
    @if(auth()->user()->role->canManageClients())
        <a class="btn" href="{{ route('clients.create') }}" style="margin-top:14px">Nuevo cliente</a>
    @endif
</x-empty-panel>
@endsection
