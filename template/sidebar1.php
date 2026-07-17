      <aside class="dashboard-sidebar" id="dashboardSidebar">
        <div class="dashboard-brand">
          <button class="dashboard-sidebar-toggle">
            <span class="material-symbols-rounded">menu</span>
          </button>
          <a class="logo">My Dashboard</a>
        </div>
        <nav class="dashboard-nav">
          <div class="dashboard-nav-section">
            <a href="#" class="dashboard-nav-item active" data-view="overview">
              <span class="nav-icon material-symbols-rounded">school</span>
              <span class="nav-label"><?= lang('High school') ?></span>
            </a>
            <a href="high_school.php" class="dashboard-nav-item" data-view="projects">
              <span class="nav-icon material-symbols-rounded">folder</span>
              <span class="nav-label"><?= lang('High School Type') ?></span>
            </a>
            <a href="#" class="dashboard-nav-item" data-view="tasks">
              <span class="nav-icon material-symbols-rounded">checklist</span>
              <span class="nav-label"><?= lang('Profile') ?></span>
            </a>
            <a href="#" class="dashboard-nav-item" data-view="reports">
              <span class="nav-icon material-symbols-rounded">bar_chart</span>
              <span class="nav-label"><?= lang('Specialization') ?></span>
            </a>
            <a href="#" class="dashboard-nav-item" data-view="settings">
              <span class="nav-icon material-symbols-rounded">settings</span>
              <spam class="nav-label"><?= lang('Bilingual') ?></spam>
            </a>
          </div>
        </nav>
        <!-- Back to Site Button -->
        <div class="sidebar-footer">
          <a href="#" class="btn btn-secondary sidebar-back-button">
            <span class="material-symbols-rounded">home</span>
            <span class="btn-label">Back to Site</span>
          </a>
        </div>
      </aside>
