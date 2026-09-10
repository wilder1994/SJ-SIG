@if($errors->has('workbook'))
    <p style="color:var(--bad);margin:0 0 10px">{{ $errors->first('workbook') }}</p>
@endif
<form class="drop-card" method="post" action="{{ route('people.import.preview') }}" enctype="multipart/form-data" data-dropzone data-accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" data-max="10240">
    @csrf
    <p class="drop-card-title">Carga masiva</p>
    <p class="muted drop-card-hint">Arrastre, pegue o seleccione un Excel. Luego revise altas, cambios y errores antes de importar.</p>
    <input class="drop-input" type="file" name="workbook" accept=".xlsx,.xls" required>
    <div class="drop-empty">
        <span class="drop-icon drop-icon-xls" aria-hidden="true">XLS</span>
        <p>Arrastre, pegue o seleccione un Excel</p>
    </div>
    <div class="drop-ready" hidden>
        <div class="drop-file">
            <span class="drop-icon drop-icon-xls" data-file-icon aria-hidden="true">XLS</span>
            <div>
                <p data-file-name></p>
                <p class="muted" data-file-size></p>
            </div>
        </div>
    </div>
    <p class="muted drop-error" hidden></p>
    <div class="drop-actions">
        <button class="btn ghost" type="button" data-drop-clear hidden>Quitar</button>
        <button class="btn" type="submit" disabled>Revisar</button>
    </div>
</form>
