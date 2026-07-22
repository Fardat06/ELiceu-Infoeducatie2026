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
$pageTitle = 'Tipuri de liceu';

include __DIR__ . '/template/header.php';
?>
<div class="dashboard-container">
  <?php include __DIR__ . '/template/sidebar.php'; ?>
  <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>

  <main class="dashboard-main">
    <?php include __DIR__ . '/template/header_main.php'; ?>

    <div class="dashboard-content">
      <div class="dashboard-view active" id="tipuri">

        <div id="alertBox"></div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Tipuri definite</div>
              <div class="stat-card-icon primary">
                <span class="material-symbols-rounded">category</span>
              </div>
            </div>
            <div class="stat-card-value" id="sTotal">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Tipuri folosite</div>
              <div class="stat-card-icon success">
                <span class="material-symbols-rounded">link</span>
              </div>
            </div>
            <div class="stat-card-value" id="sUsed">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Nefolosite</div>
              <div class="stat-card-icon warning">
                <span class="material-symbols-rounded">link_off</span>
              </div>
            </div>
            <div class="stat-card-value" id="sUnused">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Licee clasificate</div>
              <div class="stat-card-icon info">
                <span class="material-symbols-rounded">school</span>
              </div>
            </div>
            <div class="stat-card-value" id="sLicee">–</div>
          </div>
        </div>

        <div class="dashboard-table-container">
          <div class="dashboard-table-header">
            <h3 class="dashboard-table-title">Tipuri de liceu</h3>
            <div class="table-actions">
              <button class="btn btn-secondary" id="btnBulkDelete" disabled>
                <span class="material-symbols-rounded">delete</span>
                <span class="btn-label">Șterge selecția (<span id="selCount">0</span>)</span>
              </button>
              <button class="btn btn-primary" id="btnAdd">
                <span class="material-symbols-rounded">add</span>
                <span class="btn-label">Adaugă tip</span>
              </button>
            </div>
          </div>

          <div class="table-filters">
            <select id="fUsed" class="form-select">
              <option value="">— Toate —</option>
              <option value="used">Doar folosite</option>
              <option value="unused">Doar nefolosite</option>
            </select>
          </div>

          <table id="tbl" class="dashboard-table" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="chkAll"></th>
                <th>ID</th>
                <th>Denumire</th>
                <th>Licee</th>
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
  <div class="modal-box" style="max-width:560px">
    <form id="frm" novalidate>
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" id="fAction" value="create">
      <input type="hidden" name="id_tip_liceu" id="fId" value="">

      <div class="modal-box-header">
        <h3 id="modalTitle">Adaugă tip de liceu</h3>
        <button type="button" class="modal-close" data-close>
          <span class="material-symbols-rounded">close</span>
        </button>
      </div>

      <div class="modal-box-body">
        <div class="form-field">
          <label>Denumire <span class="req">*</span></label>
          <input type="text" name="description" id="fDesc" maxlength="250" required
                 placeholder="ex: Colegiul Național">
          <small class="hint">
            Denumirea apare în selectorul „Tip liceu” din modulul de licee.
            Redenumirea actualizează automat toate liceele care o folosesc.
          </small>
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
<script>window.API = 'plugin/tip_api.php';</script>
<script src="layout/js/tip_admin.js"></script>

<?php
include __DIR__ . '/template/footer.php';
ob_end_flush();
