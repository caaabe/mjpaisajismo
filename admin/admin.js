const photoInput = document.getElementById('photoInput');
let previewUrls = [];
photoInput?.addEventListener('change', () => {
  previewUrls.forEach(URL.revokeObjectURL); previewUrls = [];
  const preview = document.getElementById('uploadPreview'); preview.replaceChildren();
  const files = Array.from(photoInput.files);
  const invalid = files.length > 10 || files.some(f => f.size > 10 * 1024 * 1024 || !['image/jpeg','image/png','image/webp'].includes(f.type));
  photoInput.setCustomValidity(invalid ? 'Elegí hasta 10 fotos JPG, PNG o WebP, de hasta 10 MB cada una.' : '');
  if (invalid) { photoInput.reportValidity(); return; }
  files.forEach(file => {
    const figure = document.createElement('figure'); const img = document.createElement('img');
    const url = URL.createObjectURL(file); previewUrls.push(url); img.src = url; img.alt = '';
    const caption = document.createElement('figcaption'); caption.textContent = file.name;
    figure.append(img, caption); preview.append(figure);
  });
});
document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => {
  if (!window.confirm(form.dataset.confirm)) event.preventDefault();
}));
document.getElementById('projectForm')?.addEventListener('submit', event => {
  const status = document.getElementById('saveStatus'); status.textContent = 'Guardando… Esperá a que termine la subida.';
  event.submitter.disabled = true;
});
