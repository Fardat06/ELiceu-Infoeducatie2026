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
$pageTitle = 'Utilizatori';

include __DIR__ . '/template/header.php';
?>
<div class="dashboard-container">
  <?php include __DIR__ . '/template/sidebar.php'; ?>
  <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>

  <main class="dashboard-main">
    <?php include __DIR__ . '/template/header_main.php'; ?>

    <div class="dashboard-content">
      <div class="dashboard-view active" id="utilizatori">

        <div id="alertBox"></div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Conturi totale</div>
              <div class="stat-card-icon primary">
                <span class="material-symbols-rounded">group</span>
              </div>
            </div>
            <div class="stat-card-value" id="sTotal">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Active</div>
              <div class="stat-card-icon success">
                <span class="material-symbols-rounded">check_circle</span>
              </div>
            </div>
            <div class="stat-card-value" id="sActive">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Dezactivate</div>
              <div class="stat-card-icon warning">
                <span class="material-symbols-rounded">block</span>
              </div>
            </div>
            <div class="stat-card-value" id="sBlocked">–</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-header">
              <div class="stat-card-title">Administratori</div>
              <div class="stat-card-icon info">
                <span class="material-symbols-rounded">shield_person</span>
              </div>
            </div>
            <div class="stat-card-value" id="sAdmins">–</div>
          </div>
        </div>

        <div class="dashboard-table-container">
          <div class="dashboard-table-header">
            <h3 class="dashboard-table-title">Conturi de administrare</h3>
            <div class="table-actions">
              <button class="btn btn-secondary" id="btnBulkDelete" disabled>
                <span class="material-symbols-rounded">delete</span>
                <span class="btn-label">Șterge selecția (<span id="selCount">0</span>)</span>
              </button>
              <button class="btn btn-primary" id="btnAdd">
                <span class="material-symbols-rounded">person_add</span>
                <span class="btn-label">Adaugă utilizator</span>
              </button>
            </div>
          </div>

          <div class="table-filters">
            <select id="fGroup" class="form-select"><option value="">— Toate grupurile —</option></select>
            <select id="fStop" class="form-select">
              <option value="">— Toate stările —</option>
              <option value="Activ">Doar active</option>
              <option value="Blocat">Doar dezactivate</option>
            </select>
          </div>

          <table id="tbl" class="dashboard-table" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="chkAll"></th>
                <th>ID</th>
                <th>Utilizator</th>
                <th>Email</th>
                <th>Grup</th>
                <th>Limbă</th>
                <th>Stare</th>
                <th>Creat</th>
                <th>Acțiuni</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </main>
</div>

<!-- ============ modal utilizator ============ -->
<div class="modal-overlay" id="modalForm" hidden>
  <div class="modal-box">
    <form id="frm" novalidate autocomplete="off">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" id="fAction" value="create">
      <input type="hidden" name="UserID" id="fId" value="">

      <div class="modal-box-header">
        <h3 id="modalTitle">Adaugă utilizator</h3>
        <button type="button" class="modal-close" data-close>
          <span class="material-symbols-rounded">close</span>
        </button>
      </div>

      <div class="modal-tabs">
        <button type="button" class="modal-tab active" data-tab="t1">Cont</button>
        <button type="button" class="modal-tab" data-tab="t2">Drepturi</button>
      </div>

      <div class="modal-box-body">

        <div class="modal-pane active" id="t1">
          <div class="form-grid">
            <div class="form-field col-6">
              <label>Nume utilizator <span class="req">*</span></label>
              <input type="text" name="UserName" id="fUser" maxlength="255" required autocomplete="off">
              <small class="hint">Fără spații. Folosit la autentificare.</small>
            </div>
            <div class="form-field col-6">
              <label>Email <span class="req">*</span></label>
              <input type="email" name="Email" id="fEmail" maxlength="255" required autocomplete="off">
            </div>

            <div class="form-field col-12">
              <label>Nume complet <span class="req">*</span></label>
              <input type="text" name="FullName" id="fFull" maxlength="255" required>
            </div>

            <div class="form-field col-6">
              <label>Prenume</label>
              <input type="text" name="first_name" id="fFirst" maxlength="45">
            </div>
            <div class="form-field col-6">
              <label>Nume</label>
              <input type="text" name="last_name" id="fLast" maxlength="45">
            </div>

            <div class="form-field col-12">
              <label>Parolă <span class="req" id="passReq">*</span></label>
              <input type="password" name="Password" id="fPass" minlength="8" autocomplete="new-password">
              <small class="hint" id="passHint">Minimum 8 caractere.</small>
            </div>
          </div>
        </div>

        <div class="modal-pane" id="t2">
          <div class="form-grid">
            <div class="form-field col-6">
              <label>Grup</label>
              <select name="GroupID" id="fGroupSel"></select>
              <small class="hint">Grupul 2 sau mai mare are drepturi de administrare.</small>
            </div>
            <div class="form-field col-6">
              <label>Limbă interfață</label>
              <select name="Language" id="fLang"></select>
            </div>

            <div class="form-field col-6">
              <label>Status înregistrare</label>
              <select name="RegStatus" id="fReg">
                <option value="1">Aprobat</option>
                <option value="0">În așteptare</option>
              </select>
            </div>
            <div class="form-field col-6">
              <label>Nivel încredere</label>
              <input type="number" name="TrustStatus" id="fTrust" min="0" max="99" value="0">
            </div>

            <div class="form-field col-12">
              <label class="switch">
                <input type="checkbox" name="stopx" value="1" id="fStopx">
                <span>Cont dezactivat (nu se poate autentifica)</span>
              </label>
            </div>

            <div class="form-field col-12" id="selfWarn" hidden>
              <div class="toast-msg warning" style="box-shadow:none">
                <span>Acesta este contul tău. Grupul și starea nu pot fi modificate de aici.</span>
              </div>
            </div>
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

<!-- ============ modal resetare parolă ============ -->
<div class="modal-overlay" id="modalPass" hidden>
  <div class="modal-box" style="max-width:480px">
    <form id="frmPass" novalidate autocomplete="off">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="reset_password">
      <input type="hidden" name="UserID" id="pId" value="">

      <div class="modal-box-header">
        <h3>Resetează parola</h3>
        <button type="button" class="modal-close" data-close-pass>
          <span class="material-symbols-rounded">close</span>
        </button>
      </div>

      <div class="modal-box-body">
        <div class="form-field">
          <label>Utilizator</label>
          <input type="text" id="pUser" readonly>
        </div>
        <div class="form-field">
          <label>Parolă nouă <span class="req">*</span></label>
          <input type="password" name="Password" id="pPass" minlength="8" required autocomplete="new-password">
          <small class="hint">Minimum 8 caractere. Comunic-o utilizatorului pe un canal sigur.</small>
        </div>
      </div>

      <div class="modal-box-footer">
        <button type="button" class="btn btn-secondary" data-close-pass>Anulează</button>
        <button type="submit" class="btn btn-primary" id="btnPassSave">
          <span class="material-symbols-rounded">key</span>
          <span class="btn-label">Resetează</span>
        </button>
      </div>
    </form>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>window.API = 'plugin/users_api.php';</script>
<script src="layout/js/users_admin.js"></script>

<?php
include __DIR__ . '/template/footer.php';
ob_end_flush();