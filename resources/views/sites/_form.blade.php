@if($errors->any())
    <p class="span-2" style="color:var(--bad)">{{ $errors->first() }}</p>
@endif
<label class="field span-2">Instalación
    <input name="name" required placeholder="Planta 1, Bodega 2…" value="{{ old('name') }}">
</label>
<p class="muted span-2">El código se asigna solo: iniciales del cliente + número (por ejemplo SOS01).</p>
@include('partials.location-fields')
<div class="span-2">
    <button class="btn" type="submit">Crear instalación</button>
</div>
