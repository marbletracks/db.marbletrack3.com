// Photos from badmint (mg #709). "📷 Upload & crop…" opens badmint's /mt3/ page for
// the Part and Workers named in the Notes (a popup on desktop, a tab on the phone).
// Each photo it saves comes back by postMessage and becomes an Image URL row, so this
// form's own Save links it: badmint never creates a Moment. Only badmint is heard, and
// only its one message. Notes, Perspectives and loaded_photo_ids are never touched.
(function () {
  'use strict';
  const BADMINT = 'https://badmint.robnugen.com';
  const fields = document.getElementById('image-url-fields');
  const button = document.getElementById('mt3-photos-open');
  if (!fields || !button) return;

  let perspectives = [];   // {type, id, name} from the latest Notes preview
  window.mt3PhotosPerspectives = function (list) {
    perspectives = Array.isArray(list) ? list : [];
  };

  function uploadUrl() {
    const q = new URLSearchParams({ for: 'moment' });
    if (button.dataset.momentId) q.set('moment', button.dataset.momentId);
    const part = perspectives.find(p => p.type === 'part');
    if (part) q.set('part', String(part.id));
    const workers = perspectives.filter(p => p.type === 'worker').map(p => String(p.id));
    if (workers.length) q.set('worker', workers.join(','));
    return BADMINT + '/mt3/?' + q.toString();
  }
  // One badmint window per form. Reopening by name would navigate it and drop the
  // photos queued there, so a second click brings the open one forward instead.
  let popup = null;
  const note = document.getElementById('mt3-photos-note');
  button.addEventListener('click', function () {
    if (popup && !popup.closed) { popup.focus(); return; }
    popup = window.open(uploadUrl(), 'mt3-photos', 'popup,width=560,height=900');
    if (note) {
      note.textContent = popup ? '' : 'The browser blocked the window: allow popups for this site and try again.';
    }
  });

  function inputs() {
    return Array.from(fields.querySelectorAll('input[name="image_urls[]"]'));
  }
  function addRow(photo) {
    if (inputs().some(i => i.value.trim() === photo.url)) return;   // Retry resends; add once
    const blank = inputs().find(i => i.value.trim() === '') || null;
    const img = document.createElement('img');
    img.src = photo.thumb;
    img.alt = photo.name || 'Image preview';
    const input = document.createElement('input');
    input.type = 'text';
    input.size = 130;
    input.name = 'image_urls[]';
    input.value = photo.url;
    [img, document.createElement('br'), input, document.createElement('br')]
      .forEach(el => fields.insertBefore(el, blank));
  }

  window.addEventListener('message', function (event) {
    if (event.origin !== BADMINT) return;
    const data = event.data;
    if (!data || data.type !== 'mt3-photos' || data.v !== 1 || !Array.isArray(data.photos)) return;
    data.photos.forEach(p => {
      if (p && typeof p.url === 'string' && p.url.startsWith('https://b.robnugen.com/')
          && typeof p.thumb === 'string') {
        addRow(p);
      }
    });
  });
})();
