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
$pageTitle = 'Moderare recenzii';

include __DIR__ . '/template/header.php';
?>
<div class="dashboard-container">
  <?php include __DIR__ . '/template/sidebar.php'; ?>
  <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>

  <main class="dashboard-main">
    <?php include __DIR__ . '/template/header_main.php'; ?>

    <div class="dashboard-content">
      <div class="dashboard-view active" id="recenzii">

        <div id="alertBox"></div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">În așteptare</div>
              <div class="stat-card-icon warning">
                <span class="material-symbols-rounded">pending</span>
              </div>
            </div>
            <div class="stat-card-value" id="sPending">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Publicate</div>
              <div class="stat-card-icon success">
                <span class="material-symbols-rounded">check_circle</span>
              </div>
            </div>
            <div class="stat-card-value" id="sPublished">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Rating mediu</div>
              <div class="stat-card-icon primary">
                <span class="material-symbols-rounded">star</span>
              </div>
            </div>
            <div class="stat-card-value" id="sRating">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Autori suspendați</div>
              <div class="stat-card-icon info">
                <span class="material-symbols-rounded">person_off</span>
              </div>
            </div>
            <div class="stat-card-value" id="sSuspended">–</div>
          </div>
        </div>

        <div class="dashboard-table-container">
          <div class="dashboard-table-header">
            <h3 class="dashboard-table-title">Recenzii</h3>
            <div class="table-actions">
              <button class="btn btn-secondary" id="btnBulkUnpublish" disabled>
                <span class="material-symbols-rounded">visibility_off</span>
                <span class="btn-label">Retrage</span>
              </button>
              <button class="btn btn-secondary" id="btnBulkDelete" disabled>
                <span class="material-symbols-rounded">delete</span>
                <span class="btn-label">Șterge (<span id="selCount">0</span>)</span>
              </button>
              <button class="btn btn-primary" id="btnBulkPublish" disabled>
                <span class="material-symbols-rounded">check</span>
                <span class="btn-label">Publică selecția</span>
              </button>
            </div>
          </div>

          <div class="table-filters">
            <select id="fStatus" class="form-select">
              <option value="Așteptare">În așteptare</option>
              <option value="">— Toate —</option>
              <option value="Publicat">Doar publicate</option>
            </select>
            <select id="fLiceu" class="form-select"><option value="">— Toate liceele —</option></select>
            <select id="fRating" class="form-select">
              <option value="">— Toate rating-urile —</option>
              <option value="5">5 stele</option>
              <option value="4">4 stele</option>
              <option value="3">3 stele</option>
              <option value="2">2 stele</option>
              <option value="1">1 stea</option>
            </select>
          </div>

          <table id="tbl" class="dashboard-table" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="chkAll"></th>
                <th>Liceu</th>
                <th>Autor</th>
                <th>Rating</th>
                <th>Comentariu</th>
                <th>Data</th>
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
  <div class="modal-box" style="max-width:760px">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

    <div class="modal-box-header">
      <h3 id="modalTitle">Recenzie</h3>
      <button type="button" class="modal-close" data-close>
        <span class="material-symbols-rounded">close</span>
      </button>
    </div>

    <div class="modal-box-body">
      <div class="form-grid">
        <div class="form-field col-6">
          <label>Liceu</label>
          <div id="vLiceu" class="project-title-text">–</div>
        </div>
        <div class="form-field col-6">
          <label>Autor</label>
          <div id="vAutor" class="project-title-text">–</div>
          <small class="hint" id="vEmail"></small>
        </div>

        <div class="form-field col-6">
          <label>Rating</label>
          <div id="vRating">–</div>
        </div>
        <div class="form-field col-6">
          <label>Trimisă la</label>
          <div id="vData">–</div>
        </div>

        <div class="form-field col-12">
          <label>Comentariu</label>
          <div id="vComment"
               style="padding:var(--space-md);background:var(--color-surface);
                      border:1px solid var(--color-border);border-radius:var(--radius-md);
                      white-space:pre-wrap;line-height:1.6"></div>
        </div>

        <div class="form-field col-12" id="altBox" hidden>
          <label>Alte recenzii ale acestui autor</label>
          <div id="vAltele"></div>
        </div>
      </div>
    </div>

    <div class="modal-box-footer" style="justify-content:space-between">
      <div style="display:flex;gap:var(--space-sm)">
        <button type="button" class="btn btn-secondary" id="btnSuspend">
          <span class="material-symbols-rounded">person_off</span>
          <span class="btn-label">Suspendă autorul</span>
        </button>
        <button type="button" class="btn btn-secondary" id="btnReactivate" hidden>
          <span class="material-symbols-rounded">person_check</span>
          <span class="btn-label">Reactivează autorul</span>
        </button>
      </div>
      <div style="display:flex;gap:var(--space-sm)">
        <button type="button" class="btn btn-secondary" id="btnUnpublish">
          <span class="material-symbols-rounded">visibility_off</span>
          <span class="btn-label">Retrage</span>
        </button>
        <button type="button" class="btn btn-primary" id="btnPublish">
          <span class="material-symbols-rounded">check</span>
          <span class="btn-label">Publică</span>
        </button>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>window.API = 'plugin/review_api.php';</script>
<script src="layout/js/review_admin.js"></script>

<?php
include __DIR__ . '/template/footer.php';
ob_end_flush();
