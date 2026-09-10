@php
    $address = old('address', $address ?? '');
    $city = old('city', $city ?? '');
    $department = old('department', $department ?? '');
    $lat = old('lat', $lat ?? '');
    $lng = old('lng', $lng ?? '');
    $placeId = old('place_id', $placeId ?? '');
@endphp

<div class="span-2 location-block" data-location-map>
    <label class="field">Dirección
        <span class="location-address">
            <input type="text" name="address" data-location-address value="{{ $address }}" placeholder="Calle, barrio…" autocomplete="off">
            <button class="btn ghost location-pin-btn" type="button" data-location-pin title="Fijar en el mapa" aria-label="Fijar en el mapa">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/>
                </svg>
            </button>
        </span>
    </label>
    <p class="muted" style="margin:4px 0 8px">Puedes editar el texto sin cambiar las coordenadas. Usa el mapa para fijar la ubicación.</p>
    <div class="location-picker" data-location-canvas></div>
    <input type="hidden" name="lat" data-location-lat value="{{ $lat }}">
    <input type="hidden" name="lng" data-location-lng value="{{ $lng }}">
    <input type="hidden" name="place_id" data-location-place value="{{ $placeId }}">
    <div class="form-grid" style="margin-top:10px">
        <label class="field">Ciudad
            <input type="text" name="city" data-location-city value="{{ $city }}" placeholder="Municipio / ciudad">
        </label>
        <label class="field">Departamento
            <input type="text" name="department" data-location-department value="{{ $department }}" placeholder="Departamento">
        </label>
    </div>
</div>
