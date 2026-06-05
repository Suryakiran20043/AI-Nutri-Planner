// Premium organic toast notifications
function showToast(message, type = 'info') {
  const existing = document.querySelector('.toast');
  if (existing) existing.remove();

  const t = document.createElement('div');
  t.className = `toast toast-${type} animate-in`;
  
  // Custom icons based on types
  let icon = '<i class="ti ti-info-circle"></i>';
  if (type === 'success') icon = '<i class="ti ti-circle-check"></i>';
  if (type === 'error') icon = '<i class="ti ti-alert-circle"></i>';
  if (type === 'warning') icon = '<i class="ti ti-alert-triangle"></i>';

  t.innerHTML = `${icon} <span>${message}</span>`;
  document.body.appendChild(t);
  
  requestAnimationFrame(() => t.classList.add('visible'));
  
  setTimeout(() => {
    t.classList.remove('visible');
    setTimeout(() => t.remove(), 300);
  }, 4000);
}

// Sidebar active state highlights
document.addEventListener('DOMContentLoaded', () => {
  const currentPage = window.location.pathname;
  document.querySelectorAll('.nav-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href) {
      const pageName = href.split('/').pop().replace('.php', '');
      if (currentPage.includes(pageName)) {
        link.classList.add('active');
      }
    }
  });

  // Mobile sidebar toggle trigger
  document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('open');
  });
  
  // Close mobile sidebar when clicking main content
  document.querySelector('.main')?.addEventListener('click', () => {
    document.querySelector('.sidebar')?.classList.remove('open');
  });

  // Global search input focus by hotkey `/`
  document.addEventListener('keydown', (e) => {
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'SELECT' && document.activeElement.tagName !== 'TEXTAREA') {
      e.preventDefault();
      document.getElementById('search-input')?.focus();
    }
  });

  // Intercept settings link clicks globally to trigger dynamic slide-in drawer
  document.addEventListener('click', (e) => {
    const link = e.target.closest('a[href*="settings.php"]');
    if (link) {
      e.preventDefault();
      if (typeof openSettingsDrawer === 'function') {
        openSettingsDrawer();
      }
    }
  });
});
