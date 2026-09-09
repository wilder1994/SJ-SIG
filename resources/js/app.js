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
    const thumbs = document.getElementById('page-thumbs');
    const hint = document.getElementById('page-hint');
    const typeSelect = document.getElementById('slice-type');
    const nameInput = document.getElementById('slice-name');
    if (!metaNode || !form || !rows || !addBtn || !thumbs || !typeSelect || !nameInput) {
        return;
    }

    const meta = JSON.parse(metaNode.textContent || '{}');
    const types = meta.types || [];
    const selected = new Set();
    let lastPage = null;
    let sliceCount = 0;

    typeSelect.innerHTML = types.map((type) => (
        `<option value="${type.value}">${type.label} (${type.req})</option>`
    )).join('');

    function syncName() {
        const chosen = types.find((type) => type.value === typeSelect.value);
        if (chosen) {
            nameInput.value = chosen.name;
        }
    }

    typeSelect.addEventListener('change', syncName);
    syncName();

    function selectedPages() {
        return [...selected].sort((a, b) => a - b);
    }

    function paintSelection() {
        thumbs.querySelectorAll('.page-thumb').forEach((card) => {
            const page = Number(card.getAttribute('data-page'));
            const on = selected.has(page);
            card.classList.toggle('is-on', on);
            const hit = card.querySelector('.page-thumb-hit');
            if (hit) {
                hit.setAttribute('aria-pressed', on ? 'true' : 'false');
            }
        });
        if (hint) {
            const pages = selectedPages();
            hint.textContent = pages.length
                ? 'Seleccionadas: ' + pages.join(', ') + '.'
                : 'Ninguna página seleccionada.';
        }
    }

    function toggleRange(from, to) {
        const start = Math.min(from, to);
        const end = Math.max(from, to);
        for (let page = start; page <= end; page += 1) {
            selected.add(page);
        }
    }

    thumbs.addEventListener('click', (event) => {
        if (event.target.closest('[data-preview]')) {
            return;
        }
        const hit = event.target.closest('.page-thumb-hit');
        if (!hit) {
            return;
        }
        const page = Number(hit.getAttribute('data-page'));
        if (event.shiftKey && lastPage !== null) {
            toggleRange(lastPage, page);
        } else if (selected.has(page)) {
            selected.delete(page);
        } else {
            selected.add(page);
        }
        lastPage = page;
        paintSelection();
    });

    function addSlice() {
        const pages = selectedPages();
        const chosen = types.find((type) => type.value === typeSelect.value);
        if (!chosen || pages.length === 0) {
            if (hint) {
                hint.textContent = 'Seleccione al menos una página antes de agregar.';
            }
            return;
        }
        const index = sliceCount;
        sliceCount += 1;
        const wrap = document.createElement('div');
        wrap.className = 'slice-row';
        const pageInputs = pages.map((page) => `<input type="hidden" name="slices[${index}][pages][]" value="${page}">`).join('');
        wrap.innerHTML = `
            ${pageInputs}
            <input type="hidden" name="slices[${index}][document_type]" value="${chosen.value}">
            <input type="hidden" name="slices[${index}][display_name]">
            <div>
                <p style="margin:0;font-weight:500" data-slice-label></p>
                <p class="muted" style="margin:2px 0 0">Páginas ${pages.join(', ')}</p>
            </div>
            <p class="muted" style="margin:0" data-slice-name></p>
            <button class="btn ghost" type="button" data-remove-slice>Quitar</button>
        `;
        wrap.querySelector('input[name$="[display_name]"]').value = nameInput.value;
        wrap.querySelector('[data-slice-label]').textContent = chosen.label;
        wrap.querySelector('[data-slice-name]').textContent = nameInput.value;
        wrap.querySelector('[data-remove-slice]')?.addEventListener('click', () => wrap.remove());
        rows.appendChild(wrap);
        wrap.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        selected.clear();
        lastPage = null;
        paintSelection();
    }

    addBtn.addEventListener('click', addSlice);
    form.addEventListener('submit', (event) => {
        if (!rows.children.length) {
            event.preventDefault();
            if (hint) {
                hint.textContent = 'Agregue al menos un documento a la lista.';
            }
        }
    });
    paintSelection();

    async function paintThumbnails() {
        if (!meta.preview_url) {
            return;
        }
        try {
            const pdfjs = await import('pdfjs-dist');
            const worker = await import('pdfjs-dist/build/pdf.worker.min.mjs?url');
            pdfjs.GlobalWorkerOptions.workerSrc = worker.default;
            const doc = await pdfjs.getDocument({ url: meta.preview_url, withCredentials: true }).promise;
            for (let pageNum = 1; pageNum <= doc.numPages; pageNum += 1) {
                const card = thumbs.querySelector(`.page-thumb[data-page="${pageNum}"]`);
                const canvas = card?.querySelector('canvas');
                if (!card || !canvas) {
                    continue;
                }
                const page = await doc.getPage(pageNum);
                const viewport = page.getViewport({ scale: 0.28 });
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                card.classList.add('is-ready');
            }
        } catch {
            // Las tarjetas quedan con el número de página.
        }
    }

    paintThumbnails();
})();

