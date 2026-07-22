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
$pageTitle = 'Admitere';

include __DIR__ . '/template/header.php';
?>
<div class="dashboard-container">
  <?php include __DIR__ . '/template/sidebar.php'; ?>
  <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>

  <main class="dashboard-main">
    <?php include __DIR__ . '/template/header_main.php'; ?>

    <div class="dashboard-content">
      <div class="dashboard-view active" id="admitere">

        <div id="alertBox"></div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Specializări</div>
              <div class="stat-card-icon primary">
                <span class="material-symbols-rounded">list_alt</span>
              </div>
            </div>
            <div class="stat-card-value" id="sTotal">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Total locuri</div>
              <div class="stat-card-icon success">
                <span class="material-symbols-rounded">groups</span>
              </div>
            </div>
            <div class="stat-card-value" id="sLocuri">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Școli distincte</div>
              <div class="stat-card-icon info">
                <span class="material-symbols-rounded">school</span>
              </div>
            </div>
            <div class="stat-card-value" id="sScoli">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Media generală</div>
              <div class="stat-card-icon warning">
                <span class="material-symbols-rounded">functions</span>
              </div>
            </div>
            <div class="stat-card-value" id="sMedie">–</div>
          </div>
        </div>

        <div class="dashboard-table-container">
          <div class="dashboard-table-header">
            <h3 class="dashboard-table-title">
              Oferta de admitere <span id="yearLabel" class="status-badge info">2026</span>
              <span id="roBadge" class="status-badge warning" hidden>doar citire</span>
            </h3>
            <div class="table-actions">
              <button class="btn btn-secondary" id="btnBulkDelete" disabled>
                <span class="material-symbols-rounded">delete</span>
                <span class="btn-label">Șterge selecția (<span id="selCount">0</span>)</span>
              </button>
              <button class="btn btn-primary" id="btnAdd">
                <span class="material-symbols-rounded">add</span>
                <span class="btn-label">Adaugă specializare</span>
              </button>
            </div>
          </div>

          <div class="table-filters">
            <select id="fYear" class="form-select">
              <option value="2026">An 2026</option>
              <option value="2025">An 2025 (doar citire)</option>
            </select>
            <select id="fScoala" class="form-select"><option value="">— Toate școlile —</option></select>
            <select id="fProfil" class="form-select"><option value="">— Toate profilurile —</option></select>
            <select id="fFiliera" class="form-select"><option value="">— Toate filierele —</option></select>
          </div>

          <table id="tbl" class="dashboard-table" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="chkAll"></th>
                <th>Cod</th>
                <th>Școala</th>
                <th>Specializare</th>
                <th>Filieră</th>
                <th>Profil</th>
                <th>Clase</th>
                <th>Locuri</th>
                <th>Media</th>
                <th>Acțiuni</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </main>
</div>

<!-- ============ modal ============ -->
<div class="modal-overlay" id="modalForm" hidden>
  <div class="modal-box">
    <form id="frm" novalidate>
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" id="fAction" value="create">
      <input type="hidden" name="id" id="fId" value="">
      <input type="hidden" name="year" id="fYearHidden" value="2026">

      <div class="modal-box-header">
        <h3 id="modalTitle">Adaugă specializare</h3>
        <button type="button" class="modal-close" data-close>
          <span class="material-symbols-rounded">close</span>
        </button>
      </div>

      <div class="modal-tabs">
        <button type="button" class="modal-tab active" data-tab="t1">Identificare</button>
        <button type="button" class="modal-tab" data-tab="t2">Locuri și medii</button>
        <button type="button" class="modal-tab" data-tab="t3">Observații</button>
      </div>

      <div class="modal-box-body">

        <div class="modal-pane active" id="t1">
          <div class="form-grid">
            <div class="form-field col-4">
              <label>Codificare <span class="req">*</span></label>
              <input type="text" name="codificare" id="fCod" maxlength="10" required placeholder="ex: 101">
              <small class="hint">Codul unic al specializării.</small>
            </div>
            <div class="form-field col-8">
              <label>Numele școlii <span class="req">*</span></label>
              <input type="text" name="nume_scoala" id="fScoalaIn" maxlength="160" required list="dlScoala">
              <datalist id="dlScoala"></datalist>
            </div>

            <div class="form-field col-6">
              <label>Tip școală</label>
              <input type="text" name="tip_scoala" id="fTip" maxlength="120" list="dlTip">
              <datalist id="dlTip"></datalist>
            </div>
            <div class="form-field col-6">
              <label>Filieră</label>
              <input type="text" name="filiera" id="fFil" maxlength="80" list="dlFiliera">
              <datalist id="dlFiliera"></datalist>
            </div>

            <div class="form-field col-6">
              <label>Profil</label>
              <input type="text" name="profil" id="fProf" maxlength="80" list="dlProfil">
              <datalist id="dlProfil"></datalist>
            </div>
            <div class="form-field col-6">
              <label>Specializare</label>
              <input type="text" name="specializare" id="fSpec" maxlength="160" list="dlSpec">
              <datalist id="dlSpec"></datalist>
            </div>

            <div class="form-field col-12">
              <label>Specializare completă</label>
              <input type="text" name="specializare_complet" id="fSpecFull" maxlength="255">
              <small class="hint">Denumirea extinsă, așa cum apare în broșură.</small>
            </div>

            <div class="form-field col-12">
              <label>Mențiune</label>
              <input type="text" name="mentiune" id="fMentiune" maxlength="160"
                     placeholder="ex: bilingv limba engleză">
            </div>
          </div>
        </div>

        <div class="modal-pane" id="t2">
          <div class="form-grid">
            <div class="form-field col-3"><label>Nr. clase</label>
              <input type="number" name="clase" id="fClase" step="0.5" min="0" max="99"></div>
            <div class="form-field col-3"><label>Total locuri</label>
              <input type="number" name="total_locuri" id="fLocuri" min="0" max="9999"></div>
            <div class="form-field col-3"><label>Locuri rromi</label>
              <input type="number" name="locuri_romi" id="fRomi" min="0" max="999"></div>
            <div class="form-field col-3"><label>Locuri CES</label>
              <input type="number" name="locuri_ces" id="fCes" min="0" max="999"></div>
            <div class="form-field col-4"><label>Media ultimului admis</label>
              <input type="number" name="media_ultimului_admis" id="fMedie" step="0.01" min="0" max="10"></div>
            <div class="form-field col-4"><label>Nr. ordine</label>
              <input type="number" name="nr" id="fNr" min="0"></div>
          </div>
        </div>

        <div class="modal-pane" id="t3">
          <div class="form-field">
            <label>Observații</label>
            <textarea name="observatii" id="fObs" rows="4" maxlength="255"></textarea>
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
<script>window.API = 'plugin/admitere_api.php';</script>
<script src="layout/js/admitere_admin.js"></script>

<?php
include __DIR__ . '/template/footer.php';
ob_end_flush();