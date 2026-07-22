<?php
ob_start();
require_once __DIR__ . '/plugin/admin_init.php';
ob_clean();

if (!function_exists('csrf_token')) {
    die('admin_init.php nu a fost încărcat.');
}

if (!isset($_SESSION['username-x'])) {
    header('Location: index.php');
    exit();
}

$csrf      = csrf_token();
$pageTitle = 'Administrare licee';

include __DIR__ . '/template/header.php';
?>
<div class="dashboard-container">
  <?php include __DIR__ . '/template/sidebar.php'; ?>
  <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>

  <main class="dashboard-main">
    <?php include __DIR__ . '/template/header_main.php'; ?>

    <div class="dashboard-content">
      <div class="dashboard-view active" id="licee">

        <div id="alertBox"></div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Licee totale</div>
              <div class="stat-card-icon primary">
                <span class="material-symbols-rounded">school</span>
              </div>
            </div>
            <div class="stat-card-value" id="sTotal">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Active pe site</div>
              <div class="stat-card-icon success">
                <span class="material-symbols-rounded">visibility</span>
              </div>
            </div>
            <div class="stat-card-value" id="sActive">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Ascunse</div>
              <div class="stat-card-icon warning">
                <span class="material-symbols-rounded">visibility_off</span>
              </div>
            </div>
            <div class="stat-card-value" id="sHidden">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Fără poză</div>
              <div class="stat-card-icon info">
                <span class="material-symbols-rounded">image_not_supported</span>
              </div>
            </div>
            <div class="stat-card-value" id="sNoPhoto">–</div>
          </div>
        </div>

        <div class="dashboard-table-container">
          <div class="dashboard-table-header">
            <h3 class="dashboard-table-title">Listă licee</h3>
            <div class="table-actions">
              <button class="btn btn-secondary" id="btnBulkDelete" disabled>
                <span class="material-symbols-rounded">delete</span>
                <span class="btn-label">Șterge selecția (<span id="selCount">0</span>)</span>
              </button>
              <button class="btn btn-primary" id="btnAdd">
                <span class="material-symbols-rounded">add</span>
                <span class="btn-label">Adaugă liceu</span>
              </button>
            </div>
          </div>

          <div class="table-filters">
            <select id="fTip"  class="form-select"><option value="">— Toate tipurile —</option></select>
            <select id="fZone" class="form-select"><option value="">— Toate sectoarele —</option></select>
            <select id="fStop" class="form-select">
              <option value="">— Toate stările —</option>
              <option value="Activ">Doar active</option>
              <option value="Ascuns">Doar ascunse</option>
            </select>
          </div>

          <table id="tbl" class="dashboard-table" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="chkAll"></th>
                <th>Foto</th>
                <th>Liceu</th>
                <th>Tip</th>
                <th>Sector</th>
                <th>Clase</th>
                <th>Locuri</th>
                <th>Medie</th>
                <th>Poz.</th>
                <th>Stare</th>
                <th>Acțiuni</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </main>
</div>

<div class="modal-overlay" id="modalForm" hidden>
  <div class="modal-box">
    <form id="frm" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" id="fAction" value="create">
      <input type="hidden" name="orig_name" id="fOrigName" value="">

      <div class="modal-box-header">
        <h3 id="modalTitle">Adaugă liceu</h3>
        <button type="button" class="modal-close" data-close>
          <span class="material-symbols-rounded">close</span>
        </button>
      </div>

      <div class="modal-tabs">
        <button type="button" class="modal-tab active" data-tab="t1">Date generale</button>
        <button type="button" class="modal-tab" data-tab="t2">Statistici admitere</button>
        <button type="button" class="modal-tab" data-tab="t3">Descrieri</button>
      </div>

      <div class="modal-box-body">

        <div class="modal-pane active" id="t1">
          <div class="form-grid">
            <div class="form-field col-6">
              <label>Nume liceu <span class="req">*</span> <small>— cheia tabelului</small></label>
              <input type="text" name="name" id="fName" maxlength="100" required>
              <small class="hint">Doar numele propriu, ex: <em>Alexandru Ioan Cuza</em> (fără „Liceul Teoretic”).</small>
            </div>
            <div class="form-field col-6">
              <label>Tip liceu <span class="req">*</span></label>
              <select name="tip" id="fTipSel" required></select>
            </div>

            <div class="form-field col-4">
              <label>Oraș</label>
              <input type="text" name="city" id="fCity" value="Bucuresti" list="dlCity">
              <datalist id="dlCity"></datalist>
            </div>
            <div class="form-field col-4">
              <label>Sector / zonă <span class="req">*</span></label>
              <input type="text" name="zone" id="fZoneIn" list="dlZone" required placeholder="Sector 3">
              <datalist id="dlZone"></datalist>
            </div>
            <div class="form-field col-4">
              <label>Pagina web</label>
              <input type="url" name="web_page" id="fWeb" placeholder="https://...">
            </div>

            <div class="form-field col-12">
              <label>Adresă</label>
              <input type="text" name="address" id="fAddress" maxlength="255"
                     placeholder="S3 STR Barajul Dunării NR 5">
            </div>

            <div class="form-field col-6">
              <label>Fișier foto <small>(/src/imges/liceu/)</small></label>
              <input type="text" name="photo" id="fPhoto" placeholder="Nume Liceu.jpg">
            </div>
            <div class="form-field col-6">
              <label>Sau încarcă o imagine</label>
              <input type="file" name="photo_file" id="fFile" accept=".jpg,.jpeg,.png,.webp">
              <small class="hint">Se salvează automat cu numele liceului.</small>
            </div>

            <div class="form-field col-12">
              <img id="imgPreview" class="photo-preview" hidden alt="">
            </div>

            <div class="form-field col-12">
              <label class="switch">
                <input type="checkbox" name="stopx" value="1" id="fStopx">
                <span>Ascunde acest liceu pe site (stopx = 1)</span>
              </label>
            </div>
          </div>
        </div>

        <div class="modal-pane" id="t2">
          <div class="form-grid">
            <div class="form-field col-3"><label>Nr. clase</label>
              <input type="number" name="no_clase" id="fNoClase" min="0" max="999" value="0"></div>
            <div class="form-field col-3"><label>Total locuri</label>
              <input type="number" name="total_no_student" id="fTotal" min="0" max="999" value="0"></div>
            <div class="form-field col-3"><label>Locuri rromi</label>
              <input type="number" name="romi_student" id="fRomi" min="0" max="999" value="0"></div>
            <div class="form-field col-3"><label>Locuri CES</label>
              <input type="number" name="ces_student" id="fCes" min="0" max="999" value="0"></div>
            <div class="form-field col-3"><label>Media ultimului admis</label>
              <input type="number" name="avrg_medie" id="fMedie" step="0.01" min="0" max="10" value="0.00"></div>
            <div class="form-field col-3"><label>Poziție în clasament</label>
              <input type="number" name="position" id="fPos" min="0" max="999" value="0"></div>
          </div>
        </div>

        <div class="modal-pane" id="t3">
          <div class="form-field">
            <label>Descriere scurtă <small>— card în listă</small></label>
            <textarea name="short_description" id="fShort" rows="3" maxlength="500"></textarea>
            <small class="hint"><span id="cShort">0</span> / 500 caractere</small>
          </div>
          <div class="form-field">
            <label>Descriere lungă <small>— pagina liceului</small></label>
            <textarea name="long_description" id="fLong" rows="10"></textarea>
            <small class="hint">Folosește rând nou dublu pentru paragrafe separate.</small>
          </div>
          <div class="form-field">
            <label>Descriere (câmp vechi)</label>
            <textarea name="description" id="fDesc" rows="2"></textarea>
          </div>
        </div>
      </div>

      <div class="modal-box-footer">
        <button type="button" class="btn btn-secondary" data-close>Anulează</button>
        <button type="submit" class="btn btn-primary" id="btnSave">
          <span class="material-symbols-rounded">save</span>
          <span class="btn-label">Salvează</span>
        </button>
      </div>
    </form>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
  const PHOTO_URL = <?= json_encode(PHOTO_URL) ?>;
  window.API = 'plugin/liceu_api.php';
</script>
<script src="layout/js/admin.js"></script>

<?php
include __DIR__ . '/template/footer.php';
ob_end_flush();
