<label class="field">Código
    <input name="code" required maxlength="16" placeholder="P1" value="{{ old('code') }}">
</label>
<label class="field">Instalación
    <input name="name" required placeholder="Planta 1, Bodega 2…" value="{{ old('name') }}">
</label>
@include('partials.location-fields')
<div class="span-2">
    <button class="btn" type="submit">Crear instalación</button>
</div>
