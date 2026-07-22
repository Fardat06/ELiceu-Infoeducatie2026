/* admin/layout/js/settings_admin.js — setări site */
$(function () {

  const API = window.API || 'plugin/settings_api.php';
  let LOGO_URL = '../src/imges/';

  function notify(msg, type = 'success') {
    const id = 'a' + Date.now() + Math.random().toString(36).slice(2, 6);
    $('#alertBox').append(
      `<div id="${id}" class="toast-msg ${type}">
         <span>${msg}</span><button type="button" class="toast-close">&times;</button></div>`);
    const $el = $('#' + id);
    $el.on('click', '.toast-close', () => $el.remove());
    setTimeout(() => $el.fadeOut(200, function () { $(this).remove(); }), 5000);
  }

  const TEXT_KEYS = [
    'site_name', 'site_tagline', 'site_url', 'site_logo', 'site_favicon',
    'email_otp', 'email_confirm', 'email_list', 'email_contact', 'email_from_name',
    'smtp_host', 'smtp_port', 'smtp_user', 'smtp_secure'
  ];

  /* ---------------- încărcare ---------------- */
  function load() {
    $.getJSON(API + '?action=get', d => {
      if (!d.ok) return notify(d.msg || 'Eroare la citire.', 'danger');

      LOGO_URL = d.logo_url || LOGO_URL;
      const s = d.settings || {};

      TEXT_KEYS.forEach(k => { if (k in s) $('#' + k).val(s[k]); });
      $('#maintenance_mode').prop('checked', s.maintenance_mode === '1');

      showImage('#logoPreview', '#btnDelLogo', s.site_logo);
      showImage('#favPreview',  '#btnDelFav',  s.site_favicon);
    }).fail(() => notify('Nu s-au putut încărca setările.', 'danger'));
  }

  function showImage(imgSel, btnSel, file) {
    if (file) {
      $(imgSel).attr('src', LOGO_URL + encodeURIComponent(file) + '?t=' + Date.now())
               .prop('hidden', false);
      $(btnSel).prop('hidden', false);
    } else {
      $(imgSel).prop('hidden', true).attr('src', '');
      $(btnSel).prop('hidden', true);
    }
  }

  load();
  $('#btnReload').on('click', load);

  /* previzualizare la alegerea fișierului */
  $('#logo_file').on('change', function () {
    if (this.files && this.files[0]) {
      $('#logoPreview').attr('src', URL.createObjectURL(this.files[0])).prop('hidden', false);
    }
  });
  $('#favicon_file').on('change', function () {
    if (this.files && this.files[0]) {
      $('#favPreview').attr('src', URL.createObjectURL(this.files[0])).prop('hidden', false);
    }
  });

  /* ---------------- salvare ---------------- */
  $('#frmSet').on('submit', function (ev) {
    ev.preventDefault();
    if (!this.checkValidity()) { this.reportValidity(); return; }

    const fd = new FormData(this);
    $('#btnSave').prop('disabled', true).addClass('loading');

    $.ajax({ url: API, method: 'POST', data: fd, processData: false, contentType: false, dataType: 'json' })
      .done(d => {
        if (d.ok) {
          notify(d.msg);
          $('#logo_file, #favicon_file').val('');
          load();
        } else {
          notify(d.msg, 'danger');
        }
      })
      .fail(x => notify((x.responseJSON && x.responseJSON.msg) || 'Eroare la salvare.', 'danger'))
      .always(() => $('#btnSave').prop('disabled', false).removeClass('loading'));
  });

  /* ---------------- ștergere imagini ---------------- */
  function delImage(which) {
    if (!confirm('Ștergi această imagine?')) return;
    $.post(API, { action: 'delete_logo', which: which, csrf: $('input[name=csrf]').val() }, null, 'json')
      .done(d => { notify(d.msg, d.ok ? 'success' : 'warning'); load(); })
      .fail(() => notify('Eroare la ștergere.', 'danger'));
  }
  $('#btnDelLogo').on('click', () => delImage('logo'));
  $('#btnDelFav').on('click',  () => delImage('favicon'));

  /* ---------------- email de test ---------------- */
  $('#btnTestMail').on('click', function () {
    const sugestie = $('#email_contact').val() || '';
    const to = prompt('Trimite un email de test către:', sugestie);
    if (!to) return;

    $(this).prop('disabled', true);
    $.getJSON(API + '?action=test_email&to=' + encodeURIComponent(to), d => {
      notify(d.msg, d.ok ? 'success' : 'warning');
    }).fail(() => notify('Eroare la trimitere.', 'danger'))
      .always(() => $('#btnTestMail').prop('disabled', false));
  });
});