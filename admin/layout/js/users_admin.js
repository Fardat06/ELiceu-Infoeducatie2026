/* admin/layout/js/users_admin.js — CRUD home_users */
$(function () {

  const API = window.API || 'plugin/users_api.php';
  let me = 0;   // UserID-ul contului curent

  const modalEl = document.getElementById('modalForm');
  const passEl  = document.getElementById('modalPass');

  const modal = {
    show: () => { modalEl.hidden = false; document.body.style.overflow = 'hidden'; },
    hide: () => { modalEl.hidden = true;  document.body.style.overflow = ''; }
  };
  const passModal = {
    show: () => { passEl.hidden = false; document.body.style.overflow = 'hidden'; },
    hide: () => { passEl.hidden = true;  document.body.style.overflow = ''; }
  };

  $('#modalForm').on('click', function (ev) {
    if (ev.target === this || ev.target.closest('[data-close]')) modal.hide();
  });
  $('#modalPass').on('click', function (ev) {
    if (ev.target === this || ev.target.closest('[data-close-pass]')) passModal.hide();
  });
  $(document).on('keydown', ev => {
    if (ev.key !== 'Escape') return;
    if (!passEl.hidden) passModal.hide();
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
    setTimeout(() => $el.fadeOut(200, function () { $(this).remove(); }), 5000);
  }

  const fmtDate = s => s ? new Date(s.replace(' ', 'T')).toLocaleDateString('ro-RO') : '–';

  /* ---------------- DataTable ---------------- */
  const dt = $('#tbl').DataTable({
    ajax: {
      url: API + '?action=list',
      dataSrc: function (json) { me = +json.me || 0; return json.data || []; }
    },
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100],
    order: [[2, 'asc']],
    language: {
      search: 'Caută:', searchPlaceholder: 'nume, email…',
      lengthMenu: 'Arată _MENU_ înregistrări',
      info: 'Afișate _START_–_END_ din _TOTAL_',
      infoEmpty: 'Nicio înregistrare', infoFiltered: '(filtrate din _MAX_)',
      zeroRecords: 'Niciun rezultat', emptyTable: 'Niciun cont definit',
      paginate: { first: '«', last: '»', next: '›', previous: '‹' }
    },
    columns: [
      { data: 'UserID', orderable: false, searchable: false,
        render: (id) => +id === me
          ? '<input type="checkbox" disabled title="Propriul cont">'
          : `<input type="checkbox" class="rowChk" value="${id}">` },

      { data: 'UserID', width: '60px' },

      { data: 'UserName',
        render: (u, t, r) => t !== 'display' ? u :
          `<div class="project-info">
             <div class="project-title-text">${esc(u)}
               ${+r.UserID === me ? '<span class="status-badge info">tu</span>' : ''}</div>
             <div class="project-meta-text">${esc(r.FullName || '')}</div>
           </div>` },

      { data: 'Email' },

      { data: 'GroupName',
        render: (g, t, r) => t !== 'display' ? g :
          (+r.GroupID >= 2
            ? `<span class="status-badge success">${esc(g)}</span>`
            : `<span class="status-badge warning">${esc(g)}</span>`) },

      { data: 'Language' },

      { data: 'stopx',
        render: (v, t) => {
          const label = (+v === 1) ? 'Blocat' : 'Activ';
          if (t !== 'display') return label;
          return (+v === 1)
            ? '<span class="status-badge danger">Blocat</span>'
            : '<span class="status-badge success">Activ</span>';
        } },

      { data: 'created_at', render: (v, t) => t !== 'display' ? v : fmtDate(v) },

      { data: 'UserID', orderable: false, searchable: false,
        render: id => `
          <div class="row-actions">
            <button class="icon-btn btnEdit" data-id="${id}" title="Modifică">
              <span class="material-symbols-rounded">edit</span></button>
            <button class="icon-btn btnPass" data-id="${id}" title="Resetează parola">
              <span class="material-symbols-rounded">key</span></button>
            ${+id === me ? '' : `
            <button class="icon-btn btnToggle" data-id="${id}" title="Activează / Dezactivează">
              <span class="material-symbols-rounded">block</span></button>
            <button class="icon-btn danger btnDel" data-id="${id}" title="Șterge">
              <span class="material-symbols-rounded">delete</span></button>`}
          </div>` }
    ],
    initComplete: function () { stats(this.api()); }
  });

  function stats(api) {
    const d = api.rows().data().toArray();
    $('#sTotal').text(d.length);
    $('#sActive').text(d.filter(r => +r.stopx === 0).length);
    $('#sBlocked').text(d.filter(r => +r.stopx === 1).length);
    $('#sAdmins').text(d.filter(r => +r.GroupID >= 2).length);
  }
  dt.on('xhr', () => setTimeout(() => stats(dt), 0));

  /* ---------------- filtre ---------------- */
  $('#fGroup').on('change', function () {
    dt.column(4).search(this.value ? '^' + escRe(this.value) + '$' : '', true, false).draw();
  });
  $('#fStop').on('change', function () {
    dt.column(6).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
  });

  /* ---------------- lookups ---------------- */
  $.getJSON(API + '?action=lookups', d => {
    if (!d || !d.ok) return;
    d.groups.forEach(g => {
      $('#fGroup').append(new Option(g.name, g.name));
      $('#fGroupSel').append(new Option(g.name, g.id));
    });
    d.langs.forEach(l => $('#fLang').append(new Option(l, l)));
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
    $('#modalTitle').text('Adaugă utilizator');
    $('#fPass').prop('required', true);
    $('#passReq').prop('hidden', false);
    $('#passHint').text('Minimum 8 caractere.');
    $('#selfWarn').prop('hidden', true);
    $('#fGroupSel, #fStopx, #fReg').prop('disabled', false);
    $('#fLang').val('ro');
    $('#fReg').val('1');
    firstTab();
    modal.show();
    setTimeout(() => $('#fUser').trigger('focus'), 50);
  });

  /* ---------------- MODIFICĂ ---------------- */
  $('#tbl tbody').on('click', '.btnEdit', function () {
    const id = $(this).data('id');
    $.getJSON(API + '?action=get&id=' + encodeURIComponent(id), d => {
      if (!d.ok) return notify(d.msg, 'danger');
      const r = d.row;
      const isSelf = +r.UserID === me;

      $('#fAction').val('update');
      $('#fId').val(r.UserID);
      $('#modalTitle').text('Modifică: ' + r.UserName);

      $('#fUser').val(r.UserName);
      $('#fEmail').val(r.Email);
      $('#fFull').val(r.FullName);
      $('#fFirst').val(r.first_name);
      $('#fLast').val(r.last_name);
      $('#fGroupSel').val(r.GroupID);
      $('#fLang').val(r.Language);
      $('#fReg').val(r.RegStatus === null ? '1' : String(r.RegStatus));
      $('#fTrust').val(r.TrustStatus || 0);
      $('#fStopx').prop('checked', +r.stopx === 1);

      $('#fPass').val('').prop('required', false);
      $('#passReq').prop('hidden', true);
      $('#passHint').text('Lasă gol pentru a păstra parola actuală.');

      $('#selfWarn').prop('hidden', !isSelf);
      $('#fGroupSel, #fStopx, #fReg').prop('disabled', isSelf);

      firstTab();
      modal.show();
    }).fail(() => notify('Nu s-au putut încărca datele.', 'danger'));
  });

  /* ---------------- SALVEAZĂ ---------------- */
  $('#frm').on('submit', function (ev) {
    ev.preventDefault();
    if (!this.checkValidity()) { this.reportValidity(); return; }

    $('#btnSave').prop('disabled', true).addClass('loading');

    // câmpurile disabled nu se trimit → le reactivăm temporar
    const $off = $('#frm').find(':disabled').prop('disabled', false);

    $.post(API, $(this).serialize(), null, 'json')
      .done(d => {
        if (d.ok) { modal.hide(); notify(d.msg); dt.ajax.reload(null, false); }
        else notify(d.msg, 'danger');
      })
      .fail(x => notify((x.responseJSON && x.responseJSON.msg) || 'Eroare la salvare.', 'danger'))
      .always(() => {
        $off.prop('disabled', true);
        $('#btnSave').prop('disabled', false).removeClass('loading');
      });
  });

  /* ---------------- RESETARE PAROLĂ ---------------- */
  $('#tbl tbody').on('click', '.btnPass', function () {
    const id  = $(this).data('id');
    const row = dt.rows().data().toArray().find(r => +r.UserID === +id);
    $('#pId').val(id);
    $('#pUser').val(row ? row.UserName : '#' + id);
    $('#pPass').val('');
    passModal.show();
    setTimeout(() => $('#pPass').trigger('focus'), 50);
  });

  $('#frmPass').on('submit', function (ev) {
    ev.preventDefault();
    if (!this.checkValidity()) { this.reportValidity(); return; }

    $('#btnPassSave').prop('disabled', true).addClass('loading');
    $.post(API, $(this).serialize(), null, 'json')
      .done(d => {
        if (d.ok) { passModal.hide(); notify(d.msg); }
        else notify(d.msg, 'danger');
      })
      .fail(x => notify((x.responseJSON && x.responseJSON.msg) || 'Eroare.', 'danger'))
      .always(() => $('#btnPassSave').prop('disabled', false).removeClass('loading'));
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
    post({ action: 'toggle_stop', UserID: $(this).data('id') });
  });

  $('#tbl tbody').on('click', '.btnDel', function () {
    const id  = $(this).data('id');
    const row = dt.rows().data().toArray().find(r => +r.UserID === +id);
    const nm  = row ? row.UserName : '#' + id;
    if (!confirm('Ștergi definitiv contul „' + nm + '”?\nAcțiunea nu poate fi anulată.')) return;
    post({ action: 'delete', UserID: id }, () => { selected.delete(String(id)); refreshSel(); });
  });

  $('#btnBulkDelete').on('click', function () {
    if (!selected.size) return;
    if (!confirm('Ștergi ' + selected.size + ' cont(uri) selectate?')) return;
    post({ action: 'bulk_delete', ids: [...selected] }, () => {
      selected.clear();
      $('#chkAll').prop('checked', false);
      refreshSel();
    });
  });
});