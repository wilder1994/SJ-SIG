const COLORS = { client: '#0b3d91', site: '#58c4ff' };
const COLOMBIA = { lat: 4.570868, lng: -74.297333 };

function mapsKey() {
    return typeof window.SJ_MAPS_KEY === 'string' ? window.SJ_MAPS_KEY.trim() : '';
}

function loadGoogle() {
    if (window.google?.maps) {
        return Promise.resolve();
    }
    if (window.__sjMapsLoading) {
        return window.__sjMapsLoading;
    }
    const key = mapsKey();
    if (!key) {
        return Promise.reject(new Error('missing-key'));
    }
    window.__sjMapsLoading = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(key)}&libraries=places&language=es&region=CO`;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('maps-load'));
        document.head.appendChild(script);
    });

    return window.__sjMapsLoading;
}

function pinIcon(color) {
    return {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 10,
        fillColor: color,
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight: 2,
    };
}

function readNumber(input) {
    const value = Number(input?.value);
    return Number.isFinite(value) && input?.value !== '' ? value : null;
}

function componentOf(place, type, name = 'long_name') {
    return place.address_components?.find((item) => item.types.includes(type))?.[name] || '';
}

function fillFromPlace(root, place) {
    const address = root.querySelector('[data-location-address]');
    const city = root.querySelector('[data-location-city]');
    const department = root.querySelector('[data-location-department]');
    const lat = root.querySelector('[data-location-lat]');
    const lng = root.querySelector('[data-location-lng]');
    const placeId = root.querySelector('[data-location-place]');
    if (address && place.formatted_address) {
        address.value = place.formatted_address;
    }
    if (city) {
        city.value = componentOf(place, 'locality') || componentOf(place, 'administrative_area_level_2');
    }
    if (department) {
        department.value = componentOf(place, 'administrative_area_level_1');
    }
    const location = place.geometry?.location;
    if (location && lat && lng) {
        lat.value = location.lat().toFixed(7);
        lng.value = location.lng().toFixed(7);
    }
    if (placeId && place.place_id) {
        placeId.value = place.place_id;
    }
}

function initPicker(root) {
    const canvas = root.querySelector('[data-location-canvas]');
    const address = root.querySelector('[data-location-address]');
    const latInput = root.querySelector('[data-location-lat]');
    const lngInput = root.querySelector('[data-location-lng]');
    const pinBtn = root.querySelector('[data-location-pin]');
    if (!canvas || !address) {
        return;
    }

    const startLat = readNumber(latInput) ?? COLOMBIA.lat;
    const startLng = readNumber(lngInput) ?? COLOMBIA.lng;
    const map = new google.maps.Map(canvas, {
        center: { lat: startLat, lng: startLng },
        zoom: readNumber(latInput) ? 16 : 6,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
    });
    const marker = new google.maps.Marker({
        map,
        position: { lat: startLat, lng: startLng },
        draggable: true,
        icon: pinIcon(COLORS.client),
    });

    const syncMarker = (lat, lng, zoom) => {
        const position = { lat, lng };
        marker.setPosition(position);
        map.panTo(position);
        if (zoom) {
            map.setZoom(zoom);
        }
        if (latInput) {
            latInput.value = lat.toFixed(7);
        }
        if (lngInput) {
            lngInput.value = lng.toFixed(7);
        }
    };

    marker.addListener('dragend', () => {
        const position = marker.getPosition();
        if (position && latInput && lngInput) {
            latInput.value = position.lat().toFixed(7);
            lngInput.value = position.lng().toFixed(7);
        }
    });

    const autocomplete = new google.maps.places.Autocomplete(address, {
        fields: ['place_id', 'formatted_address', 'geometry', 'address_components'],
        componentRestrictions: { country: 'co' },
    });
    autocomplete.bindTo('bounds', map);
    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place?.geometry?.location) {
            return;
        }
        fillFromPlace(root, place);
        syncMarker(place.geometry.location.lat(), place.geometry.location.lng(), 16);
    });

    pinBtn?.addEventListener('click', () => {
        const lat = readNumber(latInput);
        const lng = readNumber(lngInput);
        if (lat !== null && lng !== null) {
            syncMarker(lat, lng, 16);
            return;
        }
        if (!address.value.trim()) {
            return;
        }
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: address.value, region: 'CO' }, (results, status) => {
            if (status !== 'OK' || !results?.[0]) {
                return;
            }
            fillFromPlace(root, results[0]);
            const location = results[0].geometry.location;
            syncMarker(location.lat(), location.lng(), 16);
        });
    });
}

function initOverview(node) {
    let points = [];
    try {
        points = JSON.parse(node.getAttribute('data-points') || '[]');
    } catch {
        points = [];
    }
    const map = new google.maps.Map(node, {
        center: COLOMBIA,
        zoom: 6,
        mapTypeId: 'hybrid',
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
    });
    const wrap = node.closest('[data-overview-wrap]');
    wrap?.querySelectorAll('[data-map-type-btn]').forEach((button) => {
        button.addEventListener('click', () => {
            const type = button.getAttribute('data-map-type-btn');
            if (!type) {
                return;
            }
            map.setMapTypeId(type);
            wrap.querySelectorAll('[data-map-type-btn]').forEach((item) => {
                item.classList.toggle('is-on', item === button);
            });
        });
    });
    const bounds = new google.maps.LatLngBounds();
    points.forEach((point) => {
        const position = { lat: Number(point.lat), lng: Number(point.lng) };
        if (!Number.isFinite(position.lat) || !Number.isFinite(position.lng)) {
            return;
        }
        const marker = new google.maps.Marker({
            map,
            position,
            title: point.label,
            icon: pinIcon(point.kind === 'client' ? COLORS.client : COLORS.site),
        });
        const info = new google.maps.InfoWindow({
            content: `<strong>${point.label}</strong>${point.address ? `<div>${point.address}</div>` : ''}`,
        });
        marker.addListener('click', () => info.open({ map, anchor: marker }));
        bounds.extend(position);
    });
    if (points.length === 1) {
        map.setCenter(bounds.getCenter());
        map.setZoom(15);
    } else if (points.length > 1) {
        map.fitBounds(bounds, 48);
    }
}

function markMissingKey() {
    document.querySelectorAll('[data-location-canvas], [data-overview-map]').forEach((node) => {
        node.classList.add('is-missing-key');
        if (!node.querySelector('[data-maps-missing]')) {
            const note = document.createElement('p');
            note.className = 'muted';
            note.setAttribute('data-maps-missing', '1');
            note.textContent = 'Falta GOOGLE_MAPS_API_KEY en .env para mostrar el mapa.';
            node.appendChild(note);
        }
    });
}

export function bootMaps() {
    const pickers = [...document.querySelectorAll('[data-location-map]')];
    const overviews = [...document.querySelectorAll('[data-overview-map]')];
    if (!pickers.length && !overviews.length) {
        return;
    }
    loadGoogle()
        .then(() => {
            pickers.forEach(initPicker);
            overviews.forEach(initOverview);
        })
        .catch(markMissingKey);
}
