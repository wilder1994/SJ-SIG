@extends('layouts.app')

@section('title', ($user ? 'Editar usuario' : 'Nuevo usuario').' · SJ-SIG')

@section('content')
<article class="card" style="max-width:860px">
    <p class="kicker">Plataforma</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">{{ $user ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
    <p class="muted">Identidad, foto, rol y alcance. Los permisos salen del rol. El perfil del usuario es de solo consulta.</p>

    <form method="post" action="{{ $user ? route('users.update', $user) : route('users.store') }}" enctype="multipart/form-data" class="form-grid" style="margin-top:16px" id="user-form">
        @csrf
        @if($user)
            @method('PUT')
        @endif

        <div class="span-2 avatar-picker">
            <label class="avatar-drop">
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" id="photo-input">
                @if($user?->photo_path)
                    <img id="photo-preview" src="{{ route('users.photo', $user) }}" alt="">
                @else
                    <img id="photo-preview" alt="" hidden>
                    <span id="photo-icon" class="avatar-icon">+</span>
                @endif
            </label>
            <div>
                <p class="kicker">Foto</p>
                <p class="muted">JPG o PNG, máximo 2 MB. Clic en el círculo para cargar.</p>
            </div>
        </div>

        <label class="field span-2">Nombre completo
            <input name="name" value="{{ old('name', $user?->name) }}" required>
        </label>
        <label class="field">Tipo documento
            <select name="document_type" required>
                @foreach(['C','CE','N','TI','PT'] as $type)
                    <option value="{{ $type }}" @selected(old('document_type', $user?->document_type ?? 'C')===$type)>{{ $type }}</option>
                @endforeach
            </select>
        </label>
        <label class="field">Cédula
            <input name="document_number" value="{{ old('document_number', $user?->document_number) }}" required>
        </label>
        <label class="field">Correo corporativo
            <input type="email" name="email" value="{{ old('email', $user?->email) }}" required>
        </label>
        <label class="field">Teléfono
            <input name="phone" value="{{ old('phone', $user?->phone) }}">
        </label>
        <label class="field span-2">Cargo (etiqueta visible)
            <input name="job_title" value="{{ old('job_title', $user?->job_title) }}" placeholder="Jefe de operaciones, Coordinador, Supervisor de patrulla…">
        </label>
        <label class="field">Clave
            <span class="password-wrap">
                <input type="password" name="password" {{ $user ? '' : 'required' }} minlength="8" autocomplete="new-password">
                <button type="button" class="btn ghost password-toggle" data-password-toggle aria-label="Ver clave">Ver</button>
            </span>
        </label>
        <label class="field">Rol
            <select name="role" id="role-select" required>
                @foreach($roles as $role)
                    <option value="{{ $role->value }}"
                        data-requires-client="{{ $role->requiresClient() ? '1' : '0' }}"
                        @selected(old('role', $user?->role->value)===$role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="field" id="client-field">Cliente asignado
            <select name="tenant_id" id="tenant-select">
                <option value="">—</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" @selected((string) old('tenant_id', $user?->tenant_id)===(string) $tenant->id)>{{ $tenant->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="field" style="flex-direction:row;align-items:center;gap:8px">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user?->is_active ?? true))>
            Activo
        </label>

        <div class="span-2">
            <p class="kicker">Permisos de este rol</p>
            <div class="perm-row" id="perm-row"></div>
            <p class="muted" id="role-hint" style="margin-top:8px"></p>
        </div>

        @if($errors->any())
            <div class="span-2">
                @foreach($errors->all() as $error)
                    <p style="color:var(--bad)">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="span-2"><button class="btn" type="submit">Guardar usuario</button></div>
    </form>
</article>

<script type="application/json" id="role-meta">@json(collect($roles)->mapWithKeys(fn ($role) => [$role->value => ['hint' => $role->hint(), 'permissions' => $role->permissionPreview(), 'requires_client' => $role->requiresClient()]]))</script>
@endsection
