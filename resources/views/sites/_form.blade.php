@php
    $site = $site ?? null;
    $posts = old('posts', $site?->posts?->map(fn ($post) => [
        'id' => $post->id,
        'name' => $post->name,
        'shift_hours' => $post->shift_hours->value,
        'staffings' => $post->staffings->map(fn ($row) => [
            'role' => $row->role->value,
            'slots' => $row->slots,
        ])->all(),
    ])->all() ?? []);
    if ($posts === []) {
        $posts = [['name' => '', 'shift_hours' => 12, 'staffings' => [['role' => 'vigilante', 'slots' => 1]]]];
    }
@endphp

@if($errors->any())
    <p class="span-2" style="color:var(--bad)">{{ $errors->first() }}</p>
@endif

<label class="field span-2">Instalación
    <input name="name" required placeholder="Planta 1, Bodega 2…" value="{{ old('name', $site?->name) }}">
</label>
<p class="muted span-2">El código se asigna solo: iniciales del cliente + número (por ejemplo SOS01).</p>
@include('partials.location-fields', [
    'address' => $site?->address,
    'city' => $site?->city,
    'department' => $site?->department,
    'lat' => $site?->lat,
    'lng' => $site?->lng,
    'place_id' => $site?->place_id,
    'pinKind' => 'site',
])

<div class="span-2" data-posts>
    <p class="kicker">Puestos y unidades</p>
    <p class="muted">Cada puesto tiene modalidad y unidades por cargo. No es quién está asignado; es el cupo contratado.</p>
    <div data-post-list>
        @foreach($posts as $index => $post)
            @include('sites._post', ['index' => $index, 'post' => $post, 'modalities' => $modalities, 'roles' => $roles])
        @endforeach
    </div>
    <button class="btn ghost" type="button" data-add-post style="margin-top:10px">Agregar puesto</button>
    <template data-post-template>
        @include('sites._post', ['index' => '__i__', 'post' => ['name' => '', 'shift_hours' => 12, 'staffings' => [['role' => 'vigilante', 'slots' => 1]]], 'modalities' => $modalities, 'roles' => $roles])
    </template>
</div>
