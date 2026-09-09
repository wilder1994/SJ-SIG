const layer = document.getElementById('preview-layer');
const frame = document.getElementById('preview-iframe');
const title = document.getElementById('preview-title');
const download = document.getElementById('preview-download');
const closeBtn = document.getElementById('preview-close');

function closePreview() {
    if (!layer || !frame) {
        return;
    }
    layer.classList.remove('is-open');
    layer.hidden = true;
    frame.src = '';
}

function openPreview(url, name) {
    if (!layer || !frame) {
        return;
    }
    title.textContent = name;
    download.href = url.replace('/ver', '/descarga');
    frame.src = url;
    layer.hidden = false;
    layer.classList.add('is-open');
}

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');
    if (toggle) {
        event.preventDefault();
        const wrap = toggle.closest('.password-wrap');
        const input = wrap?.querySelector('input');
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
            toggle.textContent = input.type === 'password' ? 'Ver' : 'Ocultar';
        }
        return;
    }
    const trigger = event.target.closest('[data-preview]');
    if (trigger) {
        event.preventDefault();
        openPreview(trigger.getAttribute('data-preview'), trigger.getAttribute('data-name') || 'Documento');
    }
});

closeBtn?.addEventListener('click', closePreview);
layer?.addEventListener('click', (event) => {
    if (event.target === layer) {
        closePreview();
    }
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closePreview();
    }
});

const roleMetaNode = document.getElementById('role-meta');
const roleSelect = document.getElementById('role-select');
const permRow = document.getElementById('perm-row');
const roleHint = document.getElementById('role-hint');
const clientField = document.getElementById('client-field');
const tenantSelect = document.getElementById('tenant-select');
const photoInput = document.getElementById('photo-input');
const photoPreview = document.getElementById('photo-preview');
const photoIcon = document.getElementById('photo-icon');

function renderRole() {
    if (!roleMetaNode || !roleSelect || !permRow) {
        return;
    }
    const meta = JSON.parse(roleMetaNode.textContent || '{}');
    const current = meta[roleSelect.value];
    if (!current) {
        return;
    }
    permRow.innerHTML = current.permissions.map((item) => {
        const on = item.on ? ' is-on' : '';
        return `<span class="perm-chip${on}">${item.label}</span>`;
    }).join('');
    if (roleHint) {
        roleHint.textContent = current.hint;
    }
    if (clientField && tenantSelect) {
        const needs = current.requires_client;
        clientField.style.display = needs ? '' : 'none';
        tenantSelect.required = needs;
        if (!needs) {
            tenantSelect.value = '';
        }
    }
}

roleSelect?.addEventListener('change', renderRole);
renderRole();

photoInput?.addEventListener('change', () => {
    const file = photoInput.files?.[0];
    if (!file || !photoPreview) {
        return;
    }
    photoPreview.src = URL.createObjectURL(file);
    photoPreview.hidden = false;
    if (photoIcon) {
        photoIcon.hidden = true;
    }
});

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-toggle-panel]');
    if (!toggle) {
        return;
    }
    const panel = document.getElementById(toggle.getAttribute('data-toggle-panel') || '');
    if (panel) {
        panel.hidden = !panel.hidden;
    }
});

(function initHistoryIndexer() {
    const metaNode = document.getElementById('history-meta');
    const form = document.getElementById('index-form');
    const rows = document.getElementById('slice-rows');
    const addBtn = document.getElementById('add-slice');
    const chips = document.getElementById('page-chips');
    const hint = document.getElementById('range-hint');
    if (!metaNode || !form || !rows || !addBtn || !chips) {
        return;
    }

    const meta = JSON.parse(metaNode.textContent || '{}');
    const types = meta.types || [];
    let from = 1;
    let to = Number(meta.page_count || 1);
    let picking = 'from';
    let sliceCount = 0;

    function paintChips() {
        chips.querySelectorAll('[data-page]').forEach((chip) => {
            const page = Number(chip.getAttribute('data-page'));
            chip.classList.toggle('is-on', page === from || page === to);
            chip.classList.toggle('is-in', page > Math.min(from, to) && page < Math.max(from, to));
        });
        if (hint) {
            hint.textContent = 'Rango actual: páginas ' + Math.min(from, to) + ' a ' + Math.max(from, to) + '.';
        }
    }

    chips.addEventListener('click', (event) => {
        const chip = event.target.closest('[data-page]');
        if (!chip) {
            return;
        }
        const page = Number(chip.getAttribute('data-page'));
        if (picking === 'from') {
            from = page;
            to = page;
            picking = 'to';
        } else {
            to = page;
            picking = 'from';
        }
        paintChips();
    });

    function optionsHtml(selected) {
        return types.map((type) => {
            const mark = type.value === selected ? ' selected' : '';
            return `<option value="${type.value}" data-name="${type.name}"${mark}>${type.label} (${type.req})</option>`;
        }).join('');
    }

    function addSlice() {
        const start = Math.min(from, to);
        const end = Math.max(from, to);
        const first = types[0];
        if (!first) {
            return;
        }
        const index = sliceCount;
        sliceCount += 1;
        const wrap = document.createElement('div');
        wrap.className = 'slice-row';
        wrap.innerHTML = `
            <label class="field">Tipo
                <select name="slices[${index}][document_type]" data-name-target="slices[${index}][display_name]">${optionsHtml(first.value)}</select>
            </label>
            <label class="field">Desde
                <input type="number" name="slices[${index}][page_from]" min="1" max="${meta.page_count}" value="${start}" required>
            </label>
            <label class="field">Hasta
                <input type="number" name="slices[${index}][page_to]" min="1" max="${meta.page_count}" value="${end}" required>
            </label>
            <label class="field">Nombre
                <input name="slices[${index}][display_name]" value="${first.name}" required>
            </label>
        `;
        rows.appendChild(wrap);
        const select = wrap.querySelector('select');
        select?.addEventListener('change', () => {
            const chosen = types.find((type) => type.value === select.value);
            const nameInput = wrap.querySelector('input[name$="[display_name]"]');
            if (chosen && nameInput) {
                nameInput.value = chosen.name;
            }
        });
    }

    addBtn.addEventListener('click', addSlice);
    addSlice();
    paintChips();
})();

