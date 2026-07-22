$(function () {

  const API      = window.API      || '';
  const ID_FIELD = window.ID_FIELD || 'id';
  const LABEL    = window.LABEL    || 'element';

  if (!API) { console.error('window.API nu este definit.'); return; }

  const modalEl = document.getElementById('modalForm');

  const modal = {
    show: () => { modalEl.hidden = false; document.body.style.overflow = 'hidden'; },
    hide: () => { modalEl.hidden = true;  document.body.style.overflow = ''; }
  };

  $('#modalForm').on('click', function (ev) {
    if (ev.target.closest('[data-close]')) modal.hide();
  });

  $(document).on('keydown', function (ev) {
    if (ev.key === 'Escape' && !modalEl.hidden) modal.hide();
  });

  let selected = new Set();

  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, c =>
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
      /* selecție — rândurile rezervate nu pot fi selectate */
      { data: ID_FIELD, orderable: false, searchable: false,
        render: (id, t, r) => r.rezervat
          ? '<input type="checkbox" disabled title="Valoare rezervată de sistem">'
          : `<input type="checkbox" class="rowChk" value="${id}">` },

      { data: ID_FIELD, width: '60px' },

      { data: 'description',
        render: (d, t) => {
          if (t !== 'display') return d;
          return (String(d ?? '').trim() === '')
            ? '<span class="status-badge danger">(gol)</span>'
            : `<div class="project-title-text">${esc(d)}</div>`;
        } },

      { data: 'nr_uz',
        render: (n, t, r) => {
          n = +n || 0;
          if (t !== 'display') return n;
          if (r.rezervat) return '<span class="status-badge info">rezervat</span>';
          return n > 0
            ? `<span class="status-badge success">${n}</span>`
            : '<span class="status-badge warning">nefolosit</span>';
        } },

      { data: null, orderable: false, searchable: false,
        render: r => r.rezervat
          ? `<div class="row-actions">
               <span class="status-badge info" title="Valoare rezervată de sistem">blocat</span>
             </div>`
          : `<div class="row-actions">
               <button class="icon-btn btnEdit" data-id="${r[ID_FIELD]}" title="Modifică">
                 <span class="material-symbols-rounded">edit</span></button>
               <button class="icon-btn danger btnDel"
                       data-id="${r[ID_FIELD]}"
                       data-desc="${esc(r.description)}"
                       data-nr="${r.nr_uz}"
                       title="Șterge">
                 <span class="material-symbols-rounded">delete</span></button>
             </div>` }
    ],
    initComplete: function () { stats(this.api()); }
  });

  function stats(api) {
    const d = api.rows().data().toArray();
    $('#sTotal').text(d.length);
    $('#sUsed').text(d.filter(r => +r.nr_uz > 0).length);
    $('#sUnused').text(d.filter(r => +r.nr_uz === 0 && !r.rezervat).length);
    $('#sRefs').text(d.reduce((s, r) => s + (+r.nr_uz || 0), 0).toLocaleString('ro-RO'));
  }
  dt.on('xhr', () => setTimeout(() => stats(dt), 0));

  $.fn.dataTable.ext.search.push(function (settings, data, idx) {
    if (settings.nTable.id !== 'tbl') return true;
    const v = $('#fUsed').val();
    if (!v) return true;
    const nr = +dt.row(idx).data().nr_uz || 0;
    return v === 'used' ? nr > 0 : nr === 0;
  });
  $('#fUsed').on('change', () => dt.draw());


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

  $('#btnAdd').on('click', function () {
    $('#frm')[0].reset();
    $('#fAction').val('create');
    $('#fId').val('');
    $('#modalTitle').text('Adaugă ' + LABEL);
    $('#usageBox').prop('hidden', true);
    modal.show();
    setTimeout(() => $('#fDesc').trigger('focus'), 50);
  });

  $('#tbl tbody').on('click', '.btnEdit', function () {
    const id = $(this).data('id');

    $.getJSON(API + '?action=get&id=' + encodeURIComponent(id), d => {
      if (!d.ok) return notify(d.msg, 'danger');

      $('#fAction').val('update');
      $('#fId').val(d.row[ID_FIELD]);
      $('#fDesc').val(d.row.description);
      $('#modalTitle').text('Modifică ' + LABEL + ' #' + d.row[ID_FIELD]);

      const $box = $('#usageBox');
      if ($box.length) {
        const u    = d.row.usage || {};
        const keys = Object.keys(u);
        if (keys.length) {
          $('#usageList').html(
            keys.map(k =>
              `<span class="status-badge success" style="margin-right:.35rem;margin-bottom:.35rem;display:inline-block">
                 ${esc(k)}: ${u[k]}
               </span>`).join('')
          );
          $box.prop('hidden', false);
        } else {
          $box.prop('hidden', true);
        }
      }

      modal.show();
      setTimeout(() => $('#fDesc').trigger('focus'), 50);
    }).fail(() => notify('Nu s-au putut încărca datele.', 'danger'));
  });

  /* ---------------- SALVEAZĂ ---------------- */
  $('#frm').on('submit', function (ev) {
    ev.preventDefault();
    if (!this.checkValidity()) { this.reportValidity(); return; }

    /* avertisment la redenumire — propagarea atinge mai multe tabele */
    if ($('#fAction').val() === 'update') {
      const id  = $('#fId').val();
      const row = dt.rows().data().toArray().find(r => String(r[ID_FIELD]) === String(id));
      const old = row ? row.description : '';
      const nou = $('#fDesc').val().trim();

      if (old && nou !== old) {
        if (!confirm('Redenumești „' + old + '” în „' + nou + '”.\n\n' +
                     'Valoarea va fi actualizată automat în toate înregistrările legate. Continui?')) {
          return;
        }
      }
    }

    $('#btnSave').prop('disabled', true).addClass('loading');

    $.post(API, $(this).serialize(), null, 'json')
      .done(d => {
        if (d.ok) { modal.hide(); notify(d.msg); dt.ajax.reload(null, false); }
        else notify(d.msg, 'danger');
      })
      .fail(x => notify((x.responseJSON && x.responseJSON.msg) || 'Eroare la salvare.', 'danger'))
      .always(() => { $('#btnSave').prop('disabled', false).removeClass('loading'); });
  });

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

  $('#tbl tbody').on('click', '.btnDel', function () {
    const id   = $(this).data('id');
    const desc = $(this).data('desc');
    const nr   = +$(this).data('nr') || 0;

    if (nr > 0) {
      notify('Nu se poate șterge „' + desc + '”: ' + nr + ' înregistrare(i) o folosesc.', 'warning');
      return;
    }
    if (!confirm('Ștergi „' + desc + '”?')) return;

    const payload = { action: 'delete' };
    payload[ID_FIELD] = id;
    post(payload, () => { selected.delete(String(id)); refreshSel(); });
  });

  $('#btnBulkDelete').on('click', function () {
    if (!selected.size) return;
    if (!confirm('Ștergi ' + selected.size + ' înregistrări selectate?\n' +
                 'Cele în uz sau rezervate vor fi ignorate.')) return;

    post({ action: 'bulk_delete', ids: [...selected] }, () => {
      selected.clear();
      $('#chkAll').prop('checked', false);
      refreshSel();
    });
  });
});
