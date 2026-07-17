<?php
include 'plugin/function.php';
ob_start(); 
session_start();
$pageTitle1 = 'High school';
include 'plugin/init.php';
global $con;
global $pageTitle1;
global $stmt1;
global $rows;
unset($_SESSION['pagename']);
unset($_SESSION['stylecss']);
$_SESSION['stylecss']  = 'liceu.css';
$_SESSION['pagename']  = '';

if (!function_exists('count_active_filters')) {
    function count_active_filters() {
        $n = 0;
        foreach (['profil', 'sector', 'specializare', 'bilingv'] as $k) {
            if (!empty($_GET[$k])) $n += count((array)$_GET[$k]);
        }
        if (!empty($_GET['searchInput'])) $n++;
        $minSet = isset($_GET['min_medie']) && $_GET['min_medie'] !== '' && (float)$_GET['min_medie'] > 0;
        $maxSet = isset($_GET['max_medie']) && $_GET['max_medie'] !== '' && (float)$_GET['max_medie'] < 10;
        if ($minSet || $maxSet) $n++;
        return $n;
    }
}
$active_filter_count = count_active_filters();

include 'template/header.php';
?>

<link rel="stylesheet" href="src/css/licee_general_mobile.css">

<div class="overlay" id="overlay"></div>

<div class="filter-drawer" id="filterDrawer">
  <div class="filter-drawer-backdrop" id="drawerBackdrop"></div>
  <div class="filter-drawer-panel" id="drawerPanel">
    <div class="drawer-header">
      <h2>Filtre</h2>
      <button class="drawer-close" id="drawerClose">✕</button>
    </div>
  </div>
</div>

<div class="toast" id="toast">✓ Adăugat la lista ta!</div>

<div class="page-wrapper">
  <div class="breadcrumb">
    <a href="index.php">Acasă</a>
    <span>›</span>
    <span>Toate Liceele</span>
  </div>

  <div class="shop-banner">
    <div>
      <h1 style='color:#fff;'>Toate Liceele din București</h1>
      <p style='color:#fff;'>Explorează, compară și adaugă la lista ta. Găsește liceul perfect.</p>
    </div>
    <div class="banner-badge">15+ Licee disponibile</div>
  </div>

  <div class="compare-bar sticky" id="compareBar" >
    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap ;margin-left: 93%;padding-top: 10px;">
      <span style="font-weight:900">Comparare:</span>
      <div class="compare-bar-names" id="compareBarNames"></div>
    </div>
    <button class="compare-go-btn" id="compareGoBtnLista">Compară acum →</button>
  </div>

  <div class="search-bar-wrap">
    <div class="search-input-wrap">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.35-4.35" />
      </svg>
      <input class="search-input" id="searchInput" name="searchInput" type="text" placeholder="Caută un liceu, profil sau specializare…">
    </div>
    <button class="mobile-filter-btn" id="mobileFilterBtn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M3 6h18M7 12h10M11 18h2" />
      </svg>
      Filtre
      <?php if ($active_filter_count > 0): ?>
        <span class="filter-count-badge" aria-label="filtre active"><?= $active_filter_count ?></span>
      <?php endif; ?>
    </button>
    <button class="search-btn" id="searchBtn">Caută</button>
  </div>

  <div class="shop-main">
    <form id="filterForm_y">
      <aside class="filter-sidebar" id="filterSidebar">
        <input type="text" name="mylist" value="1" hidden>
        <?php $selected_ptofiles = $_GET['profil'] ?? []; ?>
        <div class="filter-card">
          <div class="filter-card-header">
            <h3>Profil</h3>
            <div class="filter-toggle">▾</div>
          </div>
          <div class="filter-options">
            <label class="filter-option"><input name="profil[]" type="checkbox" value="Real" <?= in_array("Real", $selected_ptofiles) ? 'checked' : '' ?> class="filter-profil">
              <div class="check-box"></div><span class="filter-option-label">Real</span>
              <span class="filter-option-count"><?php echo profil('Real') ?></span>
            </label>
            <label class="filter-option"><input name="profil[]" type="checkbox" value="Umanist" <?= in_array("Umanist", $selected_ptofiles) ? 'checked' : '' ?> class="filter-profil">
              <div class="check-box"></div><span class="filter-option-label">Umanist</span><span
                class="filter-option-count"><?php echo profil('Umanist') ?> </span>
            </label>
            <label class="filter-option"><input name="profil[]" type="checkbox"
                value="Resurse naturale si Protecția mediului" <?= in_array("Resurse naturale si Protecția mediului", $selected_ptofiles) ? 'checked' : '' ?> class="filter-profil">
              <div class="check-box"></div><span class="filter-option-label">Resurse naturale</span><span
                class="filter-option-count"><?php echo profil('Resurse naturale si Protecția mediului') ?></span>
            </label>
            <label class="filter-option"><input name="profil[]" type="checkbox" value="Servicii" <?= in_array("Servicii", $selected_ptofiles) ? 'checked' : '' ?> class="filter-profil">
              <div class="check-box"></div><span class="filter-option-label">Servicii</span><span
                class="filter-option-count"><?php echo profil('Servicii') ?></span>
            </label>
            <label class="filter-option"><input name="profil[]" type="checkbox" value="Tehnic" <?= in_array("Tehnic", $selected_ptofiles) ? 'checked' : '' ?> class="filter-profil">
              <div class="check-box"></div><span class="filter-option-label">Tehnic</span><span
                class="filter-option-count"><?php echo profil('Tehnic') ?></span>
            </label>
          </div>
        </div>
        <?php $selected_sectors = $_GET['sector'] ?? []; ?>
        <div class="filter-card">
          <div class="filter-card-header">
            <h3>Sector</h3>
            <div class="filter-toggle">▾</div>
          </div>
          <div class="filter-options">
            <label class="filter-option"><input name="sector[]" type="checkbox" value="1" <?= in_array(1, $selected_sectors) ? 'checked' : '' ?> class="filter-sector">
              <div class="check-box"></div><span class="filter-option-label">Sector 1</span><span
                class="filter-option-count"><?= sector('Sector 1') ?></span>
            </label>
            <label class="filter-option"><input name="sector[]" type="checkbox" value="2" <?= in_array(2, $selected_sectors) ? 'checked' : '' ?> class="filter-sector">
              <div class="check-box"></div><span class="filter-option-label">Sector 2</span><span
                class="filter-option-count"><?= sector('Sector 2') ?></span>
            </label>
            <label class="filter-option"><input name="sector[]" type="checkbox" value="3" <?= in_array(3, $selected_sectors) ? 'checked' : '' ?> class="filter-sector">
              <div class="check-box"></div><span class="filter-option-label">Sector 3</span><span
                class="filter-option-count"><?= sector('Sector 3') ?></span>
            </label>
            <label class="filter-option"><input name="sector[]" type="checkbox" value="4" <?= in_array(4, $selected_sectors) ? 'checked' : '' ?> class="filter-sector">
              <div class="check-box"></div><span class="filter-option-label">Sector 4</span><span
                class="filter-option-count"><?= sector('Sector 4') ?></span>
            </label>
            <label class="filter-option"><input name="sector[]" type="checkbox" value="5" <?= in_array(5, $selected_sectors) ? 'checked' : '' ?> class="filter-sector">
              <div class="check-box"></div><span class="filter-option-label">Sector 5</span><span
                class="filter-option-count"><?= sector('Sector 5') ?></span>
            </label>
            <label class="filter-option"><input name="sector[]" type="checkbox" value="6" <?= in_array(6, $selected_sectors) ? 'checked' : '' ?> class="filter-sector">
              <div class="check-box"></div><span class="filter-option-label">Sector 6</span><span
                class="filter-option-count"><?= sector('Sector 6') ?></span>
            </label>
          </div>
        </div>

        <div class="filter-card">
          <div class="filter-card-header">
            <h3>Medie admitere</h3>
            <div class="filter-toggle">▾</div>
          </div>
          <div class="price-range-wrap">
            <div class="price-inputs">
              <input class="price-input" id="medieMin" type="number" name="min_medie"
                value="<?= isset($_GET['min_medie']) ? $_GET['min_medie'] : 0 ?>" min="0" max="10" step="0.1">
              <span class="price-sep">—</span>
              <input class="price-input" id="medieMax" type="number" name="max_medie"
                value="<?= isset($_GET['max_medie']) ? $_GET['max_medie'] : 10 ?>" min="5" max="10" step="0.1">
            </div>
          </div>
        </div>

        <div class="filter-card">
          <div class="filter-card-header">
            <h3>Specializarea</h3>
            <div class="filter-toggle">▾</div>
          </div>
          <div class="filter-options" style="max-height: none !important;">
              <?php
              $stmt1 = $con->prepare("SELECT * FROM " . DB_PREFIX . "specializare");
              $stmt1->execute(array());
              $row110 = $stmt1->fetchAll();
              $selectedSubjects = $_GET['specializare'] ?? [];
            foreach ($row110 as $row11) { ?>
                <label class="filter-option">
                    <input name="specializare[]" type="checkbox"  value="<?= htmlspecialchars($row11['description']) ?>"
                    <?= in_array($row11['description'], $selectedSubjects, true) ? 'checked' : '' ?>  class="filter-sector">
                    <div class="check-box"></div>
                    <span class="filter-option-label"><?= htmlspecialchars($row11['description']) ?></span>
                </label>
            <?php } ?>
          </div>
        </div>

        <div class="filter-card">
          <div class="filter-card-header">
            <h3>Bilingv</h3>
            <div class="filter-toggle">▾</div>
          </div>
          <div class="filter-options" style="max-height: none !important;">
              <?php
              $stmt1 = $con->prepare("SELECT * FROM " . DB_PREFIX . "limba");
              $stmt1->execute(array());
              $row110 = $stmt1->fetchAll();
              $selectedBilingv = $_GET['bilingv'] ?? [];
              foreach ($row110 as $row11) { ?>
                <label class="filter-option">
                    <input name="bilingv[]" type="checkbox"  value="<?= htmlspecialchars($row11['description']) ?>"
                    <?= in_array($row11['description'], $selectedBilingv, true) ? 'checked' : '' ?>  class="filter-sector">
                    <div class="check-box"></div>
                    <span class="filter-option-label"><?= htmlspecialchars($row11['description']) ?></span>
                </label>
              <?php } ?>
          </div>
        </div>
        <div class="clearfilterss"></div>
      </aside>
    </form>

    <div class="grid-area">
      <div class="active-filters" id="activePills"></div>

      <div class="sort-bar">
        <span class="results-count"></span>
        <div class="sort-controls">
          <span class="sort-label">Sortează:</span>
          <?php $sort = $_GET['sort'] ?? []; ?>
          <select class="sort-select" id="sortSelect" name="sort">
            <option value="default" <?= $sort === 'default' ? 'selected' : '' ?>>Recomandate</option>
            <option value="medie-desc" <?= $sort === 'medie-desc' ? 'selected' : '' ?>>Medie admitere ↓</option>
            <option value="medie-asc" <?= $sort === 'medie-asc' ? 'selected' : '' ?>>Medie admitere ↑</option>
            <option value="tip-desc" <?= $sort === 'tip-desc' ? 'selected' : '' ?>>Tip Liceu ↓</option>
            <option value="tip-asc" <?= $sort === 'tip-asc' ? 'selected' : '' ?>>Tip Liceu ↑</option>
            <option value="name-asc" <?= $sort === 'name-asc' ? 'selected' : '' ?>>Nume A–Z</option>
            <option value="name-desc" <?= $sort === 'name-desc' ? 'selected' : '' ?>>Nume Z–A</option>
          </select>

          <div class="view-toggle">
            <button class="view-btn active" id="gridViewBtn" title="Grid">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="3" width="8" height="8" rx="1" />
                <rect x="13" y="3" width="8" height="8" rx="1" />
                <rect x="3" y="13" width="8" height="8" rx="1" />
                <rect x="13" y="13" width="8" height="8" rx="1" />
              </svg>
            </button>
            <button class="view-btn" id="listViewBtn" title="Listă">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <section class="products-section" id="loading">
        <?php include 'template/paginationa.php'; ?>
      </section>
    </div>
  </div>
</div>

<?php include 'template/footer.php'; ?>
