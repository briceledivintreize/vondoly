/* Socle « Plume » — confort de l'administration (aucune dépendance) */
(() => {
  'use strict';
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));
  const csrf = $('meta[name=csrf]')?.content || '';
  const post = (url, body) => fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf }, body: JSON.stringify(body || {}) });

  /* Menu latéral (téléphone) */
  $$('[data-side-toggle]').forEach((b) => b.addEventListener('click', () => {
    const open = document.body.classList.toggle('side-open');
    $('.topbar__menu')?.setAttribute('aria-expanded', String(open));
  }));

  /* Confirmation des suppressions */
  $$('form[data-confirm]').forEach((f) => f.addEventListener('submit', (e) => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));

  /* Zones de texte qui s'agrandissent */
  const grow = (t) => { t.style.height = 'auto'; t.style.height = Math.min(t.scrollHeight + 4, 900) + 'px'; };
  $$('textarea[data-autosize]').forEach((t) => { grow(t); t.addEventListener('input', () => grow(t)); });

  /* Barre de mise en forme (mini-Markdown) */
  $$('.md-tools').forEach((bar) => bar.addEventListener('click', (e) => {
    const b = e.target.closest('[data-md]'); if (!b) return;
    const ta = document.getElementById(bar.dataset.mdFor); if (!ta) return;
    const [s, en] = [ta.selectionStart, ta.selectionEnd];
    const sel = ta.value.slice(s, en) || 'texte';
    let out = sel;
    switch (b.dataset.md) {
      case 'bold': out = `**${sel}**`; break;
      case 'italic': out = `*${sel}*`; break;
      case 'link': { const u = prompt('Adresse du lien (https://…)', 'https://'); if (!u) return; out = `[${sel}](${u})`; break; }
      case 'h3': out = `\n\n### ${sel}\n\n`; break;
      case 'quote': out = `\n\n> ${sel}\n\n`; break;
      case 'list': out = '\n\n' + sel.split('\n').map((l) => '- ' + l).join('\n') + '\n\n'; break;
    }
    ta.setRangeText(out, s, en, 'end'); ta.focus(); grow(ta);
  }));

  /* Images : aperçu immédiat + glisser-déposer */
  $$('[data-img-field]').forEach((box) => {
    const input = $('[data-img-input]', box), prev = $('[data-img-preview]', box), drop = $('.drop', box);
    input.addEventListener('change', () => {
      const f = input.files[0]; if (!f) return;
      if (f.size > 20 * 1024 * 1024) { alert('Image trop lourde (20 Mo maximum).'); input.value = ''; return; }
      prev.src = URL.createObjectURL(f); prev.hidden = false;
      $('.drop__hint', box).firstChild.textContent = f.name + ' ';
    });
    ['dragenter', 'dragover'].forEach((ev) => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach((ev) => drop.addEventListener(ev, () => drop.classList.remove('is-over')));
    drop.addEventListener('drop', (e) => { e.preventDefault(); if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; input.dispatchEvent(new Event('change')); } });
  });

  /* Prévenir la perte d'une saisie non enregistrée */
  $$('[data-form], .form-texts, [data-settings]').forEach((f) => {
    let dirty = false;
    f.addEventListener('input', () => { dirty = true; });
    f.addEventListener('submit', () => { dirty = false; });
    addEventListener('beforeunload', (e) => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  });

  /* Liste : filtre, visibilité, tri */
  const filter = $('[data-filter-list]');
  filter?.addEventListener('input', () => {
    const q = filter.value.toLowerCase().trim();
    $$('.row').forEach((r) => { r.hidden = q && !r.dataset.text.includes(q); });
  });
  $$('[data-toggle]').forEach((b) => b.addEventListener('click', async () => {
    const r = await post(b.dataset.toggle);
    if (!r.ok) return alert('Action impossible, rechargez la page.');
    const { visible } = await r.json();
    b.setAttribute('aria-checked', visible ? 'true' : 'false');
    b.closest('.row')?.classList.toggle('is-hidden', !visible);
  }));
  const list = $('.rows--sortable');
  if (list) {
    const save = () => post(list.dataset.sortUrl, { ids: $$('.row', list).map((r) => r.dataset.id) });
    let drag = null;
    list.addEventListener('dragstart', (e) => { drag = e.target.closest('.row'); drag?.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; });
    list.addEventListener('dragend', () => { drag?.classList.remove('is-dragging'); $$('.drop-before', list).forEach((x) => x.classList.remove('drop-before')); drag = null; save(); });
    list.addEventListener('dragover', (e) => {
      e.preventDefault();
      const over = e.target.closest('.row'); if (!over || over === drag) return;
      const r = over.getBoundingClientRect();
      list.insertBefore(drag, e.clientY < r.top + r.height / 2 ? over : over.nextSibling);
    });
    list.addEventListener('click', (e) => {
      const b = e.target.closest('[data-move]'); if (!b) return;
      const row = b.closest('.row');
      if (b.dataset.move === '-1' && row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
      if (b.dataset.move === '1' && row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
      b.focus(); save();
    });
  }

  /* Réglages : onglets, palettes, aperçu WhatsApp en direct */
  const settings = $('[data-settings]');
  if (settings) {
    settings.classList.add('js-tabs');
    const tabs = $$('[data-tab]', settings), tabInput = $('[data-tab-input]', settings);
    const show = (id) => {
      tabs.forEach((t) => (t.dataset.tab === id ? t.setAttribute('aria-current', 'page') : t.removeAttribute('aria-current')));
      $$('[data-panel]', settings).forEach((p) => p.classList.toggle('is-active', p.dataset.panel === id));
      tabInput.value = id;
    };
    tabs.forEach((t) => t.addEventListener('click', (e) => { e.preventDefault(); show(t.dataset.tab); history.replaceState(null, '', '#' + t.dataset.tab); }));
    show((location.hash || '#' + tabs[0].dataset.tab).slice(1));
    $$('[data-palette]', settings).forEach((b) => b.addEventListener('click', () => {
      Object.entries(JSON.parse(b.dataset.palette)).forEach(([k, v]) => { const i = $(`input[data-key="${k}"]`, settings); if (i) { i.value = v; i.nextElementSibling.textContent = v; } });
      settings.dispatchEvent(new Event('input'));
    }));
    $$('input[type=color]', settings).forEach((i) => i.addEventListener('input', () => { i.nextElementSibling.textContent = i.value; }));
    const tpl = $('[data-wa-template]', settings), prov = $('[data-wa-prov]', settings), out = $('[data-wa-preview]', settings);
    const dom = $('input[name="s[site.domain]"]', settings);
    const render = () => {
      if (!out) return;
      const msg = (tpl.value || '').replaceAll('{titre}', 'Senghor ou rien').replaceAll('{edition}', '2025').replaceAll('{annee}', '2025');
      const p = (prov.value || '').replaceAll('{domaine}', dom?.value || '');
      out.textContent = msg + (p ? '\n\n' + p : '');
    };
    [tpl, prov, dom].forEach((x) => x?.addEventListener('input', render)); render();
  }
})();
