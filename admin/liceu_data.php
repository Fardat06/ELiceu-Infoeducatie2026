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

$csrf = csrf_token();
$pageTitle = 'Date specializări';

include __DIR__ . '/template/header.php';
?>
<div class="dashboard-container">
    <?php include __DIR__ . '/template/sidebar.php'; ?>
    <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>

    <main class="dashboard-main">
        <?php include __DIR__ . '/template/header_main.php'; ?>

        <div class="dashboard-content">
            <div class="dashboard-view active" id="liceudata">

                <div id="alertBox"></div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Specializări</div>
                            <div class="stat-card-icon primary">
                                <span class="material-symbols-rounded">dataset</span>
                            </div>
                        </div>
                        <div class="stat-card-value" id="sTotal">–</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Licee distincte</div>
                            <div class="stat-card-icon success">
                                <span class="material-symbols-rounded">school</span>
                            </div>
                        </div>
                        <div class="stat-card-value" id="sLicee">–</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Locuri 2025</div>
                            <div class="stat-card-icon info">
                                <span class="material-symbols-rounded">groups</span>
                            </div>
                        </div>
                        <div class="stat-card-value" id="sLocuri">–</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Rânduri cu probleme</div>
                            <div class="stat-card-icon warning">
                                <span class="material-symbols-rounded">warning</span>
                            </div>
                        </div>
                        <div class="stat-card-value" id="sProbleme">–</div>
                    </div>
                </div>

                <div class="dashboard-table-container">
                    <div class="dashboard-table-header">
                        <h3 class="dashboard-table-title">
                            Specializări
                            <span class="status-badge info" title="Cele 4 tabele sunt legate prin ID pozițional">liceu +
                                locuri + medie + poziție</span>
                        </h3>
                        <div class="table-actions">
                            <button class="btn btn-secondary" id="btnIntegrity">
                                <span class="material-symbols-rounded">fact_check</span>
                                <span class="btn-label">Verifică integritatea</span>
                            </button>
                            <button class="btn btn-secondary" id="btnBulkDelete" disabled>
                                <span class="material-symbols-rounded">delete</span>
                                <span class="btn-label">Șterge (<span id="selCount">0</span>)</span>
                            </button>
                            <button class="btn btn-primary" id="btnAdd">
                                <span class="material-symbols-rounded">add</span>
                                <span class="btn-label">Adaugă specializare</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-filters">
                        <select id="fLiceu" class="form-select">
                            <option value="">— Toate liceele —</option>
                        </select>
                        <select id="fProfil" class="form-select">
                            <option value="">— Toate profilurile —</option>
                        </select>
                        <select id="fZone" class="form-select">
                            <option value="">— Toate sectoarele —</option>
                        </select>
                        <select id="fProb" class="form-select">
                            <option value="">— Toate rândurile —</option>
                            <option value="1">Doar cu probleme</option>
                        </select>
                    </div>

                    <table id="tbl" class="dashboard-table" style="width:100%">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="chkAll"></th>
                                <th>ID</th>
                                <th>Liceu</th>
                                <th>Specializare</th>
                                <th>Profil</th>
                                <th>Sector</th>
                                <th>Locuri '25</th>
                                <th>Medie '25</th>
                                <th>Poz. '25</th>
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
        <form id="frm" novalidate>
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" id="fAction" value="create">
            <input type="hidden" name="id" id="fId" value="">

            <div class="modal-box-header">
                <h3 id="modalTitle">Adaugă specializare</h3>
                <button type="button" class="modal-close" data-close>
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <div class="modal-tabs">
                <button type="button" class="modal-tab active" data-tab="t1">Identificare</button>
                <button type="button" class="modal-tab" data-tab="t2">Locuri</button>
                <button type="button" class="modal-tab" data-tab="t3">Medii</button>
                <button type="button" class="modal-tab" data-tab="t4">Poziții</button>
            </div>

            <div class="modal-box-body">

                <!-- identificare -->
                <div class="modal-pane active" id="t1">
                    <div class="form-grid">
                        <div class="form-field col-6">
                            <label>Nume liceu <span class="req">*</span></label>
                            <select name="name" id="fName" required>
                                <option value="">— Alege liceul —</option>
                            </select>
                        </div>
                        <div class="form-field col-6">
                            <label>Tip</label>
                            <select name="tip" id="fTip">
                                <option value="">— Alege tipul —</option>
                            </select>
                        </div>

                        <div class="form-field col-6">
                            <label>Profil</label>
                            <select name="profil" id="fProfilIn">
                                <option value="">— Alege profilul —</option>
                            </select>
                        </div>
                        <div class="form-field col-6">
                            <label>Specializare <span class="req">*</span></label>
                            <select name="specializare" id="fSpec" required>
                                <option value="">— Alege specializarea —</option>
                            </select>
                        </div>

                        <div class="form-field col-4">
                            <label>Limbă</label>
                            <select name="limba" id="fLimba">
                                <option value="">— Alege limba —</option>
                            </select>
                        </div>
                        <div class="form-field col-4">
                            <label>Bilingv</label>
                            <select name="bilingv" id="fBil">
                                <option value="">— Alege —</option>
                            </select>
                        </div>
                        <div class="form-field col-4">
                            <label>Intensiv</label>
                            <input type="text" name="intesiv" id="fInt" maxlength="250" value="nu">
                        </div>

                        <div class="form-field col-4">
                            <label>Oraș</label>
                            <input type="text" name="city" id="fCity" maxlength="250" value="Bucuresti">
                        </div>
                        <div class="form-field col-4">
                            <label>Sector</label>
                            <input type="text" name="zone" id="fZoneIn" maxlength="250" list="dlZone">
                            <datalist id="dlZone"></datalist>
                        </div>
                        <div class="form-field col-4">
                            <label>Cod broșură</label>
                            <input type="number" name="code_din_brosura" id="fCod" min="0" max="9999" value="0">
                        </div>

                        <div class="form-field col-12">
                            <label>Adresă</label>
                            <input type="text" name="address" id="fAddr" maxlength="255">
                        </div>

                        <div class="form-field col-3"><label>Program cls. 9</label>
                            <input type="text" name="program_9" id="fP9" maxlength="20" value="dimi">
                        </div>
                        <div class="form-field col-3"><label>Program cls. 10</label>
                            <input type="text" name="program_10" id="fP10" maxlength="20" value="dimi">
                        </div>
                        <div class="form-field col-3"><label>Program cls. 11</label>
                            <input type="text" name="program_11" id="fP11" maxlength="20" value="dimi">
                        </div>
                        <div class="form-field col-3"><label>Program cls. 12</label>
                            <input type="text" name="program_12" id="fP12" maxlength="20" value="dimi">
                        </div>

                        <div class="form-field col-12">
                            <label class="switch">
                                <input type="checkbox" name="stopx" value="1" id="fStopx">
                                <span>Ascuns pe site (stopx = 1)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-pane" id="t2">
                    <div class="form-grid">
                        <?php foreach ([2025, 2024, 2023, 2022, 2021, 2020] as $y): ?>
                            <div class="form-field col-4">
                                <label>Locuri <?= $y ?></label>
                                <input type="number" name="locuri_<?= $y ?>" id="fLoc<?= $y ?>" min="0" max="999" value="0">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="modal-pane" id="t3">
                    <p class="form-text" style="color:var(--color-text-secondary);margin-bottom:var(--space-md)">
                        <strong>u_medie</strong> = media ultimului admis · <strong>p_medie</strong> = media primului
                        admis
                    </p>
                    <div class="form-grid">
                        <?php foreach ([2025, 2024, 2023, 2022, 2021, 2020] as $y): ?>
                            <div class="form-field col-3">
                                <label>Ultima <?= $y ?></label>
                                <input type="number" name="u_medie_<?= $y ?>" id="fUM<?= $y ?>" step="0.01" min="0" max="10"
                                    value="0.00">
                            </div>
                            <div class="form-field col-3">
                                <label>Prima <?= $y ?></label>
                                <input type="number" name="p_medie_<?= $y ?>" id="fPM<?= $y ?>" step="0.01" min="0" max="10"
                                    value="0.00">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="modal-pane" id="t4">
                    <p class="form-text" style="color:var(--color-text-secondary);margin-bottom:var(--space-md)">
                        <strong>u_pozition</strong> = poziția ultimului admis · <strong>nr_place</strong> = număr locuri
                        raportate
                    </p>
                    <div class="form-grid">
                        <?php foreach ([2025, 2024, 2023, 2022, 2021, 2020] as $y): ?>
                            <div class="form-field col-3">
                                <label>Poziție <?= $y ?></label>
                                <input type="number" name="u_pozition_<?= $y ?>" id="fUP<?= $y ?>" min="0" max="999999"
                                    value="0">
                            </div>
                            <div class="form-field col-3">
                                <label>Nr. locuri <?= $y ?></label>
                                <input type="number" name="nr_place_<?= $y ?>" id="fNP<?= $y ?>" min="0" max="999999"
                                    value="0">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="modal-box-footer">
                <button type="button" class="btn btn-secondary" data-close>Anulează</button>
                <button type="submit" class="btn btn-primary" id="btnSave">
                    <span class="material-symbols-rounded">save</span>
                    <span class="btn-label">Salvează în toate tabelele</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalInt" hidden>
    <div class="modal-box" style="max-width:520px">
        <div class="modal-box-header">
            <h3>Verificare integritate</h3>
            <button type="button" class="modal-close" data-close-int>
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>
        <div class="modal-box-body" id="intBody">Se verifică…</div>
        <div class="modal-box-footer">
            <button type="button" class="btn btn-secondary" data-close-int>Închide</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>window.API = 'plugin/liceudata_api.php';</script>
<script src="layout/js/liceudata_admin.js"></script>

<?php
include __DIR__ . '/template/footer.php';
ob_end_flush();
