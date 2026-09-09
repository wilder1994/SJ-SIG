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
