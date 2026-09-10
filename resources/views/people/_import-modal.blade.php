@if($canImport)
<div class="preview-layer" id="upload-layer" hidden @if($errors->has('workbook')) data-open @endif>
    <div class="upload-frame">
        <div class="preview-bar">
            <span>Carga masiva</span>
            <button class="btn ghost" type="button" data-close-upload style="color:#e8eef6;border-color:rgba(88,196,255,.35)">Cerrar</button>
        </div>
        <div class="upload-body">
            @include('people._import')
        </div>
    </div>
</div>
@endif
