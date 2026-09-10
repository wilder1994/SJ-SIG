@php
    $staffings = $post['staffings'] ?? [['role' => 'vigilante', 'slots' => 1]];
@endphp
<article class="post-block" data-post-block>
    @if(! empty($post['id']))
        <input type="hidden" name="posts[{{ $index }}][id]" value="{{ $post['id'] }}">
    @endif
    <div class="form-grid">
        <label class="field">Puesto
            <input name="posts[{{ $index }}][name]" placeholder="Portería, ronda, parqueadero…" value="{{ $post['name'] ?? '' }}">
        </label>
        <label class="field">Modalidad
            <select name="posts[{{ $index }}][shift_hours]">
                @foreach($modalities as $modality)
                    <option value="{{ $modality->value }}" @selected((int) ($post['shift_hours'] ?? 12) === $modality->value)>{{ $modality->label() }}</option>
                @endforeach
            </select>
        </label>
    </div>
    <div data-staff-list style="margin-top:10px">
        @foreach($staffings as $sIndex => $staff)
            <div class="staff-row" data-staff-row>
                <label class="field">Cargo
                    <select name="posts[{{ $index }}][staffings][{{ $sIndex }}][role]">
                        @foreach($roles as $role)
                            <option value="{{ $role->value }}" @selected(($staff['role'] ?? 'vigilante') === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">Unidades
                    <input type="number" name="posts[{{ $index }}][staffings][{{ $sIndex }}][slots]" min="1" max="99" value="{{ $staff['slots'] ?? 1 }}">
                </label>
            </div>
        @endforeach
    </div>
    <button class="btn ghost" type="button" data-add-staff style="margin-top:8px">Agregar cargo</button>
    <template data-staff-template>
        <div class="staff-row" data-staff-row>
            <label class="field">Cargo
                <select name="posts[{{ $index }}][staffings][__j__][role]">
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">Unidades
                <input type="number" name="posts[{{ $index }}][staffings][__j__][slots]" min="1" max="99" value="1">
            </label>
        </div>
    </template>
</article>
