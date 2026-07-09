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
      const pageName = href.split('/').pop().replace('', '');
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
    const link = e.target.closest('a[href*="settings"]');
    if (link) {
      e.preventDefault();
      if (typeof openSettingsDrawer === 'function') {
        openSettingsDrawer();
      }
    }
  });
});

// Global Recipe Popover Logic
function initGlobalPopover() {
  if (!document.getElementById('global-recipe-popover')) {
    const p = document.createElement('div');
    p.id = 'global-recipe-popover';
    document.body.appendChild(p);
  }
}

window.showRecipePopover = function(e, name, instructions) {
  initGlobalPopover();
  const popover = document.getElementById('global-recipe-popover');
  let stepsHtml = '';
  
  if (!instructions) instructions = "Eat fresh as served or prepare according to your preference.";
  
  // Try to extract steps
  let rawSteps = [];
  if (instructions.includes('INSTRUCTIONS:\n')) {
    rawSteps = instructions.split('INSTRUCTIONS:\n')[1].split('\n').filter(s => s.trim());
  } else if (Array.isArray(instructions)) {
    rawSteps = instructions;
  } else {
    rawSteps = instructions.split('\n').filter(s => s.trim());
  }
  
  if (rawSteps.length === 0) {
    rawSteps = ["Eat fresh as served or prepare according to your preference."];
  }
  
  // Show up to 3 steps in the preview
  const previewSteps = rawSteps.slice(0, 3);
  stepsHtml = previewSteps.map((s, i) => `<div class="popover-step"><strong>Step ${i+1}:</strong> ${s}</div>`).join('');
  if (rawSteps.length > 3) {
    stepsHtml += `<div style="text-align:center; color:var(--sage); font-weight:600; font-size:10px; margin-top:4px;">+ ${rawSteps.length - 3} more steps (Click to view full recipe)</div>`;
  }

  popover.innerHTML = `<h4>${name}</h4><div style="margin-top:8px;">${stepsHtml}</div>`;
  
  // Position the popover near the cursor but ensure it stays on screen
  const popWidth = 320;
  const popHeight = 250;
  let x = e.clientX + 15;
  let y = e.clientY + 15;
  
  if (x + popWidth > window.innerWidth) x = window.innerWidth - popWidth - 10;
  if (y + popHeight > window.innerHeight) y = e.clientY - popHeight - 15;
  
  popover.style.left = x + 'px';
  popover.style.top = y + 'px';
  popover.classList.add('visible');
}

window.hideRecipePopover = function() {
  const popover = document.getElementById('global-recipe-popover');
  if(popover) popover.classList.remove('visible');
}


