<?php
ob_start();
require_once __DIR__ . '/plugin/admin_init.php';
ob_clean();

if (!function_exists('csrf_token')) { die('admin_init.php nu a fost încărcat.'); }
if (!isset($_SESSION['username-x'])) { header('Location: index.php'); exit(); }

$csrf      = csrf_token();
$pageTitle = 'Setări';

include __DIR__ . '/template/header.php';
?>
<div class="dashboard-container">
  <?php include __DIR__ . '/template/sidebar.php'; ?>
  <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>

  <main class="dashboard-main">
    <?php include __DIR__ . '/template/header_main.php'; ?>

    <div class="dashboard-content">
      <div class="dashboard-view active" id="setari">

        <div id="alertBox"></div>

        <form id="frmSet" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="save">

          <!-- ============ identitatea site-ului ============ -->
          <div class="dashboard-table-container" style="margin-bottom:var(--space-lg)">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">
                <span class="material-symbols-rounded" style="vertical-align:-6px">public</span>
                Identitatea site-ului
              </h3>
            </div>

            <div style="padding:var(--space-lg)">
              <div class="form-grid">

                <div class="form-field col-6">
                  <label>Nume site <span class="req">*</span></label>
                  <input type="text" name="site_name" id="site_name" maxlength="120" required>
                  <small class="hint">Apare în titlul paginilor și ca expeditor în emailuri.</small>
                </div>

                <div class="form-field col-6">
                  <label>Slogan</label>
                  <input type="text" name="site_tagline" id="site_tagline" maxlength="200"
                         placeholder="Găsește liceul potrivit">
                </div>

                <div class="form-field col-12">
                  <label>Adresa site-ului</label>
                  <input type="url" name="site_url" id="site_url" placeholder="https://eliceu.ro">
                  <small class="hint">Folosită la generarea linkurilor absolute din emailuri.</small>
                </div>

                <div class="form-field col-6">
                  <label>Logo</label>
                  <input type="file" name="logo_file" id="logo_file"
                         accept=".png,.jpg,.jpeg,.webp,.svg">
                  <input type="hidden" name="site_logo" id="site_logo">
                  <div style="margin-top:var(--space-sm);display:flex;align-items:center;gap:var(--space-sm)">
                    <img id="logoPreview" class="photo-preview" hidden alt="" style="max-height:70px">
                    <button type="button" class="btn btn-secondary" id="btnDelLogo" hidden>
                      <span class="material-symbols-rounded">delete</span>
                      <span class="btn-label">Șterge</span>
                    </button>
                  </div>
                  <small class="hint">PNG, JPG, WEBP sau SVG. Se salvează în <code>/src/imges/</code>.</small>
                </div>

                <div class="form-field col-6">
                  <label>Favicon</label>
                  <input type="file" name="favicon_file" id="favicon_file" accept=".png,.ico,.svg">
                  <input type="hidden" name="site_favicon" id="site_favicon">
                  <div style="margin-top:var(--space-sm);display:flex;align-items:center;gap:var(--space-sm)">
                    <img id="favPreview" class="photo-preview" hidden alt="" style="max-height:48px">
                    <button type="button" class="btn btn-secondary" id="btnDelFav" hidden>
                      <span class="material-symbols-rounded">delete</span>
                      <span class="btn-label">Șterge</span>
                    </button>
                  </div>
                  <small class="hint">Recomandat 32×32 px sau SVG.</small>
                </div>

              </div>
            </div>
          </div>

          <!-- ============ adrese de email ============ -->
          <div class="dashboard-table-container" style="margin-bottom:var(--space-lg)">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">
                <span class="material-symbols-rounded" style="vertical-align:-6px">mail</span>
                Adrese de email
              </h3>
              <div class="table-actions">
                <button type="button" class="btn btn-secondary" id="btnTestMail">
                  <span class="material-symbols-rounded">send</span>
                  <span class="btn-label">Trimite email de test</span>
                </button>
              </div>
            </div>

            <div style="padding:var(--space-lg)">
              <div class="form-grid">

                <div class="form-field col-6">
                  <label>Email cod OTP</label>
                  <input type="email" name="email_otp" id="email_otp" placeholder="otp@eliceu.ro">
                  <small class="hint">Expeditorul codurilor de autentificare în doi pași.</small>
                </div>

                <div class="form-field col-6">
                  <label>Email confirmare cont</label>
                  <input type="email" name="email_confirm" id="email_confirm"
                         placeholder="confirmare@eliceu.ro">
                  <small class="hint">Expeditorul linkurilor de activare și de resetare a parolei.</small>
                </div>

                <div class="form-field col-6">
                  <label>Email listă opțiuni</label>
                  <input type="email" name="email_list" id="email_list" placeholder="lista@eliceu.ro">
                  <small class="hint">Expeditorul listelor personalizate trimise elevilor.</small>
                </div>

                <div class="form-field col-6">
                  <label>Email contact</label>
                  <input type="email" name="email_contact" id="email_contact"
                         placeholder="contact@eliceu.ro">
                  <small class="hint">Adresa publică, afișată pe site și folosită ca Reply-To.</small>
                </div>

                <div class="form-field col-12">
                  <label>Nume expeditor</label>
                  <input type="text" name="email_from_name" id="email_from_name" maxlength="120"
                         placeholder="Ǝliceu">
                  <small class="hint">Numele afișat în căsuța destinatarului, înaintea adresei.</small>
                </div>

              </div>
            </div>
          </div>

          <!-- ============ server SMTP ============ -->
          <div class="dashboard-table-container" style="margin-bottom:var(--space-lg)">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">
                <span class="material-symbols-rounded" style="vertical-align:-6px">dns</span>
                Server SMTP
                <span class="status-badge warning">opțional</span>
              </h3>
            </div>

            <div style="padding:var(--space-lg)">
              <div class="form-grid">

                <div class="form-field col-6">
                  <label>Host</label>
                  <input type="text" name="smtp_host" id="smtp_host" placeholder="smtp.gmail.com">
                  <small class="hint">Lasă gol pentru a folosi funcția <code>mail()</code> din PHP.</small>
                </div>

                <div class="form-field col-3">
                  <label>Port</label>
                  <input type="number" name="smtp_port" id="smtp_port" min="1" max="65535" value="587">
                </div>

                <div class="form-field col-3">
                  <label>Securitate</label>
                  <select name="smtp_secure" id="smtp_secure">
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                    <option value="">Fără</option>
                  </select>
                </div>

                <div class="form-field col-12">
                  <label>Utilizator</label>
                  <input type="text" name="smtp_user" id="smtp_user" autocomplete="off">
                </div>

                <div class="form-field col-12">
                  <div class="toast-msg warning" style="box-shadow:none">
                    <span>
                      Parola SMTP nu se salvează aici, ca să nu ajungă în copiile de siguranță
                      ale bazei de date. Pune-o în <code>plugin/config.php</code>:
                      <code>define('SMTP_PASS', '…');</code>
                    </span>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- ============ mentenanță ============ -->
          <div class="dashboard-table-container" style="margin-bottom:var(--space-lg)">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">
                <span class="material-symbols-rounded" style="vertical-align:-6px">construction</span>
                Mod mentenanță
              </h3>
            </div>

            <div style="padding:var(--space-lg)">
              <div class="form-grid">

                <div class="form-field col-12">
                  <label class="switch">
                    <input type="checkbox" name="maintenance_mode" value="1" id="maintenance_mode">
                    <span>Activează modul mentenanță</span>
                  </label>
                  <small class="hint">
                    Vizitatorii sunt redirecționați către <code>maintenance.php</code>.
                    Conturile autentificate și panoul de administrare rămân accesibile.
                  </small>
                </div>

                <div class="form-field col-6">
                  <label>Revenire estimată</label>
                  <input type="text" name="maintenance_until" id="maintenance_until"
                         maxlength="100" placeholder="ex: 22 iulie, ora 18:00">
                  <small class="hint">Afișată pe pagina de așteptare. Lasă gol pentru a o ascunde.</small>
                </div>

                <div class="form-field col-6" style="display:flex;align-items:flex-end">
                  <a href="../maintenance.php" target="_blank" class="btn btn-secondary">
                    <span class="material-symbols-rounded">open_in_new</span>
                    <span class="btn-label">Previzualizează pagina</span>
                  </a>
                </div>

              </div>
            </div>
          </div>

          <div style="display:flex;justify-content:flex-end;gap:var(--space-sm);padding-bottom:var(--space-xl)">
            <button type="button" class="btn btn-secondary" id="btnReload">
              <span class="material-symbols-rounded">refresh</span>
              <span class="btn-label">Reîncarcă</span>
            </button>
            <button type="submit" class="btn btn-primary" id="btnSave">
              <span class="material-symbols-rounded">save</span>
              <span class="btn-label">Salvează setările</span>
            </button>
          </div>

        </form>

      </div>
    </div>
  </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>window.API = 'plugin/settings_api.php';</script>
<script src="layout/js/settings_admin.js"></script>

<?php
include __DIR__ . '/template/footer.php';
ob_end_flush();