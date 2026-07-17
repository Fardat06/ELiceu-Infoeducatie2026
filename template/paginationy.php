<?php
$pageTitle1 = 'HOME';
if (!isset($con)) {
    require_once('../plugin/config.php');
    require_once '../plugin/function.php';
    require_once '../plugin/init.php';
    global $con;
}
global $where;
global $and;

$stmt = "SELECT h1.* , h2.u_medie_2025, h2.p_medie_2025 , h3.photo , h3.id_numa_liceu , h4.locuri_2025
 FROM " . DB_PREFIX . "liceu h1
INNER JOIN " . DB_PREFIX . "medie h2 ON h1.id = h2.id_medie
INNER JOIN " . DB_PREFIX . "numa_liceu  h3 ON h1.name = h3.name
INNER JOIN " . DB_PREFIX . "locuri  h4 ON h1.id = h4.id_locuri

WHERE h1.stopx = 0";

$params = [];

if (!empty($_GET['profil'])) {
    $profilPlaceholders = [];
    foreach ($_GET['profil'] as $index => $profil) {
        $key = "'".$profil."'";
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
    $stmt .= " AND h1.zone IN (" . implode(",", $sectorPlaceholders) . ")";
}

if(isset($_GET['specializare'])) {
    $profilPlaceholders = [];
    foreach ($_GET['specializare'] as $index => $specializare) {
        $key = "'".$specializare."'";
        $profilPlaceholders[] = $key;
        $params[$key] = $specializare ;
    }
    $stmt .= " AND h1.specializare IN (" . implode(",", $profilPlaceholders) . ")";
}

if(isset($_GET['bilingv'])) {
    $profilPlaceholders = [];
    foreach ($_GET['bilingv'] as $index => $bilingv) {
        $key = "'".$bilingv."'";
        $profilPlaceholders[] = $key;
        $params[$key] = $bilingv ;
    }
    $stmt .= " AND h1.bilingv IN (" . implode(",", $profilPlaceholders) . ")";
}

if (!empty($_GET['searchInput'])) {
    $search = '%' . $_GET['searchInput'] . '%';
    $stmt .= " AND (h1.name LIKE '$search' OR h1.tip LIKE '$search')";
}

if (isset($_GET['min_medie']) && isset($_GET['max_medie'])) {
    $min = $_GET['min_medie'];
    $max = $_GET['max_medie'];
    $stmt .= " AND h2.u_medie_2025 BETWEEN $min AND $max";
}

$sort = $_GET['sort'] ?? 'default';
$order = match($sort) {
    'medie-desc' => 'ORDER BY  h2.u_medie_2025 DESC',
    'medie-asc'  => 'ORDER BY  h2.u_medie_2025 ASC',
    'tip-desc'   => 'ORDER BY  h1.tip DESC',
    'tip-asc'    => 'ORDER BY  h1.tip ASC',
    'name-asc'   => 'ORDER BY  h1.name ASC',
    'name-desc'  => 'ORDER BY  h1.name DESC',
    default      => 'ORDER BY  h1.name ASC'
};
$stmt .= " $order";

$stmt2 = $con->prepare($stmt);
$stmt2->execute();
$row  = $stmt2->fetchAll();
$rows = $stmt2->rowCount();

$page_rows = 24;
$last = ceil($rows / $page_rows);
if ($last < 1) $last = 1;

$pagenum = 1;
if (isset($_GET['pn'])) {
    // Fix: preg_replace strips anything that isn't a digit
    $pagenum = (int) preg_replace('#[^0-9]#', '', $_GET['pn']);
}
if ($pagenum < 1)          $pagenum = 1;
else if ($pagenum > $last) $pagenum = $last;

$limit = 'LIMIT ' . ($pagenum - 1) * $page_rows . ',' . $page_rows;
$stmt3 = $stmt . " " . $limit;
$stmt1 = $con->prepare($stmt3);
$stmt1->execute();


/* PAGINATION CONTROLS
 * .pg-prev / .pg-next → Prev/Next
 * .pg-num → numeric page links
 * .pg-current → current page indicator
 * All also carry .table_btn so existing desktop styles apply.
 */
$paginationCtrls = '';
if ($last != 1) {
    $currentParams = $_GET;
    unset($currentParams['pn']);
    $baseUrl = 'licee_specializari.php?' . http_build_query($currentParams);
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
        <?php
        $rows1 = $stmt1->fetchAll();
        foreach ($rows1 as $row) {
            $medieVal   = is_numeric($row['u_medie_2025']) ? (float)$row['u_medie_2025'] : 0;
            $medieTier  = $medieVal >= 9   ? 'gold'
                        : ($medieVal >= 8   ? 'high'
                        : ($medieVal >= 7   ? 'mid'
                        : 'low'));
            $locuriNr  = $row['locuri_2025'];
            $locuriTxt = (is_numeric($locuriNr) && (int)$locuriNr === 1) ? 'loc' : 'locuri';
        ?>
          <div class="product-card"
               data-medie-tier="<?= $medieTier ?>"
               data-medie="<?= htmlspecialchars((string)$row['u_medie_2025'], ENT_QUOTES) ?>"
               data-sector="<?= htmlspecialchars((string)$row['zone'], ENT_QUOTES) ?>"
               style="animation-delay: .1s">
            <div class="card-image-wrap">
              <a href="liceu_page.php?id=<?php echo $row['id_numa_liceu']; ?>">
                <img src="src/images/liceu/<?php echo $row['photo'] ?>"
                     alt="<?php echo $row['tip'] . ' ' . $row['name'] ?>" loading="lazy">
                <div class="card-overlay"></div>
              </a>
            </div>
            <div class="card-body">
              <div class="card-category"><?= $row['zone'] ?></div>
              <div class="card-title">
                <a href="liceu_page.php?id=<?php echo $row['id_numa_liceu']; ?>" class="card-title-link">
                  <?php echo $row['tip'] . ' ' . $row['name'] ?>
                </a>
              </div>
              <div class="card-desc">
                Tradiție academică de excepție, cu performanțe remarcabile la matematică și informatică.
                Locul de formare al multor olimpici naționali și internaționali.
              </div>

              <div class="card-stats">
                <div class="stat-chip stat-chip-label">Nr de Locuri</div>
                <div class="stat-chip stat-chip-value">
                  <?= $locuriNr ?>
                  <span class="stat-chip-unit"><?= $locuriTxt ?></span>
                </div>
              </div>

              <div class="card-stats card-stats-tags">
                <div class="stat-chip stat-chip-label">Specializare</div>
                <div class="stat-chip stat-chip-tag"><?= $row['specializare'] ?></div>
                <?php if ($row['intesiv'] != 'nu'): ?>
                  <div class="stat-chip stat-chip-tag stat-chip-tag-alt"><?= $row['intesiv'] ?></div>
                <?php endif; ?>
                <?php if (!empty($row['bilingv']) && $row['bilingv'] !== '-'): ?>
                  <div class="stat-chip stat-chip-tag stat-chip-tag-alt"><?= $row['bilingv'] ?></div>
                <?php endif; ?>
              </div>

              <div class="card-price-row">
                <div class="card-price-block">
                  <div class="price-unit">Prima–ultima medie</div>
                  <div class="price-main range"><?= $row['p_medie_2025'] . ' - ' . $row['u_medie_2025'] ?></div>
                </div>
                <div class="card-actions">
                  <button class="add-btn red" id="<?= $row['id'] ?>" onClick="checkNr(this.id)">
                    <span class="btn-icon" aria-hidden="true">⇄</span>
                    <span class="btn-text">Compară</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>

        <!-- Desktop-only absolute position; mobile CSS overrides -->
        <span class="results-count" style="position: absolute; margin-top: -50px;">
          Afișez <strong id="countShown">
            <?php
            $pn = isset($_GET['pn']) ? (int)$_GET['pn'] : 1;
            $shown = $pn * $page_rows;
            echo ($shown > $rows) ? $rows : $shown;
            ?>
          </strong> licee din <strong id="countTotal"><?= $rows ?></strong>
        </span>
      </div>

  <!-- COMPARE BAR -->
  <div class="compare-panel" id="compareBar">
    <div class="compare-panel-header">
      <span>Compară</span>
      <button class="compare-toggle" id="compareToggle" aria-label="Restrânge">&#9662;</button>
    </div>
    <div class="compare-panel-body" id="compareBarNames"></div>
    <div class="compare-panel-footer">
      <button class="compare-go-btn" id="compareGoBtnLista">Compară Acum</button>
    </div>
  </div>

  <br>
  <div style="width: 300%;" id="pagination_controls"><?php echo $paginationCtrls; ?></div>

  <div class="empty-state" id="emptyState">
    <div class="empty-icon"></div>
    <h3>Niciun liceu găsit</h3>
    <p>Încearcă să ajustezi filtrele sau termenul de căutare.</p>
  </div>
  <div class="pagination" id="pagination"></div>