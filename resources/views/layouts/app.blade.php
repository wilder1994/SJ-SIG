@extends('layouts.base')

@section('body')
<div class="shell">
    <aside class="rail">
        <div>
            <div class="brand-mark">SJ-<span>SIG</span></div>
            <p class="muted" style="color:var(--steel);margin:8px 0 0;font-size:11px;">Supervisión contractual</p>
        </div>
        <nav class="nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'is-on' : '' }}">Tablero</a>
            @if(auth()->user()->role->canManageClients())
                <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'is-on' : '' }}">Clientes</a>
            @endif
            @if(auth()->user()->role->canManageUsers())
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') && ! request()->routeIs('users.photo') ? 'is-on' : '' }}">Usuarios</a>
            @endif
            @if(auth()->user()->role->canAccessHr())
                <a href="{{ route('sites.index') }}" class="{{ request()->routeIs('sites.*') ? 'is-on' : '' }}">Instalaciones</a>
                <a href="{{ route('people.index') }}" class="{{ request()->routeIs('people.*') ? 'is-on' : '' }}">Personal</a>
                <a href="{{ route('documents.index') }}" class="{{ request()->routeIs('documents.*') ? 'is-on' : '' }}">Documentos</a>
                <a href="{{ route('parafiscals.index') }}" class="{{ request()->routeIs('parafiscals.*') ? 'is-on' : '' }}">Parafiscales</a>
            @endif
            @if(auth()->user()->role->canAccessElectronics())
                <a href="{{ route('electronics.index') }}" class="{{ request()->routeIs('electronics.*') ? 'is-on' : '' }}">Electrónica</a>
            @endif
            @if(auth()->user()->role->canAccessHr())
                <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'is-on' : '' }}">Servicios</a>
                <a href="{{ route('novelties.index') }}" class="{{ request()->routeIs('novelties.*') ? 'is-on' : '' }}">Novedades</a>
            @endif
            @if(auth()->user()->role->canAccessOpsTeam())
                <a href="{{ route('operations.index') }}" class="{{ request()->routeIs('operations.*') ? 'is-on' : '' }}">Equipo SJ</a>
            @endif
            <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'is-on' : '' }}">Mi perfil</a>
        </nav>
        <form method="post" action="{{ route('logout') }}" style="margin-top:auto">
            @csrf
            <button class="btn ghost" style="color:#e8eef6;border-color:rgba(88,196,255,.28);width:100%">Salir</button>
        </form>
    </aside>
    <main class="stage">
        <header class="topbar">
            <div>
                <div class="kicker">{{ $currentContract?->tenant?->name ?? 'SJ-SIG' }}</div>
                <h1 class="display" style="font-size:28px;margin:4px 0 0">{{ $currentContract?->name ?? 'Sin cliente activo' }}</h1>
            </div>
            <div style="text-align:right">
                <div class="muted">{{ auth()->user()->name }} · {{ auth()->user()->role->label() }}</div>
                @if(($accessibleContracts ?? collect())->count() > 1 && auth()->user()->role->seesAllClients())
                    <form method="get" style="margin-top:8px">
                        <select name="contract" onchange="this.form.submit()">
                            @foreach($accessibleContracts as $item)
                                <option value="{{ $item->id }}" @selected($currentContract && $item->id === $currentContract->id)>{{ $item->tenant->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </header>
        @if(session('status'))
            <p class="card" style="margin-bottom:12px">{{ session('status') }}</p>
        @endif
        @yield('content')
    </main>
</div>
<div class="preview-layer" id="preview-layer" hidden>
    <div class="preview-frame">
        <div class="preview-bar">
            <span id="preview-title">Documento</span>
            <span>
                <a class="btn ghost" id="preview-download" href="#" style="color:#e8eef6;border-color:rgba(88,196,255,.35)">Descargar</a>
                <button class="btn ghost" type="button" id="preview-close" style="color:#e8eef6;border-color:rgba(88,196,255,.35)">Cerrar</button>
            </span>
        </div>
        <iframe id="preview-iframe" title="Vista del documento"></iframe>
    </div>
</div>
@endsection
