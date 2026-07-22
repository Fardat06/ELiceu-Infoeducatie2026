/* admin/layout/js/tip_admin.js — CRUD home_tip_liceu */
$(function () {

  const API = (typeof window.API !== 'undefined') ? window.API : 'plugin/tip_api.php';
  const modalEl = document.getElementById('modalForm');

  const modal = {
    show: () => { modalEl.hidden = false; document.body.style.overflow = 'hidden'; },
    hide: () => { modalEl.hidden = true;  document.body.style.overflow = ''; }
  };

  $('#modalForm').on('click', function (ev) {
    if (ev.target === this || ev.target.closest('[data-close]')) modal.hide();
  });
  $(document).on('keydown', function (ev) {
    if (ev.key === 'Escape' && !modalEl.hidden) modal.hide();
  });

  let selected = new Set();

  function esc(s) {
    return String(s).replace(/[&<>"']/g, c =>
      ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function notify(msg, type = 'success') {
    const id = 'a' + Date.now() + Math.random().toString(36).slice(2, 6);
    $('#alertBox').append(
      `<div id="${id}" class="toast-msg ${type}">
         <span>${msg}</span>
         <button type="button" class="toast-close">&times;</button>
       </div>`);
    const $el = $('#' + id);
    $el.on('click', '.toast-close', () => $el.remove());
    setTimeout(() => $el.fadeOut(200, function () { $(this).remove(); }), 5000);
  }

  /* ---------------- DataTable ---------------- */
  const dt = $('#tbl').DataTable({
    ajax: { url: API + '?action=list', dataSrc: 'data' },
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100],
    order: [[2, 'asc']],
    language: {
      search: 'Caută:', searchPlaceholder: 'denumire…',
      lengthMenu: 'Arată _MENU_ înregistrări',
      info: 'Afișate _START_–_END_ din _TOTAL_',
      infoEmpty: 'Nicio înregistrare', infoFiltered: '(filtrate din _MAX_)',
      zeroRecords: 'Niciun rezultat', emptyTable: 'Tabel gol',
      paginate: { first: '«', last: '»', next: '›', previous: '‹' }
    },
    columns: [
      { data: 'id_tip_liceu', orderable: false, searchable: false,
        render: id => `<input type="checkbox" class="rowChk" value="${id}">` },

      { data: 'id_tip_liceu', width: '60px' },

      { data: 'description',
        render: (d, t) => t !== 'display' ? d :
          `<div class="project-title-text">${esc(d)}</div>` },

      { data: 'nr_licee',
        render: (n, t) => {
          n = +n || 0;
          if (t !== 'display') return n;
          return n > 0
            ? `<span class="status-badge success">${n} licee</span>`
            : '<span class="status-badge warning">nefolosit</span>';
        } },

      { data: null, orderable: false, searchable: false,
        render: r => `
          <div class="row-actions">
            <button class="icon-btn btnEdit" data-id="${r.id_tip_liceu}" title="Modifică">
              <span class="material-symbols-rounded">edit</span></button>
            <button class="icon-btn danger btnDel"
                    data-id="${r.id_tip_liceu}" data-desc="${esc(r.description)}"
                    data-nr="${r.nr_licee}" title="Șterge">
              <span class="material-symbols-rounded">delete</span></button>
          </div>` }
    ],
    initComplete: function () { stats(this.api()); }
  });

  function stats(api) {
    const d = api.rows().data().toArray();
    $('#sTotal').text(d.length);
    $('#sUsed').text(d.filter(r => +r.nr_licee > 0).length);
    $('#sUnused').text(d.filter(r => +r.nr_licee === 0).length);
    $('#sLicee').text(d.reduce((s, r) => s + (+r.nr_licee || 0), 0));
  }
  dt.on('xhr', () => setTimeout(() => stats(dt), 0));

  /* filtru folosit / nefolosit */
  $.fn.dataTable.ext.search.push(function (settings, data, idx) {
    const v = $('#fUsed').val();
    if (!v) return true;
    const nr = +dt.row(idx).data().nr_licee || 0;
    return v === 'used' ? nr > 0 : nr === 0;
  });
  $('#fUsed').on('change', () => dt.draw());

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
    $('#modalTitle').text('Adaugă tip de liceu');
    modal.show();
    setTimeout(() => $('#fDesc').trigger('focus'), 50);
  });

  /* ---------------- MODIFICĂ ---------------- */
  $('#tbl tbody').on('click', '.btnEdit', function () {
    const id = $(this).data('id');
    $.getJSON(API + '?action=get&id=' + encodeURIComponent(id), d => {
      if (!d.ok) return notify(d.msg, 'danger');
      $('#fAction').val('update');
      $('#fId').val(d.row.id_tip_liceu);
      $('#fDesc').val(d.row.description);
      $('#modalTitle').text('Modifică tip #' + d.row.id_tip_liceu);
      modal.show();
      setTimeout(() => $('#fDesc').trigger('focus'), 50);
    }).fail(() => notify('Nu s-au putut încărca datele.', 'danger'));
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
      .always(() => { $('#btnSave').prop('disabled', false).removeClass('loading'); });
  });

  /* ---------------- ȘTERGE ---------------- */
  function post(data, after) {
    data.csrf = $('input[name=csrf]').val();
    $.post(API, data, null, 'json')
      .done(d => {
        notify(d.msg, d.ok ? 'success' : 'warning');
        dt.ajax.reload(null, false);
        if (after) after();
      })
      .fail(x => notify((x.responseJSON && x.responseJSON.msg) || 'Eroare.', 'danger'));
  }

  $('#tbl tbody').on('click', '.btnDel', function () {
    const id   = $(this).data('id');
    const desc = $(this).data('desc');
    const nr   = +$(this).data('nr') || 0;

    if (nr > 0) {
      notify('Nu se poate șterge „' + desc + '”: ' + nr + ' liceu(e) îl folosesc.', 'warning');
      return;
    }
    if (!confirm('Ștergi tipul „' + desc + '”?')) return;
    post({ action: 'delete', id_tip_liceu: id }, () => { selected.delete(String(id)); refreshSel(); });
  });

  $('#btnBulkDelete').on('click', function () {
    if (!selected.size) return;
    if (!confirm('Ștergi ' + selected.size + ' tip(uri) selectate?\nCele folosite vor fi ignorate.')) return;
    post({ action: 'bulk_delete', ids: [...selected] }, () => {
      selected.clear();
      $('#chkAll').prop('checked', false);
      refreshSel();
    });
  });
});