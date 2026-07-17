        <header class="dashboard-header">
          <!-- Header Content -->
          <div class="dashboard-header-content">
            <button class="dashboard-sidebar-toggle">
              <span class="material-symbols-rounded">menu</span>
            </button>
            <h1 class="dashboard-header-title" id="dashboardTitle"><?=  lang('OVERVIEW') ?></h1>
          </div>
          <!-- Header Actions -->
          <div class="dashboard-header-actions">

            <!-- User Profile -->
            <div class="user-menu" id="userMenu">
              <div class="user-menu-trigger" id="user-menu-trigger">
                <div class="user-avatar-small">
                  <img src="layout/images/user_female.png?w=100&h=100&fit=crop&crop=face&auto=format" alt="User Avatar" />
                </div>
              </div>
              <div class="user-menu-dropdown">
                <a href="#" class="user-menu-item">
                  <span class="icon material-symbols-rounded">person</span>
                  <span>Profile</span>
                </a>
                <!-- Theme Toggle inside dropdown -->
                <div class="user-menu-item theme-item">
                  <span class="icon material-symbols-rounded">palette</span>
                  <div class="theme-toggle" id="theme-toggle">
                    <div class="theme-option active" data-theme="light">Light</div>
                    <div class="theme-option" data-theme="dark">Dark</div>
                  </div>
                </div>
                <a href="logout.php" class="user-menu-item">
                  <span class="icon material-symbols-rounded">logout</span>
                  <span>Sign Out</span>
                </a>
              </div>
            </div>
          </div>
        </header>
