/* admin/layout/js/admitere_admin.js — CRUD home_admitere_2026 */
$(function () {

  const API = window.API || 'plugin/admitere_api.php';
  let year  = '2026';
  let rw    = true;

  const modalEl = document.getElementById('modalForm');
  const modal = {
    show: () => { modalEl.hidden = false; document.body.style.overflow = 'hidden'; },
    hide: () => { modalEl.hidden = true;  document.body.style.overflow = ''; }
  };

  $('#modalForm').on('click', function (ev) {
    if (ev.target === this || ev.target.closest('[data-close]')) modal.hide();
  });
  $(document).on('keydown', ev => {
    if (ev.key === 'Escape' && !modalEl.hidden) modal.hide();
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
    setTimeout(() => $el.fadeOut(200, function () { $(this).remove(); }), 5000);
  }

  function medieClass(v) {
    v = parseFloat(v) || 0;
    if (v >= 8) return 'success';
    if (v >= 6) return 'warning';
    return 'danger';
  }
  const num = v => (v === null || v === '' ? '–' : v);

  /* ---------------- DataTable ---------------- */
  const dt = $('#tbl').DataTable({
    ajax: {
      url: API,
      data: d => { d.action = 'list'; d.year = year; },
      dataSrc: function (json) {
        rw = json.rw !== false;
        $('#roBadge').prop('hidden', rw);
        $('#btnAdd').prop('disabled', !rw);
        $('#yearLabel').text(json.year || year);
        return json.data || [];
      }
    },
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100, 250],
    order: [[2, 'asc']],
    language: {
      search: 'Caută:', searchPlaceholder: 'școală, specializare, cod…',
      lengthMenu: 'Arată _MENU_ înregistrări',
      info: 'Afișate _START_–_END_ din _TOTAL_',
      infoEmpty: 'Nicio înregistrare', infoFiltered: '(filtrate din _MAX_)',
      zeroRecords: 'Niciun rezultat', emptyTable: 'Tabel gol',
      paginate: { first: '«', last: '»', next: '›', previous: '‹' }
    },
    columns: [
      { data: 'row_key', orderable: false, searchable: false,
        render: k => `<input type="checkbox" class="rowChk" value="${esc(k)}">` },

      { data: 'codificare',
        render: (c, t) => t !== 'display' ? c :
          `<span class="status-badge info">${esc(c)}</span>` },

      { data: 'nume_scoala',
        render: (n, t, r) => t !== 'display' ? n :
          `<div class="project-info">
             <div class="project-title-text">${esc(n)}</div>
             <div class="project-meta-text">${esc(r.tip_scoala || '')}</div>
           </div>` },

      { data: 'specializare',
        render: (s, t, r) => t !== 'display' ? s :
          `<div>${esc(s || '–')}</div>` +
          (r.mentiune ? `<div class="project-meta-text">${esc(r.mentiune)}</div>` : '') },

      { data: 'filiera' },
      { data: 'profil' },
      { data: 'clase', render: v => num(v) },
      { data: 'total_locuri', render: v => num(v) },

      { data: 'media_ultimului_admis',
        render: (v, t) => {
          if (t !== 'display') return parseFloat(v) || 0;
          if (v === null || v === '') return '<span class="muted">–</span>';
          return `<span class="status-badge ${medieClass(v)}">${parseFloat(v).toFixed(2)}</span>`;
        } },

      { data: 'row_key', orderable: false, searchable: false,
        render: k => `
          <div class="row-actions">
            <button class="icon-btn btnEdit" data-key="${esc(k)}" title="Vezi / Modifică">
              <span class="material-symbols-rounded">edit</span></button>
            <button class="icon-btn danger btnDel" data-key="${esc(k)}" title="Șterge">
              <span class="material-symbols-rounded">delete</span></button>
          </div>` }
    ],
    initComplete: function () { stats(this.api()); }
  });

  function stats(api) {
    const d = api.rows().data().toArray();
    $('#sTotal').text(d.length);
    $('#sLocuri').text(d.reduce((s, r) => s + (+r.total_locuri || 0), 0).toLocaleString('ro-RO'));
    $('#sScoli').text(new Set(d.map(r => r.nume_scoala)).size);

    const medii = d.map(r => parseFloat(r.media_ultimului_admis)).filter(v => v > 0);
    $('#sMedie').text(medii.length
      ? (medii.reduce((a, b) => a + b, 0) / medii.length).toFixed(2)
      : '–');
  }
  dt.on('xhr', () => setTimeout(() => stats(dt), 0));

  /* ---------------- schimbare an ---------------- */
  $('#fYear').on('change', function () {
    year = this.value;
    $('#fYearHidden').val(year);
    selected.clear();
    $('#chkAll').prop('checked', false);
    refreshSel();
    dt.ajax.reload();
    loadLookups();
  });

  /* ---------------- filtre ---------------- */
  $('#fScoala').on('change', function () {
    dt.column(2).search(this.value ? '^' + escRe(this.value) + '$' : '', true, false).draw();
  });
  $('#fProfil').on('change', function () {
    dt.column(5).search(this.value ? '^' + escRe(this.value) + '$' : '', true, false).draw();
  });
  $('#fFiliera').on('change', function () {
    dt.column(4).search(this.value ? '^' + escRe(this.value) + '$' : '', true, false).draw();
  });

  /* ---------------- lookups ---------------- */
  function loadLookups() {
    $.getJSON(API + '?action=lookups&year=' + year, d => {
      if (!d || !d.ok) return;

      $('#fScoala').find('option:gt(0)').remove();
      $('#fProfil').find('option:gt(0)').remove();
      $('#fFiliera').find('option:gt(0)').remove();
      $('#dlScoala, #dlTip, #dlFiliera, #dlProfil, #dlSpec').empty();

      d.nume_scoala.forEach(v => {
        $('#fScoala').append(new Option(v, v));
        $('#dlScoala').append(`<option value="${esc(v)}">`);
      });
      d.profil.forEach(v => {
        $('#fProfil').append(new Option(v, v));
        $('#dlProfil').append(`<option value="${esc(v)}">`);
      });
      d.filiera.forEach(v => {
        $('#fFiliera').append(new Option(v, v));
        $('#dlFiliera').append(`<option value="${esc(v)}">`);
      });
      d.tip_scoala.forEach(v => $('#dlTip').append(`<option value="${esc(v)}">`));
      d.specializare.forEach(v => $('#dlSpec').append(`<option value="${esc(v)}">`));
    });
  }
  loadLookups();

  /* ---------------- selecție ---------------- */
  function refreshSel() {
    $('#selCount').text(selected.size);
    $('#btnBulkDelete').prop('disabled', selected.size === 0 || !rw);
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
    if (!rw) return notify('Anul ' + year + ' este disponibil doar în citire.', 'warning');
    $('#frm')[0].reset();
    $('#fAction').val('create');
    $('#fId').val('');
    $('#fYearHidden').val(year);
    $('#modalTitle').text('Adaugă specializare');
    $('#btnSave').prop('disabled', false);
    firstTab();
    modal.show();
    setTimeout(() => $('#fCod').trigger('focus'), 50);
  });

  /* ---------------- MODIFICĂ ---------------- */
  $('#tbl tbody').on('click', '.btnEdit', function () {
    const key = $(this).data('key');
    $.getJSON(API + '?action=get&year=' + year + '&key=' + encodeURIComponent(key), d => {
      if (!d.ok) return notify(d.msg, 'danger');
      const r = d.row;

      $('#fAction').val('update');
      $('#fId').val(r.id || '');
      $('#fYearHidden').val(year);
      $('#modalTitle').text(rw ? 'Modifică: ' + r.codificare : 'Vizualizare: ' + r.codificare);

      $('#fCod').val(r.codificare);
      $('#fScoalaIn').val(r.nume_scoala);
      $('#fTip').val(r.tip_scoala);
      $('#fFil').val(r.filiera);
      $('#fProf').val(r.profil);
      $('#fSpec').val(r.specializare);
      $('#fSpecFull').val(r.specializare_complet);
      $('#fMentiune').val(r.mentiune);
      $('#fClase').val(r.clase);
      $('#fLocuri').val(r.total_locuri);
      $('#fRomi').val(r.locuri_romi);
      $('#fCes').val(r.locuri_ces);
      $('#fMedie').val(r.media_ultimului_admis);
      $('#fNr').val(r.nr);
      $('#fObs').val(r.observatii);

      // în modul doar-citire blocăm toate câmpurile
      $('#frm').find('input, textarea').prop('readonly', !rw);
      $('#btnSave').prop('disabled', !rw);

      firstTab();
      modal.show();
    }).fail(() => notify('Nu s-au putut încărca datele.', 'danger'));
  });

  /* ---------------- SALVEAZĂ ---------------- */
  $('#frm').on('submit', function (ev) {
    ev.preventDefault();
    if (!rw) return;
    if (!this.checkValidity()) { this.reportValidity(); return; }

    $('#btnSave').prop('disabled', true).addClass('loading');

    $.post(API, $(this).serialize(), null, 'json')
      .done(d => {
        if (d.ok) { modal.hide(); notify(d.msg); dt.ajax.reload(null, false); }
        else notify(d.msg, 'danger');
      })
      .fail(x => notify((x.responseJSON && x.responseJSON.msg) || 'Eroare la salvare.', 'danger'))
      .always(() => { $('#btnSave').prop('disabled', false).removeClass('loading'); });
  });

  /* ---------------- ȘTERGE ---------------- */
  function post(data, after) {
    data.csrf = $('input[name=csrf]').val();
    data.year = year;
    $.post(API, data, null, 'json')
      .done(d => {
        notify(d.msg, d.ok ? 'success' : 'warning');
        dt.ajax.reload(null, false);
        if (after) after();
      })
      .fail(x => notify((x.responseJSON && x.responseJSON.msg) || 'Eroare.', 'danger'));
  }

  $('#tbl tbody').on('click', '.btnDel', function () {
    if (!rw) return notify('Anul ' + year + ' este disponibil doar în citire.', 'warning');
    const key = $(this).data('key');
    if (!confirm('Ștergi înregistrarea cu codificarea „' + key + '”?')) return;
    post({ action: 'delete', id: key }, () => { selected.delete(String(key)); refreshSel(); });
  });

  $('#btnBulkDelete').on('click', function () {
    if (!selected.size || !rw) return;
    if (!confirm('Ștergi ' + selected.size + ' înregistrări selectate?')) return;
    post({ action: 'bulk_delete', ids: [...selected] }, () => {
      selected.clear();
      $('#chkAll').prop('checked', false);
      refreshSel();
    });
  });
});