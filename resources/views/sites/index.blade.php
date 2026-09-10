@extends('layouts.app')

@section('title', 'Instalaciones · SJ-SIG')

@section('content')
@if(! $currentContract)
    <x-empty-panel kicker="Sin instalaciones" title="No hay instalaciones">
        <p class="muted">Aún no hay un cliente activo. Crea el primero en Clientes; después podrás usar este módulo.</p>
        @if(auth()->user()->role->canManageClients())
            <a class="btn" href="{{ route('clients.create') }}" style="margin-top:14px">Nuevo cliente</a>
        @endif
    </x-empty-panel>
@elseif($sites->isEmpty())
<article class="card" style="max-width:820px">
    <p class="kicker">Sin instalaciones</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">No hay instalaciones</h2>
    <p class="muted">En este cliente todavía no hay plantas, bodegas ni puestos. Crea la primera con su dirección.</p>
    @if(auth()->user()->role->canManageStructure())
        <form method="post" action="{{ route('sites.store') }}" class="form-grid" style="margin-top:16px">
            @csrf
            @include('sites._form')
        </form>
    @endif
</article>
@else
<article class="card" style="margin-bottom:12px">
    <p class="kicker">Estructura del cliente</p>
    <h2 class="display" style="font-size:24px;margin:4px 0 8px">Instalaciones y puestos</h2>
    <p class="muted">Plantas, bodegas o sedes. En cada puesto: modalidad (8 / 12 / 24 h) y unidades (vigilantes contratados). No es el listado de personas.</p>
    @if(auth()->user()->role->canManageStructure())
        <form method="post" action="{{ route('sites.store') }}" class="form-grid" style="margin-top:14px">
            @csrf
            @include('sites._form')
        </form>
    @endif
</article>

@foreach($sites as $site)
<article class="card" style="margin-bottom:12px">
    <p class="kicker">{{ $site->code }}{{ $site->city ? ' · '.$site->city : '' }}{{ $site->department ? ' · '.$site->department : '' }}</p>
    <h3 class="display" style="font-size:22px;margin:4px 0 10px">{{ $site->name }}</h3>
    @if($site->address)
        <p class="muted">{{ $site->address }}</p>
    @endif
    <table class="data">
        <thead><tr><th>Puesto</th><th>Modalidad</th><th>Unidades</th></tr></thead>
        <tbody>
        @forelse($site->posts as $post)
            <tr>
                <td>{{ $post->code }} · {{ $post->name }}</td>
                <td>{{ $post->shift_hours->label() }}</td>
                <td>{{ $post->guard_slots }} vigilante{{ $post->guard_slots === 1 ? '' : 's' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">Sin puestos en esta instalación.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if(auth()->user()->role->canManageStructure())
        <form method="post" action="{{ route('sites.posts.store', $site) }}" class="form-grid" style="margin-top:14px">
            @csrf
            <label class="field">Código
                <input name="code" required maxlength="16" placeholder="POR">
            </label>
            <label class="field">Puesto
                <input name="name" required placeholder="Portería, ronda, parqueadero…">
            </label>
            <label class="field">Modalidad
                <select name="shift_hours" required>
                    @foreach($modalities as $modality)
                        <option value="{{ $modality->value }}" @selected($modality === \App\Enums\ServiceModality::Hours12)>{{ $modality->label() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">Unidades (vigilantes)
                <input type="number" name="guard_slots" min="1" max="99" value="1" required>
            </label>
            <div class="span-2"><button class="btn" type="submit">Agregar puesto</button></div>
        </form>
    @endif
</article>
@endforeach
@endif
@endsection
