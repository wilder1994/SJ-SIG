@props(['kicker', 'title', 'requireClient' => true])

@php
    $blocked = $requireClient && ! $currentContract;
@endphp

<div class="empty-panel">
    <article class="card">
        <p class="kicker">{{ $kicker }}</p>
        <h2 class="display" style="font-size:26px;margin:4px 0 8px">{{ $title }}</h2>
        @if($blocked)
            <p class="muted">Aún no hay un cliente activo. Crea el primero en Clientes; después podrás usar este módulo.</p>
            @if(auth()->user()->role->canManageClients())
                <a class="btn" href="{{ route('clients.create') }}" style="margin-top:14px">Nuevo cliente</a>
            @endif
        @else
            {{ $slot }}
        @endif
    </article>
</div>
