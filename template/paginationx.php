<?php
ob_start();

$urllink = $_SERVER['REQUEST_URI'];
$path = parse_url($urllink, PHP_URL_PATH);
$filename = basename($path);

$pageTitle1 = 'High school';
if (!isset($con)) {
    require_once('../plugin/config.php');
    require_once '../plugin/function.php';
    require_once '../plugin/init.php';
    global $con;
}
global $where;
global $and;
if (isset($_SESSION['ID'])){
    $stmtx = $con->prepare("SELECT lista_g FROM " . DB_PREFIX . "user_details  WHERE id = ? ");
    $stmtx->execute(array($_SESSION['ID']));
    $row1 = $stmtx->fetch();
    $subject = $row1['lista_g'] ;
}

$stmt = "SELECT DISTINCT hnl.*, h2.avg_medie
FROM  " . DB_PREFIX . "numa_liceu hnl
INNER JOIN  " . DB_PREFIX . "liceu hl ON hnl.name = hl.name
JOIN (
    SELECT name, MIN(u_medie_2025)AS avg_medie
    FROM  " . DB_PREFIX . "medie
    WHERE stopx = 0 AND u_medie_2025 >0
    GROUP BY name
) AS h2 ON hnl.name = h2.name
WHERE hnl.stopx = 0";
$params = [];
if ( $filename=='licee_general_lista.php') {
    if ($subject!=''){
        $stmt .= " AND hnl.id_numa_liceu IN (" . $subject  . ")";
    }else{
        $stmt .= " AND hnl.id_numa_liceu =''";
    }
}

if (!empty($_GET['profil'])) {
    $profilPlaceholders = [];
    foreach ($_GET['profil'] as $index => $profil) {
        $key = "'".$profil."'";
        $profilPlaceholders[] = $key;
       // $params[$key] = $profil;
    }
    $stmt .= " AND hl.profil IN (" . implode(",", $profilPlaceholders) . ")";
}

if (!empty($_GET['sector'])) {
    $sectorPlaceholders = [];
    foreach ($_GET['sector'] as $index => $sector) {
        $key = "'Sector " . $sector . "'";
        $sectorPlaceholders[] = $key;
        $params[$key] = $sector;
    }
    $stmt .= " AND hnl.zone IN (" . implode(",", $sectorPlaceholders) . ")";
}

if(isset($_GET['specializare'])) {
    $profilPlaceholders = [];
    foreach ($_GET['specializare'] as $index => $specializare) {
        $key = "'".$specializare."'";
        $profilPlaceholders[] = $key;
        $params[$key] = $specializare ;
    }
    $stmt .= " AND h2.specializare IN (" . implode(",", $profilPlaceholders) . ")";
}

if(isset($_GET['bilingv'])) {
    $profilPlaceholders = [];
    foreach ($_GET['bilingv'] as $index => $bilingv) {
        $key = "'".$bilingv."'";
        $profilPlaceholders[] = $key;
        $params[$key] = $bilingv ;
    }
    $stmt .= " AND h2.bilingv IN (" . implode(",", $profilPlaceholders) . ")";
}

if (!empty($_GET['searchInput'])) {
    $search = '%' . $_GET['searchInput'] . '%';
    $stmt .= " AND (hnl.name LIKE '$search' OR hnl.tip LIKE '$search')";
}

if (isset($_GET['min_medie']) && isset($_GET['max_medie'])) {
    $min = $_GET['min_medie'];
    $max = $_GET['max_medie'];
    $lo = min($min, $max);
    $hi = max($min, $max);
    $stmt .= " AND h2.avg_medie BETWEEN $lo AND $hi";
}

$sort = $_GET['sort'] ?? 'default';
$order = match($sort) {
    'medie-desc' => 'ORDER BY  h2.avg_medie DESC',
    'medie-asc'  => 'ORDER BY  h2.avg_medie ASC',
    'tip-desc'   => 'ORDER BY  hnl.tip DESC',
    'tip-asc'    => 'ORDER BY  hnl.tip ASC',
    'name-asc'   => 'ORDER BY  hnl.name ASC',
    'name-desc'  => 'ORDER BY  hnl.name DESC',
    default      => 'ORDER BY  hnl.name ASC'
};
$stmt .= " $order";
$stmt2 = $con->prepare($stmt);
$stmt2->execute();
$row  = $stmt2->fetchAll();
$rows = $stmt2->rowCount();

$page_rows = 9;
$last = ceil($rows / $page_rows);
if ($last < 1) $last = 1;

$pagenum = 1;
if (isset($_GET['pn'])) {
    $pagenum = preg_replace('#[^0-9]#', '', $_GET['pn']);
}
if ($pagenum < 1)        $pagenum = 1;
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
    $baseUrl = 'licee_general.php?' . http_build_query($currentParams);
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
            $medieVal   = is_numeric($row['avg_medie']) ? (float)$row['avg_medie'] : 0;
            $medieTier  = $medieVal >= 9   ? 'gold'
                        : ($medieVal >= 8   ? 'high'
                        : ($medieVal >= 7   ? 'mid'
                        : 'low'));
            $locuriNr   = place($row['name']);
            $locuriTxt  = (is_numeric($locuriNr) && (int)$locuriNr === 1) ? 'loc' : 'locuri';
        ?>
          <div class="product-card"
               data-medie-tier="<?= $medieTier ?>"
               data-medie="<?= htmlspecialchars((string)$row['avg_medie'], ENT_QUOTES) ?>"
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
              <div class="card-desc"><?= $row['short_description'] ?></div>

              <div class="card-stats">
                <div class="stat-chip stat-chip-label">Nr de Locuri</div>
                <div class="stat-chip stat-chip-value">
                  <?= $locuriNr ?>
                  <span class="stat-chip-unit"><?= $locuriTxt ?></span>
                </div>
              </div>

              <div class="card-price-row">
                <div class="card-price-block">
                  <div class="price-unit">Medie admitere</div>
                  <div class="price-main"><?php echo $row['avg_medie'] ?></div>
                </div>
                <div class="card-actions">
                  <button class="add-btn red" id="<?= $row['id_numa_liceu'] ?>" onClick="checkNr(this.id)">
                    <span class="btn-icon" aria-hidden="true">⇄</span>
                    <span class="btn-text">Compară</span>
                  </button>
                  <?php if (isset($_SESSION['ID'])):
                      $inList = strpos($subject ?? '', $row['id_numa_liceu']) !== false;
                      $cls    = $inList ? 'green' : 'red';
                  ?>
                    <button class="add-btn <?= $cls ?>" id="<?= $row['id_numa_liceu'] ?>_lista"
                            itemid="<?= $row['id_numa_liceu'] ?>" onClick="checkNrLista_x(this.id)">
                      <span class="btn-icon" aria-hidden="true">★</span>
                      <span class="btn-text">Lista mea</span>
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php }
        if (isset($_COOKIE['compareIdsx'])) {
            $userRole = htmlspecialchars($_COOKIE['compareIdsx']);
        }
        ?>

        <!-- Inline styles here are for desktop positioning; mobile CSS
             overrides both via !important, so they're a no-op on ≤680px. -->
        <span class="results-count" style="position: absolute; margin-top: -50px;">
          Afișez <strong id="countShown">
            <?php
            $pn = isset($_GET['pn']) ? $_GET['pn'] : 1;
            if ($pn * 9 > $rows) echo $rows;
            else                  echo $pn * 9;
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
      <button class="compare-go-btn" id="compareGoBtn">Compară Acum</button>
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