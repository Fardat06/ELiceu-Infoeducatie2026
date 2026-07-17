<?php
ob_start();

$urllink = $_SERVER['REQUEST_URI'];
$path = parse_url($urllink, PHP_URL_PATH);
$filename = basename($path);

$pageTitle1 = 'HOME';
if (!isset($con)) {
  require_once('../plugin/config.php');
  require_once '../plugin/function.php';
  require_once '../plugin/init.php';
  global $con;
}
global $where;
global $and;
$itemperpage = 75;
if (isset($_SESSION['ID'])) {
  $stmtx = $con->prepare("SELECT lista_s FROM " . DB_PREFIX . "user_details  WHERE id = ? ");
  $stmtx->execute(array($_SESSION['ID']));
  $row1 = $stmtx->fetch();
  $subject = $row1['lista_s'];
}
$stmt = "SELECT h1.*, h3.photo, h3.short_description, h3.id_numa_liceu, h3.zone
FROM home_admitere h1
INNER JOIN home_numa_liceu h3
  ON h1.nume_scoala = CONVERT(h3.name USING utf8mb4) COLLATE utf8mb4_unicode_ci
WHERE h1.id > 0 ";

$params = [];

if ($filename == 'licee_specializari_lista.php') {
  if ($subject != '') {
    $stmt .= " AND h1.id IN (" . $subject . ")";
    $itemperpage = 100;
  } else {
    $stmt .= " AND h1.id =''";
    $itemperpage = 75;
  }
}

if (!empty($_GET['profil'])) {
  $profilPlaceholders = [];
  foreach ($_GET['profil'] as $index => $profil) {
    $key = "'" . $profil . "'";
    $profilPlaceholders[] = $key;
    $params[$key] = $profil;
  }
  $stmt .= " AND h1.profil IN (" . implode(",", $profilPlaceholders) . ")";
}

if (!empty($_GET['sector'])) {
  $sectorPlaceholders = [];
  foreach ($_GET['sector'] as $index => $sector) {
    $key = "'Sector " . $sector . "'";
    $sectorPlaceholders[] = $key;
    $params[$key] = $sector;
  }
  $stmt .= " AND h3.zone IN (" . implode(",", $sectorPlaceholders) . ")";
}

if (isset($_GET['specializare'])) {
  $profilPlaceholders = [];
  foreach ($_GET['specializare'] as $index => $specializare) {
    $key = "'" . $specializare . "'";
    $profilPlaceholders[] = $key;
    $params[$key] = $specializare;
  }
  $stmt .= " AND h1.specializare IN (" . implode(",", $profilPlaceholders) . ")";
}

if (isset($_GET['bilingv'])) {
  $profilPlaceholders = [];
  foreach ($_GET['bilingv'] as $index => $bilingv) {
    $key = "'" . $bilingv . "'";
    $profilPlaceholders[] = $key;
    $params[$key] = $bilingv;
  }
  $stmt .= " AND h1.mentiune IN (" . implode(",", $profilPlaceholders) . ")";
}

if (!empty($_GET['searchInput'])) {
  $search = '%' . $_GET['searchInput'] . '%';
  $stmt .= " AND (h1.nume_scoala LIKE '$search' OR h1.tip_scoala LIKE '$search')";
}

if (isset($_GET['min_medie']) && isset($_GET['max_medie'])) {
  $min = $_GET['min_medie'];
  $max = $_GET['max_medie'];
  $lo = min($min, $max);
  $hi = max($min, $max);
  $stmt .= " AND h1.media_ultimului_admis BETWEEN $lo AND $hi";
}

$sort = $_GET['sort'] ?? 'default';
$order = match ($sort) {
  'medie-desc' => 'ORDER BY  h1.media_ultimului_admis DESC',
  'medie-asc'  => 'ORDER BY  h1.media_ultimului_admis ASC',
  'tip-desc'   => 'ORDER BY  h1.tip_scoala DESC',
  'tip-asc'    => 'ORDER BY  h1.tip_scoala ASC',
  'name-asc'   => 'ORDER BY  h1.nume_scoala ASC',
  'name-desc'  => 'ORDER BY  h1.nume_scoala DESC',
  default      => 'ORDER BY  h1.nume_scoala ASC'
};

// Personal list preserves user's drag-drop order
if ($filename == 'licee_specializari_lista.php' && in_array($sort, ['default', '']) && !empty($subject)) {
  $order = "ORDER BY FIELD(h1.id, " . $subject . ")";
}
$stmt .= " $order";

$stmt2 = $con->prepare($stmt);
$stmt2->execute();
$row  = $stmt2->fetchAll();
$rows = $stmt2->rowCount();

$page_rows = $itemperpage;
$last = ceil($rows / $page_rows);
if ($last < 1) $last = 1;

// Fix: correct regex to strip anything that isn't a digit
$pagenum = 1;
if (isset($_GET['pn'])) {
  $pagenum = (int) preg_replace('#[^0-9]#', '', $_GET['pn']);
}
if ($pagenum < 1)          $pagenum = 1;
else if ($pagenum > $last) $pagenum = $last;

$limit = 'LIMIT ' . ($pagenum - 1) * $page_rows . ',' . $page_rows;
$stmt3 = $stmt . " " . $limit;
$stmt1 = $con->prepare($stmt3);
$stmt1->execute();


/* PAGINATION CONTROLS
 * URL now uses the *actual* page filename ($filename), so pagination
 * on licee_admitere.php stays on that page instead of jumping to
 * licee_specializari.php (which was the old hardcoded bug).
 */
$paginationCtrls = '';
if ($last != 1) {
  $currentParams = $_GET;
  unset($currentParams['pn']);
  // Default to licee_admitere.php when included from a page whose name we can't tell.
  $ownPage = ($filename === '' || $filename === null) ? 'licee_admitere.php' : $filename;
  $baseUrl = $ownPage . '?' . http_build_query($currentParams);
  $sep = empty($currentParams) ? '' : '&';

  if ($pagenum > 1) {
    $previous = $pagenum - 1;
    $paginationCtrls .= '<a href="' . $baseUrl . $sep . 'pn=' . $previous . '" class="table_btn pg-prev" aria-label="Pagina anterioară">‹ Prev</a> &nbsp; ';

    for ($i = $pagenum - 4; $i < $pagenum; $i++) {
      if ($i > 0) {
        $paginationCtrls .= '<a href="' . $baseUrl . $sep . 'pn=' . $i . '" class="table_btn pg-num">' . $i . '</a> &nbsp; ';
      }
    }
  }

  $paginationCtrls .= '<span class="pg-current">' . $pagenum . '</span> &nbsp; ';

  for ($i = $pagenum + 1; $i <= $last; $i++) {
    $paginationCtrls .= '<a href="' . $baseUrl . $sep . 'pn=' . $i . '" class="table_btn pg-num">' . $i . '</a> &nbsp; ';
    if ($i >= $pagenum + 4) break;
  }

  if ($pagenum != $last) {
    $next = $pagenum + 1;
    $paginationCtrls .= ' &nbsp; <a href="' . $baseUrl . $sep . 'pn=' . $next . '" class="table_btn pg-next" aria-label="Pagina următoare">Next ›</a> ';
  }
}
?>
<div class="products-grid" id="productsGrid">
  <?php if (isset($_SESSION['ID'])) {
    if (empty($_SESSION['csrf_token']))
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo '<input type="hidden" id="csrfToken" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
  } ?>
  <?php
  $rows1 = $stmt1->fetchAll();
  $pos = ($pagenum - 1) * $itemperpage;
  foreach ($rows1 as $row) {
    if ($filename == 'licee_specializari_lista.php') {
      /* ── DRAG-AND-DROP "LISTA MEA" MODE — compact stacked card,
             preserved unchanged. Mobile CSS excludes .sort-item
             from the horizontal grid so drag/drop still works. ── */
      $pos++;
      echo '<div class="product-card sort-item" style="animation-delay:.1s;height: 200px;" id="' . $row['id'] . '">';
      echo '<span class="sort-index">' . $pos . '</span>';
      echo '<div class="card-title" style="text-align: center;padding-top: 10px;">' . $row['nume_scoala'] . '</div>';
      echo '<div class="card-title"  style="text-align: center;">' . $row['specializare'] . ' ' . $row['mentiune'] . '</div>';
      if ($row['media_ultimului_admis'] != null) {
        echo '<div class="price-main"  style="text-align: center;">Ultima medie de admitere : ' . $row['media_ultimului_admis'] . '</div>';
      } else {
        echo '<div class="price-main"  style="text-align: center;">Ultima medie de admitere : - </div>';
      }
      echo '<div class="card-title"  style="text-align: center;">Codificare : ' . $row['codificare'] . '</div>';
      echo '<div class="card-title"  style="text-align: center;">Nr de Clasa : ' . $row['clase'] . '</div>';
      echo '</div>';

    } else {
      /* ── NORMAL ADMITERE CARD — mobile-friendly layout with
             medie tier stripe, tag chips, codificare pill,
             icon action button. ── */
      $medieVal   = is_numeric($row['media_ultimului_admis']) ? (float)$row['media_ultimului_admis'] : 0;
      $medieTier  = $medieVal >= 9   ? 'gold'
                  : ($medieVal >= 8   ? 'high'
                  : ($medieVal >= 7   ? 'mid'
                  : 'low'));
      $locuriNr   = $row['total_locuri'];
      $locuriTxt  = (is_numeric($locuriNr) && (int)$locuriNr === 1) ? 'loc' : 'locuri';
      $claseNr    = $row['clase'];
      $claseTxt   = (is_numeric($claseNr) && (int)$claseNr === 1) ? 'clasa' : 'clase';
      // "strpos != ''" is a fragile check; use !== false so id-at-position-0 still counts as present
      $inList     = isset($_SESSION['ID']) && strpos($subject ?? '', (string)$row['id']) !== false;
      $listCls    = $inList ? 'green' : 'red';
      ?>
      <div class="product-card"
           data-medie-tier="<?= $medieTier ?>"
           data-medie="<?= htmlspecialchars((string)$row['media_ultimului_admis'], ENT_QUOTES) ?>"
           data-sector="<?= htmlspecialchars((string)$row['zone'], ENT_QUOTES) ?>"
           data-codificare="<?= htmlspecialchars((string)$row['codificare'], ENT_QUOTES) ?>"
           style="animation-delay: .1s" draggable="true">
        <div class="card-image-wrap">
          <a href="liceu_page.php?id=<?php echo $row['id_numa_liceu']; ?>" >
            <img src="src/images/liceu/<?php echo $row['photo'] ?>"
                 alt="<?php echo $row['tip_scoala'] . ' ' . $row['nume_scoala'] ?>" loading="lazy">
            <div class="card-overlay"></div>
          </a>
        </div>
        <div class="card-body">
          <div class="card-category"><?= $row['zone'] ?></div>
          <div class="card-title">
            <a href="liceu_page.php?id=<?php echo $row['id_numa_liceu']; ?>" class="card-title-link">
              <?php echo $row['tip_scoala'] . ' ' . $row['nume_scoala'] ?>
            </a>
          </div>
          <div class="card-desc"><?= $row['short_description'] ?></div>

          <!-- Locuri + clase combined -->
          <div class="card-stats">
            <div class="stat-chip stat-chip-label">Locuri</div>
            <div class="stat-chip stat-chip-value">
              <?= $locuriNr ?><span class="stat-chip-unit"><?= $locuriTxt ?></span>
            </div>
            <div class="stat-chip stat-chip-sep">·</div>
            <div class="stat-chip stat-chip-value">
              <?= $claseNr ?><span class="stat-chip-unit"><?= $claseTxt ?></span>
            </div>
          </div>

          <!-- Specialization + mentiune + codificare as tag pills -->
          <div class="card-stats card-stats-tags">
            <div class="stat-chip stat-chip-tag"><?= $row['specializare'] ?></div>
            <?php if (!empty($row['mentiune']) && $row['mentiune'] !== '-'): ?>
              <div class="stat-chip stat-chip-tag stat-chip-tag-alt"><?= $row['mentiune'] ?></div>
            <?php endif; ?>
            <?php if (!empty($row['codificare'])): ?>
              <div class="stat-chip stat-chip-tag stat-chip-tag-code" title="Codificare">
                #<?= $row['codificare'] ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Medie + action button -->
          <div class="card-price-row">
            <div class="card-price-block">
              <div class="price-unit">Ultima medie</div>
              <div class="price-main">
                <?= $row['media_ultimului_admis'] > 0 ? $row['media_ultimului_admis'] : '—' ?>
              </div>
            </div>
            <div class="card-actions">
              <?php if (isset($_SESSION['ID'])): ?>
                <button class="add-btn <?= $listCls ?>" id="<?= $row['id'] ?>_lista"
                        itemid="<?= $row['id'] ?>" onClick="checkNrLista_y(this.id)">
                  <span class="btn-icon" aria-hidden="true">★</span>
                  <span class="btn-text">Lista mea</span>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php
    }
  } ?>

  <!-- Desktop-only absolute position; mobile CSS overrides via !important -->
  <span class="results-count" style="position: absolute; margin-top: -50px;">
    Afișez <strong id="countShown">
      <?php
      $pn = isset($_GET['pn']) ? (int)$_GET['pn'] : 1;
      $shown = $pn * $itemperpage;
      echo ($shown > $rows) ? $rows : $shown;
      ?>
    </strong> licee din <strong id="countTotal"><?= $rows ?></strong>
  </span>
</div>

<br>

<div style="width: 300%;" id="pagination_controls"><?php echo $paginationCtrls; ?></div>

<div class="empty-state" id="emptyState">
  <div class="empty-icon"></div>
  <h3>Niciun liceu găsit</h3>
  <p>Încearcă să ajustezi filtrele sau termenul de căutare.</p>
</div>
<div class="pagination" id="pagination"></div>

<?php if ($filename == 'licee_specializari_lista.php') { ?>
  <button class="add-btn red" id="<?= $_SESSION['ID']; ?>" onclick='sendListaEmail(this.id)'>
    Trimite-mi lista pe adresa mea de e-mail.
  </button>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<?php } ?>