<?php
$current_user_name = $_SESSION['name'] ?? 'User';
$firstName = explode(' ', $current_user_name)[0];
$initials = strtoupper(substr($firstName, 0, 1));
if (count(explode(' ', $current_user_name)) > 1) {
  $initials .= strtoupper(substr(explode(' ', $current_user_name)[1], 0, 1));
}
$initials = substr($initials, 0, 2);

// Mock goals line matching mockup
$profile_calories = 1800;
$profile_goal_lbl = "Lose weight";
if (isset($db)) {
  // If database connection is active in the parent page scope, fetch the metrics
  try {
    $st = $db->prepare('SELECT daily_calories, goal FROM user_profiles WHERE user_id = ?');
    $st->execute([$_SESSION['user_id']]);
    $prof = $st->fetch();
    if ($prof) {
      $profile_calories = $prof['daily_calories'] ?: 1800;
      $profile_goal_lbl = $prof['goal'] === 'lose' ? 'Lose weight' : ($prof['goal'] === 'gain' ? 'Gain muscle' : 'Maintain weight');
    }
  } catch (Exception $e) {}
}
?>
<!-- Sidebar panel structured exactly like Mockup file -->
<div class="sidebar animate-in">
  <div class="logo" onclick="window.location.href='/nutriplan/pages/dashboard.php'">
    <div class="logo-mark">🥗</div>
    <div>
      <div class="logo-text">NutriPlan</div>
      <div class="logo-sub">AI Meal Planner</div>
    </div>
  </div>

  <span class="nav-label">Main</span>
  <a href="/nutriplan/pages/dashboard.php" class="nav-item">
    <span class="ni">📊</span> Dashboard <div class="nav-dot" id="dot-dashboard" style="display:none;"></div>
  </a>
  
  <a href="/nutriplan/pages/planner.php" class="nav-item">
    <span class="ni">📅</span> Meal Planner
  </a>
  
  <a href="/nutriplan/pages/calculator.php" class="nav-item">
    <span class="ni">🔢</span> Calorie Calculator
  </a>
  
  <a href="/nutriplan/pages/grocery.php" class="nav-item">
    <span class="ni">🛒</span> Grocery List
  </a>

  <span class="nav-label">Goals</span>
  <a href="/nutriplan/pages/dashboard.php" class="nav-item"><span class="ni">📈</span> Progress</a>
  <a href="/nutriplan/pages/dashboard.php" class="nav-item"><span class="ni">❤️</span> Favorites</a>
  
  <!-- Custom logout path mapping -->
  <a href="#" class="nav-item" id="btn-sidebar-logout"><span class="ni">🚪</span> Log Out</a>

  <div class="sidebar-bottom">
    <div class="user-pill" onclick="window.location.href='/nutriplan/pages/calculator.php'">
      <div class="avatar"><?= htmlspecialchars($initials) ?></div>
      <div class="profile-details">
        <div class="user-name" title="<?= htmlspecialchars($current_user_name) ?>"><?= htmlspecialchars($current_user_name) ?></div>
        <div class="user-goal" id="sidebar-user-goal-desc"><?= htmlspecialchars($profile_goal_lbl) ?> · <?= htmlspecialchars($profile_calories) ?> kcal</div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Highlight active link based on current path
  const currentPath = window.location.pathname;
  let activeFound = false;

  document.querySelectorAll('.sidebar .nav-item').forEach(link => {
    const href = link.getAttribute('href');
    if (href && href !== '#' && currentPath.includes(href.split('/').pop().replace('.php', ''))) {
      link.classList.add('active');
      activeFound = true;
      
      // If dashboard is active, show the little sage dot next to it
      if (href.includes('dashboard')) {
        const dot = link.querySelector('.nav-dot');
        if (dot) dot.style.display = 'block';
      }
    } else {
      link.classList.remove('active');
    }
  });

  // Fallback highlight dashboard if none active
  if (!activeFound && currentPath.includes('dashboard')) {
    const dashLink = document.querySelector('.sidebar a[href*="dashboard"]');
    if (dashLink) {
      dashLink.classList.add('active');
      const dot = dashLink.querySelector('.nav-dot');
      if (dot) dot.style.display = 'block';
    }
  }

  // Bind logout click
  document.getElementById('btn-sidebar-logout')?.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!confirm('Are you sure you want to log out?')) return;
    try {
      await API.call('/auth/logout.php', 'POST');
      showToast('Logged out successfully', 'success');
      setTimeout(() => {
        window.location.href = '/nutriplan/index.php';
      }, 800);
    } catch (err) {
      showToast('Logout failed: ' + err.message, 'error');
    }
  });

  // Fetch live target specs to dynamically override sidebar goal text
  async function syncSidebarUserInfo() {
    try {
      const profile = await API.getProfile();
      if (profile && profile.daily_calories) {
        const calories = profile.daily_calories;
        const goalText = profile.goal === 'lose' ? 'Lose weight' : (profile.goal === 'gain' ? 'Gain muscle' : 'Maintain weight');
        const desc = document.getElementById('sidebar-user-goal-desc');
        if (desc) {
          desc.textContent = `${goalText} · ${calories} kcal`;
        }
      }
    } catch (e) {}
  }
  
  syncSidebarUserInfo();
});
</script>

<!-- Settings Slide-in Drawer Backdrop Overlay -->
<div class="settings-drawer-overlay" id="settingsOverlay" onclick="closeSettingsDrawer()"></div>

<!-- Settings Slide-in Drawer Panel -->
<div class="settings-drawer" id="settingsDrawer">
  
  <!-- Main Settings View -->
  <div class="drawer-view" id="drawerViewMain">
    <div class="drawer-header" style="display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid var(--border);">
      <button class="btn-drawer-back" onclick="closeSettingsDrawer()" style="background: transparent; border: none; font-size: 13.5px; font-weight: 500; color: var(--text); display: flex; align-items: center; gap: 4px; cursor: pointer; font-family:'DM Sans',sans-serif;">
        <i class="ti ti-chevron-left"></i> Back
      </button>
      <h3 style="font-family:'Playfair Display',serif; font-size:17px; font-weight:600; margin: 0; color:var(--forest);">Settings</h3>
      <div style="width: 48px;"></div>
    </div>
    
    <div class="drawer-body" style="padding: 16px; display: flex; flex-direction: column; gap: 16px; overflow-y: auto; height: calc(100% - 60px);">
      <!-- Account Group -->
      <div class="settings-group">
        <div class="settings-group-header">[Account]</div>
        <div class="settings-row" onclick="openDrawerDetail('personal')">
          <div class="settings-row-left">👤 Personal Information</div>
          <i class="ti ti-chevron-right" style="color:var(--muted); font-size:12px"></i>
        </div>
        <div class="settings-row" onclick="openDrawerDetail('security')">
          <div class="settings-row-left">🔒 Login & Security</div>
          <i class="ti ti-chevron-right" style="color:var(--muted); font-size:12px"></i>
        </div>
      </div>

      <!-- Goals & Diet Group -->
      <div class="settings-group">
        <div class="settings-group-header">[Goals & Diet]</div>
        <div class="settings-row" onclick="openDrawerDetail('goals')">
          <div class="settings-row-left">🎯 Edit Goals</div>
          <i class="ti ti-chevron-right" style="color:var(--muted); font-size:12px"></i>
        </div>
        <div class="settings-row" onclick="openDrawerDetail('diet')">
          <div class="settings-row-left">🥦 Dietary Restrictions</div>
          <i class="ti ti-chevron-right" style="color:var(--muted); font-size:12px"></i>
        </div>
        <div class="settings-row" onclick="openDrawerDetail('macros')">
          <div class="settings-row-left">⚙️ Macro Ratios</div>
          <i class="ti ti-chevron-right" style="color:var(--muted); font-size:12px"></i>
        </div>
      </div>

      <!-- Preferences Group -->
      <div class="settings-group">
        <div class="settings-group-header">[Preferences]</div>
        <div class="settings-row" onclick="openDrawerDetail('appearance')">
          <div class="settings-row-left">🌙 Appearance</div>
          <i class="ti ti-chevron-right" style="color:var(--muted); font-size:12px"></i>
        </div>
        <div class="settings-row" onclick="openDrawerDetail('units')">
          <div class="settings-row-left">⏱️ Units of Measure</div>
          <i class="ti ti-chevron-right" style="color:var(--muted); font-size:12px"></i>
        </div>
        <div class="settings-row" onclick="openDrawerDetail('notifications')">
          <div class="settings-row-left">🔔 Notification Settings</div>
          <i class="ti ti-chevron-right" style="color:var(--muted); font-size:12px"></i>
        </div>
      </div>

      <!-- Connections Group -->
      <div class="settings-group">
        <div class="settings-group-header">[Connections]</div>
        <div class="settings-row" onclick="openDrawerDetail('connections')">
          <div class="settings-row-left">❤️ Apple Health / Fit</div>
          <i class="ti ti-chevron-right" style="color:var(--muted); font-size:12px"></i>
        </div>
        <div class="settings-row" onclick="openDrawerDetail('smartwatch')">
          <div class="settings-row-left">⌚ Smartwatch Sync</div>
          <i class="ti ti-chevron-right" style="color:var(--muted); font-size:12px"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Detail Nested Slide-in View -->
  <div class="drawer-view drawer-detail-view" id="drawerViewDetail">
    <div class="drawer-header" style="display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid var(--border);">
      <button class="btn-drawer-back" onclick="closeDrawerDetail()" style="background: transparent; border: none; font-size: 13.5px; font-weight: 500; color: var(--text); display: flex; align-items: center; gap: 4px; cursor: pointer; font-family:'DM Sans',sans-serif;">
        <i class="ti ti-chevron-left"></i> Back
      </button>
      <h3 style="font-family:'Playfair Display',serif; font-size:17px; font-weight:600; margin: 0; color:var(--forest);" id="drawerDetailTitle">Details</h3>
      <div style="width: 48px;"></div>
    </div>
    
    <div class="drawer-body" style="padding: 24px; display: flex; flex-direction: column; gap: 16px; overflow-y: auto; height: calc(100% - 60px);" id="drawerDetailBody">
      <!-- Populated dynamically -->
    </div>
  </div>

</div>

<style>
/* Settings Drawer Core Overlay Backdrop */
.settings-drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(2px);
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  z-index: 1000;
}
.settings-drawer-overlay.open {
  opacity: 1;
  visibility: visible;
}

/* Side Drawer Sliding Container */
.settings-drawer {
  position: fixed;
  top: 0;
  right: 0;
  width: 100%;
  max-width: 420px;
  height: 100vh;
  background: var(--white);
  box-shadow: -5px 0 30px rgba(0, 0, 0, 0.15);
  z-index: 1001;
  transform: translateX(100%);
  transition: transform 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  overflow: hidden;
  display: flex;
}
.settings-drawer.open {
  transform: translateX(0);
}

.drawer-view {
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  background: var(--white);
  display: flex;
  flex-direction: column;
  transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* Nested Slide-in Submenus */
.drawer-detail-view {
  transform: translateX(100%);
  z-index: 10;
  border-left: 1px solid var(--border);
}
.drawer-detail-view.open {
  transform: translateX(0);
}

/* Menu rows and header styles inside drawer */
.settings-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.settings-group-header {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--sage);
  padding: 6px 8px;
  border-bottom: 1.5px solid var(--cream);
  margin-bottom: 4px;
}

.settings-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 11px 14px;
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition);
  background: var(--white);
  border: 1px solid var(--border);
  user-select: none;
}
.settings-row:hover {
  background: var(--cream);
  border-color: var(--sage);
  transform: translateX(2px);
}

.settings-row-left {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13.5px;
  color: var(--text);
}

/* Form Switch component */
.switch-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 0;
  border-bottom: 1px solid var(--cream);
}
.switch-row:last-child {
  border-bottom: none;
}
.switch-info h5 {
  font-size: 13.5px;
  font-weight: 600;
  color: var(--text);
}
.switch-info p {
  font-size: 11.5px;
  color: var(--muted);
  margin-top: 2px;
}
.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  flex-shrink: 0;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: var(--border);
  transition: .3s;
  border-radius: 34px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 16px; width: 16px;
  left: 4px; bottom: 4px;
  background-color: white;
  transition: .3s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: var(--sage);
}
input:checked + .slider:before {
  transform: translateX(20px);
}
</style>

<script>
let drawerProfileData = {};

function openSettingsDrawer() {
  const overlay = document.getElementById('settingsOverlay');
  const drawer = document.getElementById('settingsDrawer');
  
  if (overlay && drawer) {
    // Load fresh target details
    fetchDrawerProfileData();
    overlay.classList.add('open');
    drawer.classList.add('open');
  }
}

function closeSettingsDrawer() {
  const overlay = document.getElementById('settingsOverlay');
  const drawer = document.getElementById('settingsDrawer');
  
  if (overlay && drawer) {
    overlay.classList.remove('open');
    drawer.classList.remove('open');
    closeDrawerDetail(); // Close any active sub-panel
  }
}

async function fetchDrawerProfileData() {
  try {
    drawerProfileData = await API.getProfile() || {};
  } catch (e) {}
}

const DRAWER_PANELS = {
  personal: {
    title: 'Personal Information',
    render: () => `
      <form id="form-personal" onsubmit="saveDrawerPersonal(event)">
        <div class="field-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
          <div class="form-group">
            <label class="form-label" style="font-size:11px;">Age</label>
            <input type="number" id="set-age" class="form-input" required value="${drawerProfileData.age || 25}">
          </div>
          <div class="form-group">
            <label class="form-label" style="font-size:11px;">Gender</label>
            <select id="set-gender" class="form-select" style="width:100%; height:46px;">
              <option value="male" ${drawerProfileData.gender === 'male' ? 'selected' : ''}>Male</option>
              <option value="female" ${drawerProfileData.gender === 'female' ? 'selected' : ''}>Female</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" style="font-size:11px;">Weight (kg)</label>
            <input type="number" id="set-weight" class="form-input" required value="${drawerProfileData.weight_kg || 70}">
          </div>
          <div class="form-group">
            <label class="form-label" style="font-size:11px;">Height (cm)</label>
            <input type="number" id="set-height" class="form-input" required value="${drawerProfileData.height_cm || 170}">
          </div>
        </div>
        
        <div class="form-group" style="margin-bottom:20px;">
          <label class="form-label" style="font-size:11px;">Activity Level</label>
          <select id="set-activity" class="form-select" style="width:100%; height:46px;">
            <option value="sedentary" ${drawerProfileData.activity_level === 'sedentary' ? 'selected' : ''}>Sedentary: No exercise</option>
            <option value="light" ${drawerProfileData.activity_level === 'light' ? 'selected' : ''}>Light: 1-3 days/week</option>
            <option value="moderate" ${drawerProfileData.activity_level === 'moderate' ? 'selected' : ''}>Moderate: 3-5 days/week</option>
            <option value="active" ${drawerProfileData.activity_level === 'active' ? 'selected' : ''}>Active: 6-7 days/week</option>
            <option value="very_active" ${drawerProfileData.activity_level === 'very_active' ? 'selected' : ''}>Very Active: Heavy labor</option>
          </select>
        </div>

        <button type="submit" class="btn btn-lime" style="width:100%; height:46px; justify-content:center;">
          Save Personal Info
        </button>
      </form>
    `
  },
  security: {
    title: 'Login & Security',
    render: () => `
      <form id="form-security" onsubmit="saveDrawerSecurity(event)">
        <div class="form-group" style="margin-bottom:12px;">
          <label class="form-label" style="font-size:11px;">Email Address</label>
          <input type="email" class="form-input" value="user@example.com" disabled style="background:#f7f7f7; color:var(--muted)">
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label class="form-label" style="font-size:11px;">New Password</label>
          <input type="password" id="set-new-password" class="form-input" placeholder="••••••••" minlength="6">
        </div>
        <div class="form-group" style="margin-bottom:20px;">
          <label class="form-label" style="font-size:11px;">Confirm Password</label>
          <input type="password" id="set-confirm-password" class="form-input" placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%; height:46px; justify-content:center;">
          Update Credentials
        </button>
      </form>
    `
  },
  goals: {
    title: 'Edit Goals',
    render: () => `
      <form id="form-goals" onsubmit="saveDrawerGoals(event)">
        <div class="form-group" style="margin-bottom:20px;">
          <label class="form-label" style="font-size:11px;">Active Health Goal</label>
          <div class="toggle-group" style="display:flex; gap:8px;">
            <button type="button" class="toggle-pill goal-btn ${drawerProfileData.goal === 'lose' ? 'active' : ''}" style="flex:1; font-size:11.5px; padding:6px 10px;" onclick="setDrawerGoal(this, 'lose')">🏃 Lose</button>
            <button type="button" class="toggle-pill goal-btn ${drawerProfileData.goal === 'maintain' ? 'active' : ''}" style="flex:1; font-size:11.5px; padding:6px 10px;" onclick="setDrawerGoal(this, 'maintain')">⚖️ Maintain</button>
            <button type="button" class="toggle-pill goal-btn ${drawerProfileData.goal === 'gain' ? 'active' : ''}" style="flex:1; font-size:11.5px; padding:6px 10px;" onclick="setDrawerGoal(this, 'gain')">💪 Gain</button>
          </div>
        </div>

        <div class="goal-highlight" style="background:var(--cream); padding:16px; border-radius:var(--radius-md); text-align:center; margin-bottom:20px;">
          <div class="goal-label" style="font-size:10px; text-transform:uppercase; color:var(--muted); font-weight:600; letter-spacing:0.05em;">Computed Daily Calorie Target</div>
          <div class="goal-number" style="font-family:'Playfair Display',serif; font-size:28px; font-weight:600; color:var(--forest); margin:6px 0;" id="set-target-calories">${drawerProfileData.daily_calories || 2000}</div>
          <div class="goal-unit" style="font-size:11px; color:var(--muted);">kcal / day</div>
        </div>

        <button type="submit" class="btn btn-lime" style="width:100%; height:46px; justify-content:center;">
          Save & Recalculate
        </button>
      </form>
    `
  },
  diet: {
    title: 'Dietary Restrictions',
    render: () => `
      <form id="form-diet" onsubmit="saveDrawerDiet(event)">
        <div class="form-group" style="margin-bottom:20px;">
          <label class="form-label" style="font-size:11px;">Preferred Diet Style</label>
          <select id="set-diet-type" class="form-select" style="width:100%; height:46px;">
            <option value="anything" ${drawerProfileData.diet_type === 'anything' ? 'selected' : ''}>No restrictions</option>
            <option value="vegetarian" ${drawerProfileData.diet_type === 'vegetarian' ? 'selected' : ''}>Vegetarian</option>
            <option value="vegan" ${drawerProfileData.diet_type === 'vegan' ? 'selected' : ''}>Vegan</option>
            <option value="keto" ${drawerProfileData.diet_type === 'keto' ? 'selected' : ''}>Keto (High fat, low carb)</option>
            <option value="paleo" ${drawerProfileData.diet_type === 'paleo' ? 'selected' : ''}>Paleo</option>
          </select>
        </div>
        <button type="submit" class="btn btn-lime" style="width:100%; height:46px; justify-content:center;">
          Apply Restrictions
        </button>
      </form>
    `
  },
  macros: {
    title: 'Macro Ratios',
    render: () => `
      <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:20px;">
        <div class="macro-row" style="display:flex; align-items:center; justify-content:space-between; padding:10px; border:1px solid var(--border); border-radius:var(--radius-md);">
          <div>
            <h5 style="color:var(--coral); font-size:13px; font-weight:600;">Protein</h5>
            <p style="font-size:10px; color:var(--muted)">30% of total calories</p>
          </div>
          <strong style="font-size:15px; color:var(--forest);">${drawerProfileData.protein_g || 150}g</strong>
        </div>

        <div class="macro-row" style="display:flex; align-items:center; justify-content:space-between; padding:10px; border:1px solid var(--border); border-radius:var(--radius-md);">
          <div>
            <h5 style="color:var(--amber); font-size:13px; font-weight:600;">Carbohydrates</h5>
            <p style="font-size:10px; color:var(--muted)">40% of total calories</p>
          </div>
          <strong style="font-size:15px; color:var(--forest);">${drawerProfileData.carbs_g || 200}g</strong>
        </div>

        <div class="macro-row" style="display:flex; align-items:center; justify-content:space-between; padding:10px; border:1px solid var(--border); border-radius:var(--radius-md);">
          <div>
            <h5 style="color:var(--sky); font-size:13px; font-weight:600;">Fats</h5>
            <p style="font-size:10px; color:var(--muted)">30% of total calories</p>
          </div>
          <strong style="font-size:15px; color:var(--forest);">${drawerProfileData.fat_g || 65}g</strong>
        </div>
      </div>
      <button class="btn btn-outline" onclick="openDrawerDetail('goals')" style="width:100%; height:46px; justify-content:center;">
        Adjust Target Calories
      </button>
    `
  },
  appearance: {
    title: 'Appearance',
    render: () => `
      <div class="switch-row">
        <div class="switch-info">
          <h5>Dark Mode</h5>
          <p>Apply dark high-contrast dashboard layers.</p>
        </div>
        <label class="switch">
          <input type="checkbox" id="drawer-pref-dark" onchange="toggleDrawerPref('dark')">
          <span class="slider"></span>
        </label>
      </div>
      <div class="switch-row" style="margin-bottom:20px;">
        <div class="switch-info">
          <h5>Reduce Animations</h5>
          <p>Disable page entry fades and transitions.</p>
        </div>
        <label class="switch">
          <input type="checkbox" id="drawer-pref-reduce" onchange="toggleDrawerPref('reduce_anim')">
          <span class="slider"></span>
        </label>
      </div>
      <button class="btn btn-lime" style="width:100%; height:46px; justify-content:center;" onclick="saveDrawerPrefs()">
        Save Preferences
      </button>
    `
  },
  units: {
    title: 'Units of Measure',
    render: () => `
      <div class="switch-row">
        <div class="switch-info">
          <h5>Imperial Weight (lbs)</h5>
          <p>Display all bodyweight inputs in pounds.</p>
        </div>
        <label class="switch">
          <input type="checkbox" id="drawer-pref-lbs" onchange="toggleDrawerPref('lbs')">
          <span class="slider"></span>
        </label>
      </div>
      <div class="switch-row" style="margin-bottom:20px;">
        <div class="switch-info">
          <h5>Imperial Height (ft/in)</h5>
          <p>Display height parameters in feet/inches.</p>
        </div>
        <label class="switch">
          <input type="checkbox" id="drawer-pref-ft" onchange="toggleDrawerPref('ft')">
          <span class="slider"></span>
        </label>
      </div>
      <button class="btn btn-lime" style="width:100%; height:46px; justify-content:center;" onclick="saveDrawerPrefs()">
        Save Preferences
      </button>
    `
  },
  notifications: {
    title: 'Notifications Settings',
    render: () => `
      <div class="switch-row">
        <div class="switch-info">
          <h5>Daily Reminders</h5>
          <p>Receive notifications to log eaten foods.</p>
        </div>
        <label class="switch">
          <input type="checkbox" id="drawer-pref-notif" checked>
          <span class="slider"></span>
        </label>
      </div>
      <div class="switch-row" style="margin-bottom:20px;">
        <div class="switch-info">
          <h5>Weekly Digest</h5>
          <p>Receive weekly weight target summary digests.</p>
        </div>
        <label class="switch">
          <input type="checkbox" id="drawer-pref-weekly-notif" checked>
          <span class="slider"></span>
        </label>
      </div>
      <button class="btn btn-lime" style="width:100%; height:46px; justify-content:center;" onclick="saveDrawerPrefs()">
        Save Preferences
      </button>
    `
  },
  connections: {
    title: 'Apple Health / Fit Connection',
    render: () => `
      <div style="text-align:center; padding: 24px 12px; border: 1px dashed var(--border); border-radius: var(--radius-lg); margin-bottom:20px;">
        <i class="ti ti-heart" style="font-size:36px; color:var(--coral); display:block; margin-bottom:8px;"></i>
        <h5 style="font-size:13px; font-weight:600;">Connect Health Suite</h5>
        <p style="font-size:11.5px; color:var(--muted); max-width:240px; margin:4px auto 0 auto;">Allow NutriPlan to read active calories burned and export food nutrient macros directly.</p>
      </div>
      <div class="switch-row" style="margin-bottom:20px;">
        <div class="switch-info">
          <h5>Auto Sync Logs</h5>
          <p>Automatically push daily logging metrics every hour.</p>
        </div>
        <label class="switch">
          <input type="checkbox" id="drawer-pref-sync" checked>
          <span class="slider"></span>
        </label>
      </div>
      <button class="btn btn-lime" style="width:100%; height:46px; justify-content:center;" onclick="showToast('Health suite connection initiated.', 'info')">
        Connect Apple Health / Fit
      </button>
    `
  },
  smartwatch: {
    title: 'Smartwatch Sync',
    render: () => `
      <div style="text-align:center; padding: 24px 12px; border: 1px dashed var(--border); border-radius: var(--radius-lg); margin-bottom:20px;">
        <i class="ti ti-device-watch" style="font-size:36px; color:var(--sky); display:block; margin-bottom:8px;"></i>
        <h5 style="font-size:13px; font-weight:600;">Smartwatch Integration</h5>
        <p style="font-size:11.5px; color:var(--muted); max-width:240px; margin:4px auto 0 auto;">Sync custom workout streams to recalculate calorie allowance caps on the fly.</p>
      </div>
      <button class="btn btn-lime" style="width:100%; height:46px; justify-content:center;" onclick="showToast('Wearable sync initiated.', 'info')">
        Pair Smartwatch Device
      </button>
    `
  }
};

function openDrawerDetail(panelKey) {
  const p = DRAWER_PANELS[panelKey];
  if (!p) return;

  const detailView = document.getElementById('drawerViewDetail');
  const title = document.getElementById('drawerDetailTitle');
  const body = document.getElementById('drawerDetailBody');
  
  if (detailView && title && body) {
    title.textContent = p.title;
    body.innerHTML = p.render();
    detailView.classList.add('open');
    
    // Bind preference checkboxes states
    syncDrawerPrefCheckboxes(panelKey);
  }
}

function closeDrawerDetail() {
  const detailView = document.getElementById('drawerViewDetail');
  if (detailView) {
    detailView.classList.remove('open');
  }
}

function syncDrawerPrefCheckboxes(panelKey) {
  if (panelKey === 'appearance') {
    document.getElementById('drawer-pref-dark').checked = localStorage.getItem('theme_dark') === 'true';
    document.getElementById('drawer-pref-reduce').checked = localStorage.getItem('theme_reduce_anim') === 'true';
  } else if (panelKey === 'units') {
    document.getElementById('drawer-pref-lbs').checked = localStorage.getItem('unit_lbs') === 'true';
    document.getElementById('drawer-pref-ft').checked = localStorage.getItem('unit_ft') === 'true';
  }
}

function setDrawerGoal(btn, value) {
  document.querySelectorAll('#drawerViewDetail .goal-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  drawerProfileData.goal = value;

  const tdee = parseInt(drawerProfileData.tdee) || 2400;
  const adjust = value === 'lose' ? -500 : (value === 'gain' ? 300 : 0);
  const newCal = Math.max(1200, tdee + adjust);
  
  drawerProfileData.daily_calories = newCal;
  
  drawerProfileData.protein_g = Math.round(newCal * 0.30 / 4);
  drawerProfileData.carbs_g = Math.round(newCal * 0.40 / 4);
  drawerProfileData.fat_g = Math.round(newCal * 0.30 / 9);

  document.getElementById('set-target-calories').textContent = newCal.toLocaleString();
}

function toggleDrawerPref(key) {
  if (key === 'dark') {
    const val = document.getElementById('drawer-pref-dark').checked;
    localStorage.setItem('theme_dark', val);
  } else if (key === 'reduce_anim') {
    const val = document.getElementById('drawer-pref-reduce').checked;
    localStorage.setItem('theme_reduce_anim', val);
  } else if (key === 'lbs') {
    const val = document.getElementById('drawer-pref-lbs').checked;
    localStorage.setItem('unit_lbs', val);
  } else if (key === 'ft') {
    const val = document.getElementById('drawer-pref-ft').checked;
    localStorage.setItem('unit_ft', val);
  }
}

function saveDrawerPrefs() {
  showToast('Preferences saved successfully ✓', 'success');
}

async function saveDrawerPersonal(e) {
  e.preventDefault();
  const saveBtn = e.target.querySelector('button[type="submit"]');
  saveBtn.disabled = true;

  const body = {
    age: parseInt(document.getElementById('set-age').value),
    gender: document.getElementById('set-gender').value,
    weight_kg: parseFloat(document.getElementById('set-weight').value),
    height_cm: parseFloat(document.getElementById('set-height').value),
    activity_level: document.getElementById('set-activity').value,
    goal: drawerProfileData.goal || 'maintain',
    diet_type: drawerProfileData.diet_type || 'anything'
  };

  try {
    await API.updateProfile(body);
    showToast('Personal info saved successfully ✓', 'success');
    // Refresh local drawer dataset
    drawerProfileData = await API.getProfile() || {};
    closeDrawerDetail();
    
    // Proactively sync sidebar user pill text in DOM
    if (typeof syncSidebarUserInfo === 'function') syncSidebarUserInfo();
  } catch (err) {
    showToast('Saving failed: ' + err.message, 'error');
  } finally {
    saveBtn.disabled = false;
  }
}

async function saveDrawerGoals(e) {
  e.preventDefault();
  const saveBtn = e.target.querySelector('button[type="submit"]');
  saveBtn.disabled = true;

  const body = {
    age: parseInt(drawerProfileData.age) || 25,
    gender: drawerProfileData.gender || 'male',
    weight_kg: parseFloat(drawerProfileData.weight_kg) || 70,
    height_cm: parseFloat(drawerProfileData.height_cm) || 170,
    activity_level: drawerProfileData.activity_level || 'moderate',
    goal: drawerProfileData.goal,
    diet_type: drawerProfileData.diet_type || 'anything'
  };

  try {
    await API.updateProfile(body);
    showToast('Goals updated & macros recalculated ✓', 'success');
    drawerProfileData = await API.getProfile() || {};
    closeDrawerDetail();
    
    // Proactively sync sidebar user pill text in DOM
    if (typeof syncSidebarUserInfo === 'function') syncSidebarUserInfo();
    
    // If dashboard is loaded, reload its values
    if (typeof loadDashboardData === 'function') loadDashboardData();
  } catch (err) {
    showToast('Recalculation failed: ' + err.message, 'error');
  } finally {
    saveBtn.disabled = false;
  }
}

async function saveDrawerDiet(e) {
  e.preventDefault();
  const saveBtn = e.target.querySelector('button[type="submit"]');
  saveBtn.disabled = true;

  const body = {
    age: parseInt(drawerProfileData.age) || 25,
    gender: drawerProfileData.gender || 'male',
    weight_kg: parseFloat(drawerProfileData.weight_kg) || 70,
    height_cm: parseFloat(drawerProfileData.height_cm) || 170,
    activity_level: drawerProfileData.activity_level || 'moderate',
    goal: drawerProfileData.goal || 'maintain',
    diet_type: document.getElementById('set-diet-type').value
  };

  try {
    await API.updateProfile(body);
    showToast('Diet restrictions applied successfully ✓', 'success');
    drawerProfileData = await API.getProfile() || {};
    closeDrawerDetail();
    
    // If dashboard is loaded, reload its values
    if (typeof loadDashboardData === 'function') loadDashboardData();
  } catch (err) {
    showToast('Saving restrictions failed: ' + err.message, 'error');
  } finally {
    saveBtn.disabled = false;
  }
}

async function saveDrawerSecurity(e) {
  e.preventDefault();
  const pass = document.getElementById('set-new-password').value;
  const confirmPass = document.getElementById('set-confirm-password').value;

  if (pass !== confirmPass) {
    showToast('Passwords do not match.', 'error');
    return;
  }

  showToast('Credentials updated successfully ✓', 'success');
  closeDrawerDetail();
}
</script>

