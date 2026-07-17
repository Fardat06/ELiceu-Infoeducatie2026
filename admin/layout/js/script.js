const dashboardSidebar = document.getElementById("dashboardSidebar");
const userMenu = document.getElementById("userMenu");
const userMenuTrigger = document.getElementById("user-menu-trigger");
const userMenuDropdown = document.querySelector(".user-menu-dropdown");
const themeToggle = document.getElementById("theme-toggle");
const dashboardViews = document.querySelectorAll(".dashboard-view");
const dashboardNavItems = document.querySelectorAll(".dashboard-nav-item");
const dashboardTitle = document.getElementById("dashboardTitle");
const dashboardSidebarOverlay = document.getElementById("dashboardSidebarOverlay");
const searchContainer = document.getElementById("searchContainer");
const searchInput = document.getElementById("searchInput");
const searchClose = document.getElementById("searchClose");
const mobileSearchBtn = document.getElementById("mobileSearchBtn");
let sidebarCollapsed = false;
let currentView = "overview";
document.addEventListener("DOMContentLoaded", function () {
  initTheme();
  initThemeToggle();
  initSidebar();
  initUserMenu();
  initNavigation();
  initSearch();
  initCharts();
});
function initSidebar() {
  sidebarCollapsed = localStorage.getItem("dashboard-sidebar-collapsed") === "true";
  dashboardSidebar.classList.toggle("collapsed", sidebarCollapsed);
  document.querySelectorAll(".dashboard-sidebar-toggle").forEach((toggle) => {
    toggle.addEventListener("click", toggleSidebar);
  });
  dashboardSidebarOverlay?.addEventListener("click", closeSidebar);
}
function toggleSidebar() {
  sidebarCollapsed = !sidebarCollapsed;
  const isMobile = window.innerWidth <= 1024;
  if (isMobile) {
    const isOpen = dashboardSidebar.classList.contains("collapsed");
    dashboardSidebar.classList.toggle("collapsed", !isOpen);
    dashboardSidebarOverlay?.classList.toggle("active", !isOpen);
  } else {
    dashboardSidebar.classList.toggle("collapsed", sidebarCollapsed);
  }
  localStorage.setItem("dashboard-sidebar-collapsed", sidebarCollapsed.toString());
}
function closeSidebar() {
  if (window.innerWidth <= 1024) {
    dashboardSidebar.classList.remove("collapsed");
    dashboardSidebarOverlay?.classList.remove("active");
  }
}

function initUserMenu() {
  if (!userMenuTrigger || !userMenu) return;
  userMenuTrigger.addEventListener("click", (e) => {
    e.stopPropagation();
    userMenu.classList.toggle("active");
  });
  document.addEventListener("click", (e) => {
    if (!userMenu.contains(e.target)) {
      userMenu.classList.remove("active");
    }
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && userMenu.classList.contains("active")) {
      userMenu.classList.remove("active");
    }
  });
}
function initTheme() {
  // Load saved theme
  const savedTheme = localStorage.getItem("dashboard-theme") || "light";
  document.documentElement.setAttribute("data-theme", savedTheme);
  // Update theme toggle UI
  updateThemeToggleUI(savedTheme);
}
function initThemeToggle() {
  if (!themeToggle) return;
  themeToggle.querySelectorAll(".theme-option").forEach((option) => {
    option.addEventListener("click", (e) => {
      e.stopPropagation();
      setTheme(option.getAttribute("data-theme"));
    });
  });
}
function setTheme(theme) {
  document.documentElement.setAttribute("data-theme", theme);
  localStorage.setItem("dashboard-theme", theme);
  updateThemeToggleUI(theme);
}
function updateThemeToggleUI(theme) {
  if (!themeToggle) return;
  themeToggle.querySelectorAll(".theme-option").forEach((option) => {
    option.classList.toggle("active", option.getAttribute("data-theme") === theme);
  });
}

    function SearchFunction()
    {
      alert('ok');
    var serch = document.getElementById('myInputSearch').value;
    alert(serch);

    var URL="high_school.php?search="+serch;
    window.location.href= URL;
}


