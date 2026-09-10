@php
    $person = $person ?? null;
    $val = fn (string $field, mixed $fallback = null) => old($field, $fallback);
    $date = fn (string $field, $carbon) => old($field, $carbon?->format('Y-m-d'));
@endphp

@if($errors->any())
    <p class="span-2" style="color:var(--bad)">{{ $errors->first() }}</p>
@endif

<div class="span-2 avatar-picker">
    @include('people._avatar', ['person' => $person, 'canEdit' => true])
    <div>
        <p class="kicker">Foto</p>
        <p class="muted">JPG, PNG o WebP, máximo 2 MB. Clic en el círculo o en la cámara.</p>
    </div>
</div>

<p class="kicker span-2">Identidad</p>
<label class="field">Tipo documento
    <select name="document_type" required>
        @foreach(['C','CE','N','TI','PT'] as $type)
            <option value="{{ $type }}" @selected($val('document_type', $person?->document_type ?? 'C') === $type)>{{ $type }}</option>
        @endforeach
    </select>
</label>
<label class="field">Cédula
    <input name="document_number" value="{{ $val('document_number', $person?->document_number) }}" required>
</label>
<label class="field span-2">Nombre completo
    <input name="full_name" value="{{ $val('full_name', $person?->full_name) }}" required>
</label>
<label class="field">Fecha de nacimiento
    <input type="date" name="birth_date" value="{{ $date('birth_date', $person?->birth_date) }}">
</label>
<label class="field">Lugar de expedición
    <input name="document_issue_place" value="{{ $val('document_issue_place', $person?->document_issue_place) }}">
</label>
<label class="field">Fecha de expedición
    <input type="date" name="document_issued_on" value="{{ $date('document_issued_on', $person?->document_issued_on) }}">
</label>
<label class="field">Tipo de sangre
    <input name="blood_type" value="{{ $val('blood_type', $person?->blood_type) }}">
</label>
<label class="field">Sexo
    <input name="sex" value="{{ $val('sex', $person?->sex) }}">
</label>
<label class="field">Escolaridad
    <input name="education" value="{{ $val('education', $person?->education) }}">
</label>
<label class="field">Estado civil
    <input name="marital_status" value="{{ $val('marital_status', $person?->marital_status) }}">
</label>
<label class="field">Número de hijos
    <input type="number" name="children_count" min="0" max="30" value="{{ $val('children_count', $person?->children_count) }}">
</label>

<p class="kicker span-2">Contacto y residencia</p>
<label class="field">Teléfono
    <input name="phone" value="{{ $val('phone', $person?->phone) }}">
</label>
<label class="field">Correo
    <input type="email" name="email" value="{{ $val('email', $person?->email) }}">
</label>
<label class="field">Lugar de residencia
    <input name="residence_city" value="{{ $val('residence_city', $person?->residence_city) }}">
</label>
<label class="field span-2">Dirección
    <input name="address" value="{{ $val('address', $person?->address) }}">
</label>

<p class="kicker span-2">Vinculación laboral</p>
<label class="field">Cargo
    <input name="job_code" value="{{ $val('job_code', $person?->job_code) }}">
</label>
<label class="field">Tipo de vinculación
    <input name="engagement_type" value="{{ $val('engagement_type', $person?->engagement_type) }}">
</label>
<label class="field">Tipo de cotizante
    <input name="contributor_type" value="{{ $val('contributor_type', $person?->contributor_type) }}">
</label>
<label class="field">Tipo de contrato
    <input name="labor_contract_type" value="{{ $val('labor_contract_type', $person?->labor_contract_type) }}">
</label>
<label class="field">Fecha de ingreso
    <input type="date" name="hired_on" value="{{ $date('hired_on', $person?->hired_on) }}">
</label>
<label class="field">Vencimiento de contrato
    <input type="date" name="labor_contract_ends_on" value="{{ $date('labor_contract_ends_on', $person?->labor_contract_ends_on) }}">
</label>
<label class="field">Fecha de retiro
    <input type="date" name="left_on" value="{{ $date('left_on', $person?->left_on) }}">
</label>

<p class="kicker span-2">Seguridad social</p>
<label class="field">Código EPS
    <input name="eps_code" value="{{ $val('eps_code', $person?->eps_code) }}">
</label>
<label class="field">EPS
    <input name="eps_name" value="{{ $val('eps_name', $person?->eps_name) }}">
</label>
<label class="field">Código AFP
    <input name="afp_code" value="{{ $val('afp_code', $person?->afp_code) }}">
</label>
<label class="field">Pensión
    <input name="afp_name" value="{{ $val('afp_name', $person?->afp_name) }}">
</label>
<label class="field">Caja de compensación
    <input name="compensation_fund" value="{{ $val('compensation_fund', $person?->compensation_fund) }}">
</label>
<label class="field">ARL
    <input name="arl_name" value="{{ $val('arl_name', $person?->arl_name) }}">
</label>
<label class="field">Nivel de riesgo ARL
    <input name="arl_risk_level" value="{{ $val('arl_risk_level', $person?->arl_risk_level) }}">
</label>
