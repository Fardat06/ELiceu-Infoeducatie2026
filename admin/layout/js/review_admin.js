/* admin/layout/js/review_admin.js — moderare recenzii */
$(function () {

  const API = window.API || 'plugin/review_api.php';
  let current = null;   // recenzia deschisă în modal

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

  const stars = n => '★'.repeat(Math.max(0, Math.min(5, +n || 0))) +
                     '☆'.repeat(5 - Math.max(0, Math.min(5, +n || 0)));

  const fmtDate = s => s
    ? new Date(String(s).replace(' ', 'T')).toLocaleString('ro-RO',
        { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
    : '–';

  const trunc = (s, n = 90) => {
    s = String(s ?? '');
    return s.length > n ? esc(s.slice(0, n)) + '…' : esc(s);
  };

  /* ---------------- DataTable ---------------- */
  const dt = $('#tbl').DataTable({
    ajax: { url: API + '?action=list', dataSrc: 'data' },
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100],
    order: [],   // ordinea vine din SQL: în așteptare mai întâi
    language: {
      search: 'Caută:', searchPlaceholder: 'liceu, autor, comentariu…',
      lengthMenu: 'Arată _MENU_ înregistrări',
      info: 'Afișate _START_–_END_ din _TOTAL_',
      infoEmpty: 'Nicio recenzie', infoFiltered: '(filtrate din _MAX_)',
      zeroRecords: 'Niciun rezultat', emptyTable: 'Nicio recenzie primită',
      paginate: { first: '«', last: '»', next: '›', previous: '‹' }
    },
    columns: [
      { data: 'id', orderable: false, searchable: false,
        render: id => `<input type="checkbox" class="rowChk" value="${id}">` },

      { data: 'liceu',
        render: (l, t) => t !== 'display' ? (l || '') :
          `<div class="project-title-text">${esc(l || '(liceu șters)')}</div>` },

      { data: 'autor',
        render: (a, t, r) => {
          const nume = a || ('user #' + r.user_id);
          if (t !== 'display') return nume;
          const susp = (+r.autor_activ === 0)
            ? ' <span class="status-badge danger">suspendat</span>' : '';
          return `<div class="project-info">
                    <div class="project-title-text">${esc(nume)}${susp}</div>
                    <div class="project-meta-text">${r.autor_recenzii || 0} recenzii</div>
                  </div>`;
        } },

      { data: 'rating',
        render: (v, t) => t !== 'display' ? (+v || 0) :
          `<span title="${+v}/5" style="color:var(--color-warning);white-space:nowrap">${stars(v)}</span>` },

      { data: 'comment', render: (c, t) => t !== 'display' ? c : trunc(c) },

      { data: 'created_at', render: (v, t) => t !== 'display' ? v : fmtDate(v) },

      { data: 'is_active',
        render: (v, t) => {
          const label = (+v === 1) ? 'Publicat' : 'Așteptare';
          if (t !== 'display') return label;
          return (+v === 1)
            ? '<span class="status-badge success">Publicat</span>'
            : '<span class="status-badge warning">Așteptare</span>';
        } },

      { data: null, orderable: false, searchable: false,
        render: r => `
          <div class="row-actions">
            <button class="icon-btn btnView" data-id="${r.id}" title="Vezi recenzia">
              <span class="material-symbols-rounded">visibility</span></button>
            ${+r.is_active === 1
              ? `<button class="icon-btn btnUnpub" data-id="${r.id}" title="Retrage de pe site">
                   <span class="material-symbols-rounded">unpublished</span></button>`
              : `<button class="icon-btn btnPub" data-id="${r.id}" title="Publică">
                   <span class="material-symbols-rounded">check_circle</span></button>`}
            <button class="icon-btn danger btnDel" data-id="${r.id}" title="Șterge">
              <span class="material-symbols-rounded">delete</span></button>
          </div>` }
    ],
    initComplete: function () {
      stats(this.api());
      $('#fStatus').val('Așteptare').trigger('change');   // implicit: coada de moderare
    }
  });

  function stats(api) {
    const d = api.rows().data().toArray();
    $('#sPending').text(d.filter(r => +r.is_active === 0).length);
    $('#sPublished').text(d.filter(r => +r.is_active === 1).length);

    const rt = d.map(r => +r.rating).filter(v => v > 0);
    $('#sRating').text(rt.length ? (rt.reduce((a, b) => a + b, 0) / rt.length).toFixed(2) : '–');

    const susp = new Set(d.filter(r => +r.autor_activ === 0).map(r => r.user_id));
    $('#sSuspended').text(susp.size);
  }
  dt.on('xhr', () => setTimeout(() => stats(dt), 0));

  /* ---------------- filtre ---------------- */
  $('#fStatus').on('change', function () {
    dt.column(6).search(this.value ? '^' + escRe(this.value) + '$' : '', true, false).draw();
  });
  $('#fLiceu').on('change', function () {
    dt.column(1).search(this.value ? '^' + escRe(this.value) + '$' : '', true, false).draw();
  });
  $('#fRating').on('change', function () {
    dt.column(3).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
  });

  $.getJSON(API + '?action=lookups', d => {
    if (!d || !d.ok) return;
    d.licee.forEach(l => $('#fLiceu').append(new Option(l, l)));
  });

  /* ---------------- selecție ---------------- */
  function refreshSel() {
    $('#selCount').text(selected.size);
    const off = selected.size === 0;
    $('#btnBulkPublish, #btnBulkUnpublish, #btnBulkDelete').prop('disabled', off);
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

  /* ---------------- acțiuni ---------------- */
  function post(data, after) {
    data.csrf = $('input[name=csrf]').first().val();
    $.post(API, data, null, 'json')
      .done(d => {
        notify(d.msg, d.ok ? 'success' : 'warning');
        dt.ajax.reload(null, false);
        if (after) after(d);
      })
      .fail(x => notify((x.responseJSON && x.responseJSON.msg) || 'Eroare.', 'danger'));
  }

  $('#tbl tbody').on('click', '.btnPub',   function () { post({ action: 'publish',   id: $(this).data('id') }); });
  $('#tbl tbody').on('click', '.btnUnpub', function () { post({ action: 'unpublish', id: $(this).data('id') }); });

  $('#tbl tbody').on('click', '.btnDel', function () {
    const id = $(this).data('id');
    if (!confirm('Ștergi definitiv această recenzie?')) return;
    post({ action: 'delete', id: id }, () => { selected.delete(String(id)); refreshSel(); });
  });

  $('#btnBulkPublish').on('click', function () {
    if (!selected.size) return;
    post({ action: 'bulk_publish', ids: [...selected] }, clearSel);
  });
  $('#btnBulkUnpublish').on('click', function () {
    if (!selected.size) return;
    post({ action: 'bulk_unpublish', ids: [...selected] }, clearSel);
  });
  $('#btnBulkDelete').on('click', function () {
    if (!selected.size) return;
    if (!confirm('Ștergi definitiv ' + selected.size + ' recenzii?')) return;
    post({ action: 'bulk_delete', ids: [...selected] }, clearSel);
  });
  function clearSel() {
    selected.clear();
    $('#chkAll').prop('checked', false);
    refreshSel();
  }

  /* ---------------- modal: vezi recenzia ---------------- */
  $('#tbl tbody').on('click', '.btnView', function () {
    const id = $(this).data('id');
    $.getJSON(API + '?action=get&id=' + encodeURIComponent(id), d => {
      if (!d.ok) return notify(d.msg, 'danger');
      current = d.row;

      $('#modalTitle').text('Recenzie #' + current.id);
      $('#vLiceu').text(current.liceu || '(liceu șters)');
      $('#vAutor').html(esc(current.autor || ('user #' + current.user_id)) +
        (+current.autor_activ === 0 ? ' <span class="status-badge danger">suspendat</span>' : ''));
      $('#vEmail').text(current.autor_email || '');
      $('#vRating').html(
        `<span style="color:var(--color-warning);font-size:var(--text-xl)">${stars(current.rating)}</span>
         <span class="project-meta-text"> ${current.rating}/5</span>`);
      $('#vData').text(fmtDate(current.created_at));
      $('#vComment').text(current.comment || '');

      // alte recenzii ale autorului
      const alt = current.altele || [];
      if (alt.length) {
        $('#vAltele').html(alt.map(a => `
          <div style="padding:var(--space-sm) 0;border-bottom:1px solid var(--color-border)">
            <div style="display:flex;justify-content:space-between;gap:var(--space-sm)">
              <strong>${esc(a.liceu || '–')}</strong>
              <span style="color:var(--color-warning)">${stars(a.rating)}</span>
            </div>
            <div class="project-meta-text">${trunc(a.comment, 120)}</div>
          </div>`).join(''));
        $('#altBox').prop('hidden', false);
      } else {
        $('#altBox').prop('hidden', true);
      }

      const publicat = +current.is_active === 1;
      $('#btnPublish').prop('hidden', publicat);
      $('#btnUnpublish').prop('hidden', !publicat);

      const suspendat = +current.autor_activ === 0;
      $('#btnSuspend').prop('hidden', suspendat);
      $('#btnReactivate').prop('hidden', !suspendat);

      modal.show();
    }).fail(() => notify('Nu s-a putut încărca recenzia.', 'danger'));
  });

  $('#btnPublish').on('click', function () {
    if (!current) return;
    post({ action: 'publish', id: current.id }, () => modal.hide());
  });
  $('#btnUnpublish').on('click', function () {
    if (!current) return;
    post({ action: 'unpublish', id: current.id }, () => modal.hide());
  });

  /* ---------------- suspendare autor ---------------- */
  $('#btnSuspend').on('click', function () {
    if (!current) return;
    const nume = current.autor || ('user #' + current.user_id);
    if (!confirm('Suspenzi contul „' + nume + '”?\nNu se va mai putea autentifica.')) return;

    const ascunde = confirm('Retragi și toate recenziile acestui autor de pe site?\n' +
                            'OK = da, Anulează = păstrează recenziile publicate.');

    post({ action: 'suspend_user', user_id: current.user_id, hide_reviews: ascunde ? 1 : 0 },
         () => modal.hide());
  });

  $('#btnReactivate').on('click', function () {
    if (!current) return;
    const nume = current.autor || ('user #' + current.user_id);
    if (!confirm('Reactivezi contul „' + nume + '”?')) return;
    post({ action: 'reactivate_user', user_id: current.user_id }, () => modal.hide());
  });
});