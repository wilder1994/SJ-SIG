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

