import { bootMaps } from './maps.js';

bootMaps();

(function initRail() {
    const shell = document.querySelector('[data-shell]');
    const toggle = document.querySelector('[data-rail-toggle]');
    if (!shell || !toggle) {
        return;
    }

    const apply = (off) => {
        shell.classList.toggle('is-rail-off', off);
        toggle.setAttribute('aria-expanded', off ? 'false' : 'true');
        toggle.setAttribute('aria-label', off ? 'Mostrar menú' : 'Ocultar menú');
        toggle.textContent = off ? '›' : '‹';
        try {
            localStorage.setItem('sj-rail', off ? 'off' : 'on');
        } catch {
            // ignore quota / private mode
        }
    };

    apply(shell.classList.contains('is-rail-off'));
    toggle.addEventListener('click', () => apply(! shell.classList.contains('is-rail-off')));
})();

(function initSiteForm() {
    const form = document.querySelector('[data-site-form]');
    const list = form?.querySelector('[data-post-list]');
    const template = form?.querySelector('[data-post-template]');
    if (!form || !list || !template) {
        return;
    }

    const rewrite = () => {
        [...list.querySelectorAll('[data-post-block]')].forEach((block, index) => {
            block.querySelectorAll('input, select, textarea').forEach((field) => {
                const name = field.getAttribute('name') || '';
                if (name.includes('[id]')) {
                    field.name = `posts[${index}][id]`;
                } else if (name.includes('[shift_hours]')) {
                    field.name = `posts[${index}][shift_hours]`;
                } else if (name.endsWith('[name]')) {
                    field.name = `posts[${index}][name]`;
                }
            });
            [...block.querySelectorAll('[data-staff-row]')].forEach((row, staffIndex) => {
                row.querySelectorAll('select').forEach((field) => {
                    field.name = `posts[${index}][staffings][${staffIndex}][role]`;
                });
                row.querySelectorAll('input[type="number"]').forEach((field) => {
                    field.name = `posts[${index}][staffings][${staffIndex}][slots]`;
                });
            });
        });
    };

    form.querySelector('[data-add-post]')?.addEventListener('click', () => {
        list.insertAdjacentHTML('beforeend', template.innerHTML);
        rewrite();
    });

    form.addEventListener('click', (event) => {
        const button = event.target.closest('[data-add-staff]');
        if (! button) {
            return;
        }
        const block = button.closest('[data-post-block]');
        const staffTemplate = block?.querySelector('[data-staff-template]');
        const staffList = block?.querySelector('[data-staff-list]');
        if (! staffTemplate || ! staffList) {
            return;
        }
        staffList.insertAdjacentHTML('beforeend', staffTemplate.innerHTML);
        rewrite();
    });
})();

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
    if (event.key !== 'Escape') {
        return;
    }
    const upload = document.getElementById('upload-layer');
    if (upload?.classList.contains('is-open')) {
        upload.classList.remove('is-open');
        upload.hidden = true;
        return;
    }
    const folderLayer = document.getElementById('folder-layer');
    if (folderLayer?.classList.contains('is-open')) {
        folderLayer.classList.remove('is-open');
        folderLayer.hidden = true;
        return;
    }
    closePreview();
});

(function initFolderModal() {
    const layer = document.getElementById('folder-layer');
    if (!layer) {
        return;
    }

    const panes = [...layer.querySelectorAll('[data-folder-pane]')];

    const close = () => {
        layer.hidden = true;
        layer.classList.remove('is-open');
        panes.forEach((pane) => {
            pane.hidden = true;
        });
    };

    const open = (id) => {
        panes.forEach((pane) => {
            pane.hidden = pane.getAttribute('data-folder-pane') !== id;
        });
        layer.hidden = false;
        layer.classList.add('is-open');
        const pane = panes.find((item) => item.getAttribute('data-folder-pane') === id);
        const search = pane?.querySelector('[data-folder-search]');
        if (search instanceof HTMLInputElement) {
            search.value = '';
            search.dispatchEvent(new Event('input'));
            search.focus();
        }
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-open-folder]');
        if (trigger) {
            event.preventDefault();
            open(trigger.getAttribute('data-open-folder') || '');
        }
        if (event.target.closest('[data-close-folder]')) {
            event.preventDefault();
            close();
        }
    });
    layer.addEventListener('click', (event) => {
        if (event.target === layer) {
            close();
        }
    });
    layer.addEventListener('input', (event) => {
        const search = event.target;
        if (!(search instanceof HTMLInputElement) || !search.hasAttribute('data-folder-search')) {
            return;
        }
        const pane = search.closest('[data-folder-pane]');
        const needle = search.value.trim().toLowerCase();
        pane?.querySelectorAll('[data-doc-search]').forEach((row) => {
            const hay = row.getAttribute('data-doc-search') || '';
            row.hidden = needle !== '' && !hay.includes(needle);
        });
    });
})();

(function initUploadModal() {
    const layer = document.getElementById('upload-layer');
    if (!layer) {
        return;
    }

    const open = () => {
        layer.hidden = false;
        layer.classList.add('is-open');
    };
    const close = () => {
        layer.hidden = true;
        layer.classList.remove('is-open');
    };

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-open-upload]')) {
            event.preventDefault();
            open();
        }
        if (event.target.closest('[data-close-upload]')) {
            event.preventDefault();
            close();
        }
    });
    layer.addEventListener('click', (event) => {
        if (event.target === layer) {
            close();
        }
    });
    if (layer.hasAttribute('data-open')) {
        open();
    }
})();

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
    if (photoInput.hasAttribute('data-photo-autosubmit')) {
        photoInput.form?.requestSubmit();
    }
});

(function initDropzones() {
    const cards = [...document.querySelectorAll('[data-dropzone]')];
    if (!cards.length) {
        return;
    }

    let hovered = null;

    function accepts(file, accept) {
        const tokens = (accept || '').split(',').map((item) => item.trim().toLowerCase()).filter(Boolean);
        const name = file.name.toLowerCase();
        const type = (file.type || '').toLowerCase();
        return tokens.some((token) => {
            if (token.startsWith('.')) {
                return name.endsWith(token);
            }
            return type === token;
        });
    }

    function formatSize(bytes) {
        if (bytes < 1024) {
            return bytes + ' B';
        }
        if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(1) + ' KB';
        }
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function paint(card, file) {
        const empty = card.querySelector('.drop-empty');
        const ready = card.querySelector('.drop-ready');
        const error = card.querySelector('.drop-error');
        const clear = card.querySelector('[data-drop-clear]');
        const submit = card.querySelector('button[type="submit"]');
        const name = card.querySelector('[data-file-name]');
        const size = card.querySelector('[data-file-size]');
        const icon = card.querySelector('[data-file-icon]');
        const isExcel = !!file && (/\.(xlsx|xls)$/i.test(file.name) || /spreadsheet|excel/i.test(file.type || ''));
        const isImage = !!file && !isExcel && (/^image\//.test(file.type || '') || /\.(jpe?g|png)$/i.test(file.name));
        if (empty) {
            empty.hidden = !!file;
        }
        if (ready) {
            ready.hidden = !file;
        }
        if (clear) {
            clear.hidden = !file;
        }
        if (submit) {
            submit.disabled = !file;
        }
        if (error) {
            error.hidden = true;
            error.textContent = '';
        }
        card.classList.toggle('is-ready', !!file);
        if (file && name) {
            name.textContent = file.name;
        }
        if (file && size) {
            size.textContent = formatSize(file.size);
        }
        if (icon) {
            icon.textContent = isExcel ? 'XLS' : (isImage ? 'IMG' : 'PDF');
            icon.classList.toggle('drop-icon-xls', isExcel);
            icon.classList.toggle('drop-icon-img', isImage);
            icon.classList.toggle('drop-icon-pdf', !isImage && !isExcel);
        }
    }

    function showError(card, message) {
        const error = card.querySelector('.drop-error');
        if (error) {
            error.hidden = false;
            error.textContent = message;
        }
    }

    function assign(card, file) {
        const input = card.querySelector('.drop-input');
        const accept = card.getAttribute('data-accept') || '';
        const maxKb = Number(card.getAttribute('data-max') || 0);
        if (!file || !input) {
            return;
        }
        if (!accepts(file, accept)) {
            showError(card, 'Este archivo no es válido para esta carpeta.');
            return;
        }
        if (maxKb > 0 && file.size > maxKb * 1024) {
            showError(card, 'El archivo supera el tamaño máximo.');
            return;
        }
        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
        paint(card, file);
    }

    function clearCard(card) {
        const input = card.querySelector('.drop-input');
        if (input) {
            input.value = '';
        }
        paint(card, null);
    }

    cards.forEach((card) => {
        const input = card.querySelector('.drop-input');
        card.addEventListener('mouseenter', () => {
            hovered = card;
        });
        card.addEventListener('click', (event) => {
            if (event.target.closest('button, a, input, label, select, textarea')) {
                return;
            }
            input?.click();
        });
        card.querySelector('.drop-empty')?.addEventListener('click', () => input?.click());
        input?.addEventListener('change', () => assign(card, input.files?.[0]));
        card.addEventListener('dragover', (event) => {
            event.preventDefault();
            card.classList.add('is-over');
        });
        card.addEventListener('dragleave', () => card.classList.remove('is-over'));
        card.addEventListener('drop', (event) => {
            event.preventDefault();
            card.classList.remove('is-over');
            assign(card, event.dataTransfer?.files?.[0]);
        });
        card.querySelector('[data-drop-clear]')?.addEventListener('click', () => clearCard(card));
        paint(card, null);
    });

    document.addEventListener('paste', (event) => {
        const target = event.target;
        if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement) {
            return;
        }
        const file = event.clipboardData?.files?.[0];
        const uploadLayer = document.getElementById('upload-layer');
        const modalCard = uploadLayer?.classList.contains('is-open')
            ? uploadLayer.querySelector('[data-dropzone]')
            : null;
        const dropTarget = modalCard || hovered;
        if (file && dropTarget) {
            event.preventDefault();
            assign(dropTarget, file);
        }
    });
})();

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
    const folderSelect = document.getElementById('slice-folder');
    const typeSelect = document.getElementById('slice-type');
    const typeField = document.getElementById('type-field');
    const tipoField = document.getElementById('tipo-field');
    const tipoInput = document.getElementById('slice-tipo');
    const nameInput = document.getElementById('slice-name');
    const courseFields = document.getElementById('course-fields');
    const takenOnInput = document.getElementById('slice-taken-on');
    const providerInput = document.getElementById('slice-provider');
    if (!metaNode || !form || !rows || !addBtn || !thumbs || !typeSelect || !nameInput) {
        return;
    }

    const meta = JSON.parse(metaNode.textContent || '{}');
    const catalogs = meta.catalogs || [];
    const selected = new Set();
    const assigned = new Set();
    const totalPages = Number(meta.page_count) || thumbs.querySelectorAll('.page-thumb').length;
    let lastPage = null;
    let sliceCount = 0;

    if (folderSelect) {
        folderSelect.innerHTML = catalogs.map((catalog) => (
            `<option value="${catalog.value}">${catalog.label}</option>`
        )).join('');
    }

    function currentCatalog() {
        return catalogs.find((catalog) => catalog.value === folderSelect?.value) || catalogs[0] || null;
    }

    function applyCatalog() {
        const catalog = currentCatalog();
        const otherOn = Boolean(catalog?.other_fields);
        const courseOn = Boolean(catalog?.course_fields);
        if (typeField) {
            typeField.hidden = otherOn;
        }
        if (tipoField) {
            tipoField.hidden = !otherOn;
        }
        if (courseFields) {
            courseFields.hidden = !courseOn;
        }
        if (otherOn) {
            typeSelect.innerHTML = `<option value="${meta.other_type}">Otro soporte</option>`;
        } else {
            typeSelect.innerHTML = (catalog?.types || []).map((type) => (
                `<option value="${type.value}">${type.label} (${type.req})</option>`
            )).join('');
        }
        if (tipoInput) {
            tipoInput.value = '';
        }
        syncName();
    }

    function slugTipo(text) {
        return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^A-Za-z0-9]+/g, '_')
            .replace(/^_|_$/g, '')
            .slice(0, 40);
    }

    function normalizeKey(text) {
        return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/\.pdf$/i, '')
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_|_$/g, '');
    }

    function reservedHit(tipo, name) {
        const suffix = meta.name_suffix || '';
        const base = name.replace(/\.pdf$/i, '');
        const stem = suffix && base.endsWith(suffix) ? base.slice(0, -suffix.length) : base;
        const needles = [normalizeKey(tipo), normalizeKey(name), normalizeKey(stem)].filter(Boolean);
        for (const reserved of meta.reserved || []) {
            for (const needle of needles) {
                for (const key of ['slug', 'label_slug', 'value', 'name']) {
                    const token = reserved[key] || '';
                    if (token && (needle === token || needle.startsWith(`${token}_`))) {
                        return reserved;
                    }
                }
            }
        }
        return null;
    }

    function syncName() {
        const catalog = currentCatalog();
        if (catalog?.other_fields) {
            const slug = slugTipo(tipoInput?.value || '');
            nameInput.value = slug ? slug + (meta.name_suffix || '') : '';
            return;
        }
        const chosen = (catalog?.types || []).find((type) => type.value === typeSelect.value);
        if (chosen) {
            nameInput.value = chosen.name;
        }
    }

    folderSelect?.addEventListener('change', applyCatalog);
    typeSelect.addEventListener('change', syncName);
    tipoInput?.addEventListener('input', syncName);
    applyCatalog();

    function selectedPages() {
        return [...selected].sort((a, b) => a - b);
    }

    function paintSelection() {
        thumbs.querySelectorAll('.page-thumb').forEach((card) => {
            const page = Number(card.getAttribute('data-page'));
            const used = assigned.has(page);
            card.hidden = used;
            const on = !used && selected.has(page);
            card.classList.toggle('is-on', on);
            const hit = card.querySelector('.page-thumb-hit');
            if (hit) {
                hit.setAttribute('aria-pressed', on ? 'true' : 'false');
            }
        });
        const left = Math.max(0, totalPages - assigned.size);
        const remaining = document.getElementById('pages-remaining');
        if (remaining) {
            remaining.textContent = assigned.size === 0
                ? `${totalPages} página${totalPages === 1 ? '' : 's'}`
                : `${left} restante${left === 1 ? '' : 's'} de ${totalPages}`;
        }
        if (hint) {
            const pages = selectedPages();
            hint.textContent = pages.length
                ? 'Seleccionadas: ' + pages.join(', ') + '.'
                : (left === 0 && assigned.size > 0
                    ? 'Todas las páginas están en la lista.'
                    : 'Ninguna página seleccionada.');
        }
    }

    function toggleRange(from, to) {
        const start = Math.min(from, to);
        const end = Math.max(from, to);
        for (let page = start; page <= end; page += 1) {
            if (!assigned.has(page)) {
                selected.add(page);
            }
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
        if (assigned.has(page)) {
            return;
        }
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
        const catalog = currentCatalog();
        const otherOn = Boolean(catalog?.other_fields);
        const courseOn = Boolean(catalog?.course_fields);
        const chosen = (catalog?.types || []).find((type) => type.value === typeSelect.value)
            || (otherOn ? { value: meta.other_type, label: (tipoInput?.value || '').trim() } : null);
        if (!catalog || !chosen || pages.length === 0) {
            if (hint) {
                hint.textContent = 'Seleccione al menos una página antes de agregar.';
            }
            return;
        }
        if (courseOn && (!takenOnInput?.value || !providerInput?.value.trim())) {
            if (hint) {
                hint.textContent = 'Indique la fecha y la entidad que dicta el curso.';
            }
            return;
        }
        if (otherOn) {
            const tipo = (tipoInput?.value || '').trim();
            if (!tipo) {
                if (hint) {
                    hint.textContent = 'Digite el tipo del soporte.';
                }
                return;
            }
            const othersInList = [...rows.querySelectorAll('input[name$="[folder]"]')]
                .filter((input) => input.value === 'otros').length;
            if ((meta.existing_count || 0) + othersInList + 1 > (meta.max_others || 20)) {
                if (hint) {
                    hint.textContent = `Solo se permiten ${meta.max_others || 20} soportes en Otros por trabajador.`;
                }
                return;
            }
            const clash = reservedHit(tipo, nameInput.value);
            if (clash) {
                if (hint) {
                    hint.textContent = `Ese tipo o nombre coincide con ${clash.folder} (${clash.label}). Cámbielo o cárguelo en esa carpeta.`;
                }
                return;
            }
        }
        const index = sliceCount;
        sliceCount += 1;
        const wrap = document.createElement('div');
        wrap.className = 'slice-row';
        const pageInputs = pages.map((page) => `<input type="hidden" name="slices[${index}][pages][]" value="${page}">`).join('');
        const courseInputs = courseOn
            ? `<input type="hidden" name="slices[${index}][taken_on]" value="${takenOnInput.value}"><input type="hidden" name="slices[${index}][provider]">`
            : '';
        const tipoHidden = otherOn
            ? `<input type="hidden" name="slices[${index}][tipo]" value="">`
            : '';
        wrap.innerHTML = `
            ${pageInputs}
            ${courseInputs}
            ${tipoHidden}
            <input type="hidden" name="slices[${index}][folder]" value="${catalog.value}">
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
        if (courseOn) {
            wrap.querySelector('input[name$="[provider]"]').value = providerInput.value.trim();
        }
        if (otherOn) {
            wrap.querySelector('input[name$="[tipo]"]').value = (tipoInput?.value || '').trim();
        }
        wrap.querySelector('[data-slice-label]').textContent = otherOn
            ? `${catalog.label} · ${(tipoInput?.value || '').trim()}`
            : `${catalog.label} · ${chosen.label}`;
        const extra = courseOn
            ? ` · ${providerInput.value.trim()} · ${takenOnInput.value}`
            : '';
        wrap.querySelector('[data-slice-name]').textContent = nameInput.value + extra;
        wrap.dataset.pages = pages.join(',');
        wrap.querySelector('[data-remove-slice]')?.addEventListener('click', () => {
            (wrap.dataset.pages || '').split(',').forEach((item) => {
                const page = Number(item);
                if (page > 0) {
                    assigned.delete(page);
                }
            });
            wrap.remove();
            lastPage = null;
            paintSelection();
        });
        pages.forEach((page) => assigned.add(page));
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

