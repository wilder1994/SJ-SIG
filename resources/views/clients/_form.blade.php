@php
    use App\Enums\IdDocumentType;
    use App\Enums\PersonKind;
    use App\Enums\StructureType;
    $client = $client ?? null;
@endphp

<label class="field span-2">Persona
    <select name="person_kind" required>
        @foreach(PersonKind::cases() as $kind)
            <option value="{{ $kind->value }}" @selected(old('person_kind', $client?->person_kind ?? PersonKind::Juridica->value) === $kind->value)>{{ $kind->label() }}</option>
        @endforeach
    </select>
</label>
<label class="field span-2">Tipo de estructura
    <select name="structure_type">
        <option value="">Seleccione…</option>
        @foreach(StructureType::cases() as $type)
            <option value="{{ $type->value }}" @selected((string) old('structure_type', $client?->structure_type) === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </select>
</label>
<p class="muted span-2">Queda fijo para este cliente; las instalaciones heredan este universo. Si el listado no cubre el caso, elige Otro.</p>
<label class="field span-2">Nombre comercial
    <input name="trade_name" value="{{ old('trade_name', $client?->trade_name) }}">
</label>
<label class="field span-2">Razón social / nombre legal
    <input name="legal_name" value="{{ old('legal_name', $client?->legal_name ?? $client?->name) }}" required>
</label>
<label class="field">Tipo de documento
    <select name="document_type" required>
        @foreach(IdDocumentType::cases() as $type)
            <option value="{{ $type->value }}" @selected(old('document_type', $client?->document_type ?? 'NIT') === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </select>
</label>
<label class="field">Número de documento
    <input name="document_number" value="{{ old('document_number', $client?->nit) }}" required>
</label>
<label class="field">Correo de contacto
    <input type="email" name="contact_email" value="{{ old('contact_email', $client?->contact_email) }}">
</label>
<label class="field">Teléfono
    <input name="phone" value="{{ old('phone', $client?->phone) }}">
</label>
<p class="kicker span-2" style="margin-top:6px">Representante legal</p>
<label class="field">Nombre
    <input name="legal_rep_name" value="{{ old('legal_rep_name', $client?->legal_rep_name) }}">
</label>
<label class="field">Correo
    <input type="email" name="legal_rep_email" value="{{ old('legal_rep_email', $client?->legal_rep_email) }}">
</label>
@include('partials.location-fields', [
    'address' => $client?->address,
    'city' => $client?->city,
    'department' => $client?->department,
    'lat' => $client?->lat,
    'lng' => $client?->lng,
    'placeId' => $client?->place_id,
])
