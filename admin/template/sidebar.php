<?php

$currentPage = basename($_SERVER['PHP_SELF']);
if (!function_exists('navActive')) {
    function navActive(string $file, string $current): string
    {
        return $file === $current ? ' active' : '';
    }
}

if (!function_exists('lang')) {
    function lang($k) { return $k; }
}

$navItems = [
    ['file' => 'dashboard.php',   'icon' => 'dashboard',   'label' => 'Dashboard',        'view' => 'overview'],
    ['file' => 'numa_liceu.php',  'icon' => 'school',      'label' => 'High school',      'view' => 'licee'],
    ['file' => 'high_school.php', 'icon' => 'folder',      'label' => 'High School Type', 'view' => 'tip'],
    ['file' => 'profil.php',      'icon' => 'checklist',   'label' => 'Profile',          'view' => 'profil'],
    ['file' => 'specializare.php', 'icon' => 'book_2', 'label' => 'Specialization', 'view' => 'spec'],
    ['file' => 'limba.php', 'icon' => 'translate', 'label' => 'Languages', 'view' => 'limba'],
    ['file' => 'bilingv.php',     'icon' => 'translate',   'label' => 'Bilingual',        'view' => 'bilingv'],
    ['file' => 'admitere.php',    'icon' => 'trending_up', 'label' => 'Admission',        'view' => 'admitere'],
    ['file' => 'liceu_data.php', 'icon' => 'dataset', 'label' => 'School Data', 'view' => 'liceudata'],
    ['file' => 'pdftoexecl.php', 'icon' => 'picture_as_pdf', 'label' => 'PDF to Excel', 'view' => 'pdftoexcel'],
    ['file' => 'review.php', 'icon' => 'reviews', 'label' => 'Reviews', 'view' => 'recenzii'],
    ['file' => 'users.php',       'icon' => 'group',       'label' => 'Users',            'view' => 'users'],
    ['file' => 'settings.php', 'icon' => 'settings', 'label' => 'Settings', 'view' => 'setari'],
];
?>
<aside class="dashboard-sidebar" id="dashboardSidebar">

  <div class="dashboard-brand">
    <button class="dashboard-sidebar-toggle" type="button" aria-label="Meniu">
      <span class="material-symbols-rounded">menu</span>
    </button>
    <a class="logo" href="dashboard.php">Ǝliceu Admin</a>
  </div>

  <nav class="dashboard-nav">
    <div class="dashboard-nav-section">
      <?php foreach ($navItems as $item): ?>
        <a href="<?= htmlspecialchars($item['file'], ENT_QUOTES, 'UTF-8') ?>"
           class="dashboard-nav-item<?= navActive($item['file'], $currentPage) ?>"
           data-view="<?= htmlspecialchars($item['view'], ENT_QUOTES, 'UTF-8') ?>">
          <span class="nav-icon material-symbols-rounded"><?= $item['icon'] ?></span>
          <span class="nav-label"><?= htmlspecialchars(lang($item['label']), ENT_QUOTES, 'UTF-8') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </nav>

  <div class="sidebar-footer">
    <a href="../index.php" class="btn btn-secondary sidebar-back-button">
      <span class="material-symbols-rounded">home</span>
      <span class="btn-label"><?= htmlspecialchars(lang('Back to Site'), ENT_QUOTES, 'UTF-8') ?></span>
    </a>
    <a href="logout.php" class="btn btn-secondary sidebar-back-button">
      <span class="material-symbols-rounded">logout</span>
      <span class="btn-label"><?= htmlspecialchars(lang('Logout'), ENT_QUOTES, 'UTF-8') ?></span>
    </a>
  </div>
</aside>
