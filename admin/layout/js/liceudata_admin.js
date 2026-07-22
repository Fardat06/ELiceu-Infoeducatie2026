/* admin/layout/js/liceudata_admin.js — CRUD unificat pe 4 tabele */
$(function () {

  const API   = window.API || 'plugin/liceudata_api.php';
  const YEARS = [2025, 2024, 2023, 2022, 2021, 2020];

  const modalEl = document.getElementById('modalForm');
  const intEl   = document.getElementById('modalInt');

  const modal = {
    show: () => { modalEl.hidden = false; document.body.style.overflow = 'hidden'; },
    hide: () => { modalEl.hidden = true;  document.body.style.overflow = ''; }
  };
  const intModal = {
    show: () => { intEl.hidden = false; document.body.style.overflow = 'hidden'; },
    hide: () => { intEl.hidden = true;  document.body.style.overflow = ''; }
  };

  $('#modalForm').on('click', function (ev) {
    if (ev.target === this || ev.target.closest('[data-close]')) modal.hide();
  });
  $('#modalInt').on('click', function (ev) {
    if (ev.target === this || ev.target.closest('[data-close-int]')) intModal.hide();
  });
  $(document).on('keydown', ev => {
    if (ev.key !== 'Escape') return;
    if (!intEl.hidden) intModal.hide();
    else if (!modalEl.hidden) modal.hide();
  });
  $('.modal-tab').on('click', function () {
    $('.modal-tab').removeClass('active');
    $('.modal-pane').removeClass('active');
    $(this).addClass('active');
    $('#' + $(this).data('tab')).addClass('active');
  });
  function firstTab() { $('.modal-tab').first().trigger('click'); }

  let selected = new Set();

  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, c =>
      ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }
  function escRe(s) { return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

  function notify(msg, type = 'success') {
    const id = 'a' + Date.now() + Math.random().toString(36).slice(2, 6);
    $('#alertBox').append(
      `<div id="${id}" class="toast-msg ${type}">
         <span>${msg}</span><button type="button" class="toast-close">&times;</button></div>`);
    const $el = $('#' + id);
    $el.on('click', '.toast-close', () => $el.remove());
    setTimeout(() => $el.fadeOut(200, function () { $(this).remove(); }), 5500);
  }

  function medieClass(v) {
    v = parseFloat(v) || 0;
    if (v >= 8) return 'success';
    if (v >= 6) return 'warning';
    return 'danger';
  }

  /* ---------------- DataTable ---------------- */
  const dt = $('#tbl').DataTable({
    ajax: { url: API + '?action=list', dataSrc: 'data' },
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100, 250],
    order: [[2, 'asc']],
    language: {
      search: 'Caută:', searchPlaceholder: 'liceu, specializare…',
      lengthMenu: 'Arată _MENU_ înregistrări',
      info: 'Afișate _START_–_END_ din _TOTAL_',
      infoEmpty: 'Nicio înregistrare', infoFiltered: '(filtrate din _MAX_)',
      zeroRecords: 'Niciun rezultat', emptyTable: 'Tabel gol',
      paginate: { first: '«', last: '»', next: '›', previous: '‹' }
    },
    columns: [
      { data: 'id', orderable: false, searchable: false,
        render: id => `<input type="checkbox" class="rowChk" value="${id}">` },

      { data: 'id', width: '70px',
        render: (id, t, r) => {
          if (t !== 'display') return +id;
          let warn = '';
          if (r.problema) {
            const p = [];
            if (r.lipsa.length)     p.push('lipsă: ' + r.lipsa.join(', '));
            if (r.nealiniat.length) p.push('nealiniat: ' + r.nealiniat.join(', '));
            warn = ` <span class="status-badge danger" title="${esc(p.join(' · '))}">!</span>`;
          }
          return `${+id}${warn}`;
        } },

      { data: 'name',
        render: (n, t, r) => t !== 'display' ? n :
          `<div class="project-info">
             <div class="project-title-text">${esc(n)}</div>
             <div class="project-meta-text">${esc(r.tip || '')}</div>
           </div>` },

      { data: 'specializare',
        render: (s, t, r) => t !== 'display' ? s :
          `<div>${esc(s)}</div>` +
          (r.bilingv && r.bilingv !== '-'
            ? `<div class="project-meta-text">${esc(r.bilingv)}</div>` : '') },

      { data: 'profil' },
      { data: 'zone' },

      { data: 'locuri_2025',
        render: v => v === null ? '<span class="muted">–</span>' : v },

      { data: 'u_medie_2025',
        render: (v, t) => {
          if (t !== 'display') return parseFloat(v) || 0;
          if (v === null || +v === 0) return '<span class="muted">–</span>';
          return `<span class="status-badge ${medieClass(v)}">${parseFloat(v).toFixed(2)}</span>`;
        } },

      { data: 'u_pozition_2025',
        render: v => (v === null || +v === 0) ? '<span class="muted">–</span>' : (+v).toLocaleString('ro-RO') },

      { data: 'stopx',
        render: (v, t) => {
          const label = (+v === 1) ? 'Ascuns' : 'Activ';
          if (t !== 'display') return label;
          return (+v === 1)
            ? '<span class="status-badge warning">Ascuns</span>'
            : '<span class="status-badge success">Activ</span>';
        } },

      { data: 'id', orderable: false, searchable: false,
        render: id => `
          <div class="row-actions">
            <button class="icon-btn btnEdit" data-id="${id}" title="Modifică">
              <span class="material-symbols-rounded">edit</span></button>
            <button class="icon-btn btnToggle" data-id="${id}" title="Ascunde / Afișează">
              <span class="material-symbols-rounded">visibility</span></button>
            <button class="icon-btn danger btnDel" data-id="${id}" title="Șterge din toate tabelele">
              <span class="material-symbols-rounded">delete</span></button>
          </div>` }
    ],
    initComplete: function () { stats(this.api()); }
  });

  function stats(api) {
    const d = api.rows().data().toArray();
    $('#sTotal').text(d.length);
    $('#sLicee').text(new Set(d.map(r => r.name)).size);
    $('#sLocuri').text(d.reduce((s, r) => s + (+r.locuri_2025 || 0), 0).toLocaleString('ro-RO'));
    $('#sProbleme').text(d.filter(r => r.problema).length);
  }
  dt.on('xhr', () => setTimeout(() => stats(dt), 0));

  /* ---------------- filtre ---------------- */
  $('#fLiceu').on('change', function () {
    dt.column(2).search(this.value ? '^' + escRe(this.value) + '$' : '', true, false).draw();
  });
  $('#fProfil').on('change', function () {
    dt.column(4).search(this.value ? '^' + escRe(this.value) + '$' : '', true, false).draw();
  });
  $('#fZone').on('change', function () {
    dt.column(5).search(this.value ? '^' + escRe(this.value) + '$' : '', true, false).draw();
  });

  $.fn.dataTable.ext.search.push(function (settings, data, idx) {
    if (settings.nTable.id !== 'tbl') return true;
    return $('#fProb').val() !== '1' || !!dt.row(idx).data().problema;
  });
  $('#fProb').on('change', () => dt.draw());

/* ---------------- lookups ---------------- */
  let lookupsReady = false;

  function fillSelect($sel, values, keepFirst = true) {
    const first = keepFirst ? $sel.find('option').first() : null;
    $sel.empty();
    if (first && first.length) $sel.append(first);
    values.forEach(v => $sel.append(new Option(v, v)));
  }

  /** Adaugă o valoare care nu există în listă (date vechi, neconforme). */
  function ensureOption($sel, val) {
    if (!val) return;
    const exists = $sel.find('option').filter(function () { return this.value === val; }).length;
    if (!exists) {
      $sel.append(new Option(val + '  (valoare veche)', val));
    }
    $sel.val(val);
  }

  const lookupsLoaded = $.getJSON(API + '?action=lookups', d => {
    if (!d || !d.ok) return;

    // filtre din antetul tabelului
    d.name.forEach(v   => $('#fLiceu').append(new Option(v, v)));
    d.profil.forEach(v => $('#fProfil').append(new Option(v, v)));
    d.zone.forEach(v   => { $('#fZone').append(new Option(v, v)); $('#dlZone').append(`<option value="${esc(v)}">`); });

    // selecturi din formular
    fillSelect($('#fName'),     d.name);
    fillSelect($('#fTip'),      d.tip);
    fillSelect($('#fProfilIn'), d.profil);
    fillSelect($('#fSpec'),     d.specializare);
    fillSelect($('#fLimba'),    d.limba);
    fillSelect($('#fBil'),      d.bilingv);

    lookupsReady = true;
  });

  /* ---------------- selecție ---------------- */
  function refreshSel() {
    $('#selCount').text(selected.size);
    $('#btnBulkDelete').prop('disabled', selected.size === 0);
  }
  $('#tbl tbody').on('change', '.rowChk', function () {
    this.checked ? selected.add(this.value) : selected.delete(this.value);
    refreshSel();
  });
  $('#chkAll').on('change', function () {
    const on = this.checked;
    $('#tbl tbody .rowChk').each(function () {
      this.checked = on;
      on ? selected.add(this.value) : selected.delete(this.value);
    });
    refreshSel();
  });
  dt.on('draw', function () {
    $('#tbl tbody .rowChk').each(function () { this.checked = selected.has(this.value); });
    refreshSel();
  });

  /* ---------------- ADAUGĂ ---------------- */
$('#btnAdd').on('click', function () {
    $('#frm')[0].reset();
    $('#fAction').val('create');
    $('#fId').val('');
    $('#modalTitle').text('Adaugă specializare');

    $('#fName, #fTip, #fProfilIn, #fSpec').val('');
    $('#fLimba').val('Limba română');
    $('#fBil').val('-');
    $('#fInt').val('nu');
    $('#fCity').val('Bucuresti');
    ['fP9','fP10','fP11','fP12'].forEach(k => $('#' + k).val('dimi'));

    firstTab();
    modal.show();
    setTimeout(() => $('#fName').trigger('focus'), 50);
  });

$('#tbl tbody').on('click', '.btnEdit', function () {
    const id = $(this).data('id');

    $.when(lookupsLoaded).always(function () {
      $.getJSON(API + '?action=get&id=' + encodeURIComponent(id), d => {
        if (!d.ok) return notify(d.msg, 'danger');
        const l = d.liceu, lo = d.locuri, m = d.medie, p = d.pozitie;

        $('#fAction').val('update');
        $('#fId').val(l.id);
        $('#modalTitle').text('Modifică #' + (+l.id) + ': ' + l.name + ' – ' + l.specializare);

        // selecturi — adăugăm opțiunea dacă valoarea nu e în lista de referință
        ensureOption($('#fName'),     l.name);
        ensureOption($('#fTip'),      l.tip);
        ensureOption($('#fProfilIn'), l.profil);
        ensureOption($('#fSpec'),     l.specializare);
        ensureOption($('#fLimba'),    l.limba);
        ensureOption($('#fBil'),      l.bilingv);

        // câmpuri text
        $('#fInt').val(l.intesiv);    $('#fCity').val(l.city);
        $('#fZoneIn').val(l.zone);    $('#fAddr').val(l.address);
        $('#fP9').val(l.program_9);   $('#fP10').val(l.program_10);
        $('#fP11').val(l.program_11); $('#fP12').val(l.program_12);
        $('#fStopx').prop('checked', +l.stopx === 1);
        $('#fCod').val(p ? p.code_din_brosura : 0);

        YEARS.forEach(y => {
          $('#fLoc' + y).val(lo ? lo['locuri_' + y] : 0);
          $('#fUM'  + y).val(m  ? m['u_medie_' + y] : '0.00');
          $('#fPM'  + y).val(m  ? m['p_medie_' + y] : '0.00');
          $('#fUP'  + y).val(p  ? p['u_pozition_' + y] : 0);
          $('#fNP'  + y).val(p  ? p['nr_place_' + y] : 0);
        });

        const lipsa = [];
        if (!lo) lipsa.push('locuri');
        if (!m)  lipsa.push('medie');
        if (!p)  lipsa.push('poziție');
        if (lipsa.length) {
          notify('Rânduri lipsă în: ' + lipsa.join(', ') + '. Vor fi create la salvare.', 'warning');
        }

        firstTab();
        modal.show();
      }).fail(() => notify('Nu s-au putut încărca datele.', 'danger'));
    });
  });
  /* ---------------- SALVEAZĂ ---------------- */
  $('#frm').on('submit', function (ev) {
    ev.preventDefault();
    if (!this.checkValidity()) { this.reportValidity(); return; }

    $('#btnSave').prop('disabled', true).addClass('loading');

    $.post(API, $(this).serialize(), null, 'json')
      .done(d => {
        if (d.ok) { modal.hide(); notify(d.msg); dt.ajax.reload(null, false); }
        else notify(d.msg, 'danger');
      })
      .fail(x => notify((x.responseJSON && x.responseJSON.msg) || 'Eroare la salvare.', 'danger'))
      .always(() => $('#btnSave').prop('disabled', false).removeClass('loading'));
  });

  /* ---------------- acțiuni rapide ---------------- */
  function post(data, after) {
    data.csrf = $('input[name=csrf]').first().val();
    $.post(API, data, null, 'json')
      .done(d => {
        notify(d.msg, d.ok ? 'success' : 'warning');
        dt.ajax.reload(null, false);
        if (after) after();
      })
      .fail(x => notify((x.responseJSON && x.responseJSON.msg) || 'Eroare.', 'danger'));
  }

  $('#tbl tbody').on('click', '.btnToggle', function () {
    post({ action: 'toggle_stop', id: $(this).data('id') });
  });

  $('#tbl tbody').on('click', '.btnDel', function () {
    const id  = $(this).data('id');
    const row = dt.rows().data().toArray().find(r => +r.id === +id);
    const nm  = row ? (row.name + ' – ' + row.specializare) : ('#' + id);
    if (!confirm('Ștergi „' + nm + '” din TOATE cele 4 tabele?\nAcțiunea nu poate fi anulată.')) return;
    post({ action: 'delete', id: id }, () => { selected.delete(String(id)); refreshSel(); });
  });

  $('#btnBulkDelete').on('click', function () {
    if (!selected.size) return;
    if (!confirm('Ștergi ' + selected.size + ' specializări din toate cele 4 tabele?')) return;
    post({ action: 'bulk_delete', ids: [...selected] }, () => {
      selected.clear(); $('#chkAll').prop('checked', false); refreshSel();
    });
  });

  /* ---------------- verificare integritate ---------------- */
  $('#btnIntegrity').on('click', function () {
    $('#intBody').html('Se verifică…');
    intModal.show();

    $.getJSON(API + '?action=integrity', d => {
      if (!d.ok) { $('#intBody').text(d.msg); return; }
      const r = d.raport;
      const ok = (r.liceu === r.locuri && r.liceu === r.medie && r.liceu === r.pozitie
                  && !r.fara_locuri && !r.fara_medie && !r.fara_pozitie && !r.nealiniate);

      const line = (label, val, bad) =>
        `<div style="display:flex;justify-content:space-between;padding:var(--space-sm) 0;
                     border-bottom:1px solid var(--color-border)">
           <span>${label}</span>
           <span class="status-badge ${bad ? 'danger' : 'success'}">${val}</span>
         </div>`;

      $('#intBody').html(
        `<div class="toast-msg ${ok ? 'success' : 'warning'}" style="box-shadow:none;margin-bottom:var(--space-md)">
           <span>${ok ? 'Toate cele 4 tabele sunt aliniate corect.'
                      : 'S-au găsit nepotriviri. Verifică rândurile marcate cu „!”.'}</span>
         </div>` +
        line('Rânduri în home_liceu',   r.liceu,   false) +
        line('Rânduri în home_locuri',  r.locuri,  r.locuri  !== r.liceu) +
        line('Rânduri în home_medie',   r.medie,   r.medie   !== r.liceu) +
        line('Rânduri în home_poztion', r.pozitie, r.pozitie !== r.liceu) +
        line('Fără rând în locuri',     r.fara_locuri,   r.fara_locuri > 0) +
        line('Fără rând în medie',      r.fara_medie,    r.fara_medie > 0) +
        line('Fără rând în poziție',    r.fara_pozitie,  r.fara_pozitie > 0) +
        line('Nume nealiniate',         r.nealiniate,    r.nealiniate > 0)
      );
    }).fail(() => $('#intBody').text('Eroare la verificare.'));
  });
});