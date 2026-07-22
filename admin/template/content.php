<?php
global $con;
if (!isset($con) || !($con instanceof PDO)) {
    require_once __DIR__ . '/../plugin/admin_init.php';
}

$TL   = DB_PREFIX . 'numa_liceu';
$TSP  = DB_PREFIX . 'specializare';
$TLOC = DB_PREFIX . 'locuri';
$TUSR = DB_PREFIX . 'users';

if (!function_exists('e')) {
    function e($v): string {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

$stats = [
    'total'    => 0,
    'active'   => 0,
    'ascunse'  => 0,
    'faraPoza' => 0,
    'spec'     => 0,
    'locuri'   => 0,
    'medie'    => 0,
];

try {
    $r = $con->query("
        SELECT COUNT(*) AS total,
               SUM(CASE WHEN stopx = 0 THEN 1 ELSE 0 END) AS active,
               SUM(CASE WHEN stopx = 1 THEN 1 ELSE 0 END) AS ascunse,
               SUM(CASE WHEN photo = '' OR photo IS NULL THEN 1 ELSE 0 END) AS faraPoza,
               SUM(total_no_student) AS locuri,
               AVG(NULLIF(avrg_medie, 0)) AS medie
        FROM `$TL`
    ")->fetch(PDO::FETCH_ASSOC);

    if ($r) {
        $stats['total']    = (int)$r['total'];
        $stats['active']   = (int)$r['active'];
        $stats['ascunse']  = (int)$r['ascunse'];
        $stats['faraPoza'] = (int)$r['faraPoza'];
        $stats['locuri']   = (int)$r['locuri'];
        $stats['medie']    = round((float)$r['medie'], 2);
    }

    $stats['spec'] = (int)$con->query("SELECT COUNT(*) FROM `$TSP`")->fetchColumn();
} catch (Throwable $ex) {
    $dashError = $ex->getMessage();
}

$perSector = [];
try {
    $perSector = $con->query("
        SELECT zone, COUNT(*) AS n
        FROM `$TL`
        WHERE zone <> ''
        GROUP BY zone
        ORDER BY zone
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $ex) { /* ignoră */ }

$perTip = [];
try {
    $perTip = $con->query("
        SELECT tip, COUNT(*) AS n
        FROM `$TL`
        WHERE tip <> ''
        GROUP BY tip
        ORDER BY n DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $ex) { /* ignoră */ }

$recente = [];
try {
    $recente = $con->query("
        SELECT name, tip, zone, no_clase, total_no_student,
               avrg_medie, position, stopx, updated_at
        FROM `$TL`
        ORDER BY updated_at DESC, name ASC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $ex) { /* ignoră */ }

$topMedii = [];
try {
    $topMedii = $con->query("
        SELECT name, tip, zone, avrg_medie, position
        FROM `$TL`
        WHERE avrg_medie > 0 AND stopx = 0
        ORDER BY avrg_medie DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $ex) { /* ignoră */ }

function medieBadge($v): string
{
    $v = (float)$v;
    if ($v >= 9) return 'success';
    if ($v >= 8) return 'success';
    if ($v >= 7) return 'warning';
    return 'danger';
}
?>
<div class="dashboard-content">

  <?php if (!empty($dashError)): ?>
    <div class="empty-state">
      <div class="empty-state-icon">
        <span class="material-symbols-rounded">error</span>
      </div>
      <h3 class="empty-state-title">Eroare la citirea datelor</h3>
      <p class="empty-state-description"><?= e($dashError) ?></p>
    </div>
  <?php endif; ?>

  <div class="dashboard-view active" id="overview">

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-card-title">Total licee</div>
          <div class="stat-card-icon primary">
            <span class="material-symbols-rounded">school</span>
          </div>
        </div>
        <div class="stat-card-value"><?= $stats['total'] ?></div>
        <div class="stat-card-change positive">
          <span class="material-symbols-rounded">database</span>
          <span>înregistrări în baza de date</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-card-title">Active pe site</div>
          <div class="stat-card-icon success">
            <span class="material-symbols-rounded">visibility</span>
          </div>
        </div>
        <div class="stat-card-value"><?= $stats['active'] ?></div>
        <div class="stat-card-change positive">
          <span class="material-symbols-rounded">trending_up</span>
          <span><?= $stats['total'] ? round($stats['active'] / $stats['total'] * 100) : 0 ?>% din total</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-card-title">Ascunse / fără poză</div>
          <div class="stat-card-icon warning">
            <span class="material-symbols-rounded">visibility_off</span>
          </div>
        </div>
        <div class="stat-card-value"><?= $stats['ascunse'] ?></div>
        <div class="stat-card-change negative">
          <span class="material-symbols-rounded">image_not_supported</span>
          <span><?= $stats['faraPoza'] ?> fără imagine</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-card-title">Total locuri</div>
          <div class="stat-card-icon info">
            <span class="material-symbols-rounded">groups</span>
          </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['locuri'], 0, ',', '.') ?></div>
        <div class="stat-card-change positive">
          <span class="material-symbols-rounded">bookmark</span>
          <span><?= $stats['spec'] ?> specializări</span>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="chart-grid">
      <div class="chart-card">
        <div class="chart-card-header">
          <h3 class="chart-card-title">Licee pe sector</h3>
          <p class="chart-card-subtitle">Distribuția în București</p>
        </div>
        <div class="chart-container">
          <canvas id="progressChart"></canvas>
        </div>
      </div>
      <div class="chart-card">
        <div class="chart-card-header">
          <h3 class="chart-card-title">Licee pe tip</h3>
          <p class="chart-card-subtitle">Primele 8 categorii</p>
        </div>
        <div class="chart-container">
          <canvas id="categoryChart"></canvas>
        </div>
      </div>
    </div>
 
    <div class="dashboard-table-container">
      <div class="dashboard-table-header">
        <h3 class="dashboard-table-title">Ultimele licee modificate</h3>
        <a href="numa_liceu.php" class="btn btn-secondary">Vezi toate</a>
      </div>
      <table class="dashboard-table">
        <thead>
          <tr>
            <th>Liceu</th>
            <th>Sector</th>
            <th>Medie</th>
            <th>Stare</th>
            <th>Modificat</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$recente): ?>
          <tr><td colspan="5">Nicio înregistrare.</td></tr>
        <?php else: foreach ($recente as $l): ?>
          <tr>
            <td>
              <div class="project-title-cell">
                <div class="project-icon">
                  <span class="material-symbols-rounded">school</span>
                </div>
                <div class="project-info">
                  <div class="project-title-text"><?= e($l['name']) ?></div>
                  <div class="project-meta-text">
                    <?= e($l['tip']) ?> • <?= (int)$l['no_clase'] ?> clase •
                    <?= (int)$l['total_no_student'] ?> locuri
                  </div>
                </div>
              </div>
            </td>
            <td><?= e($l['zone']) ?></td>
            <td><?= number_format((float)$l['avrg_medie'], 2, ',', '') ?></td>
            <td>
              <?php if ((int)$l['stopx'] === 1): ?>
                <span class="status-badge warning">Ascuns</span>
              <?php else: ?>
                <span class="status-badge success">Activ</span>
              <?php endif; ?>
            </td>
            <td><?= e(date('d.m.Y', strtotime($l['updated_at']))) ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="dashboard-view" id="projects">
    <div class="dashboard-table-container">
      <div class="dashboard-table-header">
        <h3 class="dashboard-table-title">Top licee după medie</h3>
        <a href="numa_liceu.php" class="btn btn-secondary">Administrează</a>
      </div>
      <table class="dashboard-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Liceu</th>
            <th>Sector</th>
            <th>Medie</th>
            <th>Poziție</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$topMedii): ?>
          <tr><td colspan="5">Nicio înregistrare.</td></tr>
        <?php else: foreach ($topMedii as $i => $l): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td>
              <div class="project-title-cell">
                <div class="project-icon">
                  <span class="material-symbols-rounded">workspace_premium</span>
                </div>
                <div class="project-info">
                  <div class="project-title-text"><?= e($l['name']) ?></div>
                  <div class="project-meta-text"><?= e($l['tip']) ?></div>
                </div>
              </div>
            </td>
            <td><?= e($l['zone']) ?></td>
            <td>
              <span class="status-badge <?= medieBadge($l['avrg_medie']) ?>">
                <?= number_format((float)$l['avrg_medie'], 2, ',', '') ?>
              </span>
            </td>
            <td><?= (int)$l['position'] ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="dashboard-view" id="tasks">
    <div class="empty-state">
      <div class="empty-state-icon">
        <span class="material-symbols-rounded">checklist</span>
      </div>
      <h3 class="empty-state-title">Specializări</h3>
      <p class="empty-state-description">
        <?= $stats['spec'] ?> specializări înregistrate. Modulul de administrare urmează.
      </p>
    </div>
  </div>

  <div class="dashboard-view" id="reports">
    <div class="empty-state">
      <div class="empty-state-icon">
        <span class="material-symbols-rounded">bar_chart</span>
      </div>
      <h3 class="empty-state-title">Rapoarte</h3>
      <p class="empty-state-description">
        Media generală a liceelor active: <strong><?= number_format($stats['medie'], 2, ',', '') ?></strong>.
        Rapoarte detaliate pe sector și specializare urmează.
      </p>
    </div>
  </div>

  <div class="dashboard-view" id="settings">
    <div class="empty-state">
      <div class="empty-state-icon">
        <span class="material-symbols-rounded">settings</span>
      </div>
      <h3 class="empty-state-title">Setări</h3>
      <p class="empty-state-description">
        Bază de date: <code><?= e(DB_DATABASE) ?></code> • prefix tabele: <code><?= e(DB_PREFIX) ?></code>
      </p>
    </div>
  </div>
</div>

<script>
window.DASH = {
  sector: {
    labels: <?= json_encode(array_column($perSector, 'zone'), JSON_UNESCAPED_UNICODE) ?>,
    data:   <?= json_encode(array_map('intval', array_column($perSector, 'n'))) ?>
  },
  tip: {
    labels: <?= json_encode(array_column($perTip, 'tip'), JSON_UNESCAPED_UNICODE) ?>,
    data:   <?= json_encode(array_map('intval', array_column($perTip, 'n'))) ?>
  }
};
</script>
