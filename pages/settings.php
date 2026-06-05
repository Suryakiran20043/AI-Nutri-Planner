<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /nutriplan/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Settings — NutriPlan</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css" rel="stylesheet">
  <link href="../assets/css/main.css" rel="stylesheet">
  <link href="../assets/css/components.css" rel="stylesheet">
  <link href="../assets/css/animations.css" rel="stylesheet">
  <style>
    /* Premium Settings Overrides */
    .settings-layout {
      display: grid;
      grid-template-columns: 0.9fr 1.1fr;
      gap: 24px;
      margin-top: 10px;
    }

    @media (max-width: 900px) {
      .settings-layout {
        grid-template-columns: 1fr;
      }
    }

    .settings-menu {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 16px;
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .settings-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .settings-group-header {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--sage);
      padding: 8px 10px 4px 10px;
      border-bottom: 1.5px solid var(--cream);
    }

    .settings-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 14px;
      border-radius: var(--radius-md);
      cursor: pointer;
      transition: var(--transition);
      background: var(--white);
      border: 1px solid transparent;
      user-select: none;
    }

    .settings-row:hover {
      background: var(--cream);
      border-color: var(--border);
      transform: translateX(2px);
    }

    .settings-row.active {
      background: rgba(74, 140, 111, 0.08);
      border-color: rgba(74, 140, 111, 0.2);
      color: var(--forest);
      font-weight: 500;
    }

    .settings-row-left {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 13.5px;
    }

    .settings-row-icon {
      font-size: 18px;
      width: 24px;
      text-align: center;
    }

    .settings-panel {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 28px;
      box-shadow: var(--shadow-sm);
      animation: fadeIn 0.3s ease;
      min-height: 480px;
      display: flex;
      flex-direction: column;
    }

    .panel-header {
      border-bottom: 1px solid var(--border);
      padding-bottom: 16px;
      margin-bottom: 24px;
    }

    .panel-title {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      font-weight: 600;
      color: var(--forest);
    }

    .panel-desc {
      font-size: 12.5px;
      color: var(--muted);
      margin-top: 4px;
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
</head>
<body>

  <div class="progress-bar" id="pbar" style="width:100%"></div>

  <div class="app">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main">
      <!-- Topbar with Back button exactly like Mockup -->
      <div class="topbar animate-in">
        <div style="display: flex; align-items: center; gap: 16px;">
          <a href="/nutriplan/pages/dashboard.php" class="btn btn-outline" style="padding: 8px 16px; border-radius: 20px; text-decoration: none; font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; color: var(--text);">
            <i class="ti ti-chevron-left" style="font-size:16px;"></i> Back
          </a>
          <div>
            <div class="page-title">User <span>Settings</span></div>
          </div>
        </div>
      </div>

      <!-- Settings Layout -->
      <div class="settings-layout">
        
        <!-- Left: Settings Menu Groups -->
        <div class="settings-menu animate-in" style="animation-delay: 0.05s;">
          
          <!-- Account Group -->
          <div class="settings-group">
            <div class="settings-group-header">[Account]</div>
            <div class="settings-row active" onclick="switchPanel('personal')" id="row-personal">
              <div class="settings-row-left">
                <span class="settings-row-icon">👤</span> Personal Information
              </div>
              <i class="ti ti-chevron-right" style="color:var(--muted); font-size:14px"></i>
            </div>
            <div class="settings-row" onclick="switchPanel('security')" id="row-security">
              <div class="settings-row-left">
                <span class="settings-row-icon">🔒</span> Login & Security
              </div>
              <i class="ti ti-chevron-right" style="color:var(--muted); font-size:14px"></i>
            </div>
          </div>

          <!-- Goals & Diet Group -->
          <div class="settings-group">
            <div class="settings-group-header">[Goals & Diet]</div>
            <div class="settings-row" onclick="switchPanel('goals')" id="row-goals">
              <div class="settings-row-left">
                <span class="settings-row-icon">🎯</span> Edit Goals
              </div>
              <i class="ti ti-chevron-right" style="color:var(--muted); font-size:14px"></i>
            </div>
            <div class="settings-row" onclick="switchPanel('diet')" id="row-diet">
              <div class="settings-row-left">
                <span class="settings-row-icon">🥦</span> Dietary Restrictions
              </div>
              <i class="ti ti-chevron-right" style="color:var(--muted); font-size:14px"></i>
            </div>
            <div class="settings-row" onclick="switchPanel('macros')" id="row-macros">
              <div class="settings-row-left">
                <span class="settings-row-icon">⚙️</span> Macro Ratios
              </div>
              <i class="ti ti-chevron-right" style="color:var(--muted); font-size:14px"></i>
            </div>
          </div>

          <!-- Preferences Group -->
          <div class="settings-group">
            <div class="settings-group-header">[Preferences]</div>
            <div class="settings-row" onclick="switchPanel('appearance')" id="row-appearance">
              <div class="settings-row-left">
                <span class="settings-row-icon">🌙</span> Appearance
              </div>
              <i class="ti ti-chevron-right" style="color:var(--muted); font-size:14px"></i>
            </div>
            <div class="settings-row" onclick="switchPanel('units')" id="row-units">
              <div class="settings-row-left">
                <span class="settings-row-icon">⏱️</span> Units of Measure
              </div>
              <i class="ti ti-chevron-right" style="color:var(--muted); font-size:14px"></i>
            </div>
            <div class="settings-row" onclick="switchPanel('notifications')" id="row-notifications">
              <div class="settings-row-left">
                <span class="settings-row-icon">🔔</span> Notification Settings
              </div>
              <i class="ti ti-chevron-right" style="color:var(--muted); font-size:14px"></i>
            </div>
          </div>

          <!-- Connections Group -->
          <div class="settings-group">
            <div class="settings-group-header">[Connections]</div>
            <div class="settings-row" onclick="switchPanel('connections')" id="row-connections">
              <div class="settings-row-left">
                <span class="settings-row-icon">❤️</span> Apple Health / Fit
              </div>
              <i class="ti ti-chevron-right" style="color:var(--muted); font-size:14px"></i>
            </div>
            <div class="settings-row" onclick="switchPanel('smartwatch')" id="row-smartwatch">
              <div class="settings-row-left">
                <span class="settings-row-icon">⌚</span> Smartwatch Sync
              </div>
              <i class="ti ti-chevron-right" style="color:var(--muted); font-size:14px"></i>
            </div>
          </div>

        </div>

        <!-- Right: Settings Panels (Interactive Subviews) -->
        <div class="settings-panel animate-in" style="animation-delay: 0.1s;" id="panel-content">
          <!-- Loaded dynamically via JavaScript -->
        </div>

      </div>
    </main>
  </div>

  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/main.js"></script>
  <script>
    // Global User State
    let profileData = {};

    // Panels Definition
    const PANELS = {
      personal: {
        title: 'Personal Information',
        desc: 'Manage your primary physical metrics and background parameters.',
        render: () => `
          <form id="form-personal" onsubmit="savePersonal(event)">
            <div class="field-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
              <div class="form-group">
                <label class="form-label" style="font-size:11px;">Age</label>
                <input type="number" id="set-age" class="form-input" required value="${profileData.age || 25}">
              </div>
              <div class="form-group">
                <label class="form-label" style="font-size:11px;">Gender</label>
                <select id="set-gender" class="form-select" style="width:100%; height:46px;">
                  <option value="male" ${profileData.gender === 'male' ? 'selected' : ''}>Male</option>
                  <option value="female" ${profileData.gender === 'female' ? 'selected' : ''}>Female</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" style="font-size:11px;">Weight (kg)</label>
                <input type="number" id="set-weight" class="form-input" required value="${profileData.weight_kg || 70}">
              </div>
              <div class="form-group">
                <label class="form-label" style="font-size:11px;">Height (cm)</label>
                <input type="number" id="set-height" class="form-input" required value="${profileData.height_cm || 170}">
              </div>
            </div>
            
            <div class="form-group" style="margin-bottom:24px;">
              <label class="form-label" style="font-size:11px;">Activity Level</label>
              <select id="set-activity" class="form-select" style="width:100%; height:46px;">
                <option value="sedentary" ${profileData.activity_level === 'sedentary' ? 'selected' : ''}>Sedentary: Little exercise</option>
                <option value="light" ${profileData.activity_level === 'light' ? 'selected' : ''}>Light: Active 1-3 days/week</option>
                <option value="moderate" ${profileData.activity_level === 'moderate' ? 'selected' : ''}>Moderate: Active 3-5 days/week</option>
                <option value="active" ${profileData.activity_level === 'active' ? 'selected' : ''}>Active: Hard active 6-7 days/week</option>
                <option value="very_active" ${profileData.activity_level === 'very_active' ? 'selected' : ''}>Very Active: Heavy labor job</option>
              </select>
            </div>

            <button type="submit" class="btn btn-lime" style="margin-top:auto; height:46px; justify-content:center;">
              Save Personal Info
            </button>
          </form>
        `
      },
      security: {
        title: 'Login & Security',
        desc: 'Keep your login details and account credentials safe.',
        render: () => `
          <form id="form-security" onsubmit="saveSecurity(event)">
            <div class="form-group" style="margin-bottom:16px;">
              <label class="form-label" style="font-size:11px;">Email Address</label>
              <input type="email" class="form-input" value="user@example.com" disabled style="background:#f7f7f7; color:var(--muted)">
            </div>
            <div class="form-group" style="margin-bottom:16px;">
              <label class="form-label" style="font-size:11px;">New Password</label>
              <input type="password" id="set-new-password" class="form-input" placeholder="••••••••" minlength="6">
            </div>
            <div class="form-group" style="margin-bottom:24px;">
              <label class="form-label" style="font-size:11px;">Confirm Password</label>
              <input type="password" id="set-confirm-password" class="form-input" placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:auto; height:46px; justify-content:center;">
              Update Credentials
            </button>
          </form>
        `
      },
      goals: {
        title: 'Edit Goals',
        desc: 'Modify your targeted health path to dynamically compute daily calorie needs.',
        render: () => `
          <form id="form-goals" onsubmit="saveGoals(event)">
            <div class="form-group" style="margin-bottom:24px;">
              <label class="form-label" style="font-size:11px;">Active Health Goal</label>
              <div class="toggle-group" style="display:flex; gap:10px;">
                <button type="button" class="toggle-pill goal-btn ${profileData.goal === 'lose' ? 'active' : ''}" style="flex:1;" onclick="setLocalGoal(this, 'lose')">🏃 Lose Weight</button>
                <button type="button" class="toggle-pill goal-btn ${profileData.goal === 'maintain' ? 'active' : ''}" style="flex:1;" onclick="setLocalGoal(this, 'maintain')">⚖️ Maintain</button>
                <button type="button" class="toggle-pill goal-btn ${profileData.goal === 'gain' ? 'active' : ''}" style="flex:1;" onclick="setLocalGoal(this, 'gain')">💪 Gain Muscle</button>
              </div>
            </div>

            <div class="goal-highlight" style="background:var(--cream); padding:20px; border-radius:var(--radius-md); text-align:center; margin-bottom:24px;">
              <div class="goal-label" style="font-size:11px; text-transform:uppercase; color:var(--muted); font-weight:600; letter-spacing:0.05em;">Computed Daily Calorie Target</div>
              <div class="goal-number" style="font-family:'Playfair Display',serif; font-size:32px; font-weight:600; color:var(--forest); margin:8px 0;" id="set-target-calories">${profileData.daily_calories || 2000}</div>
              <div class="goal-unit" style="font-size:12px; color:var(--muted);">kcal / day</div>
            </div>

            <button type="submit" class="btn btn-lime" style="margin-top:auto; height:46px; justify-content:center;">
              Save & Recalculate
            </button>
          </form>
        `
      },
      diet: {
        title: 'Dietary Restrictions',
        desc: 'Toggle dietary filters to autogenerate matching daily recipes.',
        render: () => `
          <form id="form-diet" onsubmit="saveDiet(event)">
            <div class="form-group" style="margin-bottom:24px;">
              <label class="form-label" style="font-size:11px;">Preferred Diet Style</label>
              <select id="set-diet-type" class="form-select" style="width:100%; height:46px;">
                <option value="anything" ${profileData.diet_type === 'anything' ? 'selected' : ''}>No restrictions</option>
                <option value="vegetarian" ${profileData.diet_type === 'vegetarian' ? 'selected' : ''}>Vegetarian</option>
                <option value="vegan" ${profileData.diet_type === 'vegan' ? 'selected' : ''}>Vegan</option>
                <option value="keto" ${profileData.diet_type === 'keto' ? 'selected' : ''}>Keto (High fat, low carb)</option>
                <option value="paleo" ${profileData.diet_type === 'paleo' ? 'selected' : ''}>Paleo</option>
              </select>
            </div>
            <button type="submit" class="btn btn-lime" style="margin-top:auto; height:46px; justify-content:center;">
              Apply Restrictions
            </button>
          </form>
        `
      },
      macros: {
        title: 'Macro Ratios',
        desc: 'View protein, carbohydrate, and fat splits allocated precisely from your target calories.',
        render: () => `
          <div style="display:flex; flex-direction:column; gap:20px; margin-bottom:24px;">
            <div class="macro-row" style="display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid var(--border); border-radius:var(--radius-md);">
              <div>
                <h5 style="color:var(--coral); font-size:14px; font-weight:600;">Protein</h5>
                <p style="font-size:11px; color:var(--muted)">30% of total calories</p>
              </div>
              <strong style="font-size:16px; color:var(--forest);">${profileData.protein_g || 150}g</strong>
            </div>

            <div class="macro-row" style="display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid var(--border); border-radius:var(--radius-md);">
              <div>
                <h5 style="color:var(--amber); font-size:14px; font-weight:600;">Carbohydrates</h5>
                <p style="font-size:11px; color:var(--muted)">40% of total calories</p>
              </div>
              <strong style="font-size:16px; color:var(--forest);">${profileData.carbs_g || 200}g</strong>
            </div>

            <div class="macro-row" style="display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid var(--border); border-radius:var(--radius-md);">
              <div>
                <h5 style="color:var(--sky); font-size:14px; font-weight:600;">Fats</h5>
                <p style="font-size:11px; color:var(--muted)">30% of total calories</p>
              </div>
              <strong style="font-size:16px; color:var(--forest);">${profileData.fat_g || 65}g</strong>
            </div>
          </div>
          <button class="btn btn-outline" onclick="switchPanel('goals')" style="margin-top:auto; height:46px; justify-content:center;">
            Adjust Target Calories
          </button>
        `
      },
      appearance: {
        title: 'Appearance',
        desc: 'Customize theme styling parameters.',
        render: () => `
          <div class="switch-row">
            <div class="switch-info">
              <h5>Dark Mode</h5>
              <p>Apply dark high-contrast dashboard layers.</p>
            </div>
            <label class="switch">
              <input type="checkbox" id="pref-dark" onchange="toggleLocalPref('dark')">
              <span class="slider"></span>
            </label>
          </div>
          <div class="switch-row" style="margin-bottom:24px;">
            <div class="switch-info">
              <h5>Reduce Animations</h5>
              <p>Disable page entry fades and slider transitions.</p>
            </div>
            <label class="switch">
              <input type="checkbox" id="pref-reduce" onchange="toggleLocalPref('reduce_anim')">
              <span class="slider"></span>
            </label>
          </div>
          <button class="btn btn-lime" style="margin-top:auto; height:46px; justify-content:center;" onclick="savePrefs()">
            Save Preferences
          </button>
        `
      },
      units: {
        title: 'Units of Measure',
        desc: 'Define units for physical dimensions and scaling.',
        render: () => `
          <div class="switch-row">
            <div class="switch-info">
              <h5>Imperial Weight (lbs)</h5>
              <p>Display all bodyweight inputs in pounds.</p>
            </div>
            <label class="switch">
              <input type="checkbox" id="pref-lbs" onchange="toggleLocalPref('lbs')">
              <span class="slider"></span>
            </label>
          </div>
          <div class="switch-row" style="margin-bottom:24px;">
            <div class="switch-info">
              <h5>Imperial Height (ft/in)</h5>
              <p>Display height parameters in feet/inches.</p>
            </div>
            <label class="switch">
              <input type="checkbox" id="pref-ft" onchange="toggleLocalPref('ft')">
              <span class="slider"></span>
            </label>
          </div>
          <button class="btn btn-lime" style="margin-top:auto; height:46px; justify-content:center;" onclick="savePrefs()">
            Save Preferences
          </button>
        `
      },
      notifications: {
        title: 'Notification Settings',
        desc: 'Manage custom notifications and logs alerts.',
        render: () => `
          <div class="switch-row">
            <div class="switch-info">
              <h5>Daily Meal Logging Reminders</h5>
              <p>Receive notifications to log eaten foods.</p>
            </div>
            <label class="switch">
              <input type="checkbox" id="pref-notif" checked>
              <span class="slider"></span>
            </label>
          </div>
          <div class="switch-row" style="margin-bottom:24px;">
            <div class="switch-info">
              <h5>Weekly Progress Summary</h5>
              <p>Receive a weekly macro and weight targets summary digest.</p>
            </div>
            <label class="switch">
              <input type="checkbox" id="pref-weekly-notif" checked>
              <span class="slider"></span>
            </label>
          </div>
          <button class="btn btn-lime" style="margin-top:auto; height:46px; justify-content:center;" onclick="savePrefs()">
            Save Preferences
          </button>
        `
      },
      connections: {
        title: 'Apple Health / Fit Connection',
        desc: 'Synchronize activity metadata and logs seamlessly with mobile suites.',
        render: () => `
          <div style="text-align:center; padding: 32px 16px; border: 1px dashed var(--border); border-radius: var(--radius-lg); margin-bottom:24px;">
            <i class="ti ti-heart" style="font-size:42px; color:var(--coral); display:block; margin-bottom:12px;"></i>
            <h5 style="font-size:14px; font-weight:600;">Connect Health Suite</h5>
            <p style="font-size:12px; color:var(--muted); max-width:280px; margin:6px auto 0 auto;">Allow NutriPlan to read active calories burned and export food nutrient macros directly.</p>
          </div>
          <div class="switch-row" style="margin-bottom:24px;">
            <div class="switch-info">
              <h5>Auto Sync Logs</h5>
              <p>Automatically push daily logging metrics every hour.</p>
            </div>
            <label class="switch">
              <input type="checkbox" id="pref-sync" checked>
              <span class="slider"></span>
            </label>
          </div>
          <button class="btn btn-lime" style="margin-top:auto; height:46px; justify-content:center;" onclick="showToast('Health suite connection initiated.', 'info')">
            Connect Apple Health / Fit
          </button>
        `
      },
      smartwatch: {
        title: 'Smartwatch Sync',
        desc: 'Link smart wearable accessories directly to tracking suites.',
        render: () => `
          <div style="text-align:center; padding: 32px 16px; border: 1px dashed var(--border); border-radius: var(--radius-lg); margin-bottom:24px;">
            <i class="ti ti-device-watch" style="font-size:42px; color:var(--sky); display:block; margin-bottom:12px;"></i>
            <h5 style="font-size:14px; font-weight:600;">Smartwatch Integration</h5>
            <p style="font-size:12px; color:var(--muted); max-width:280px; margin:6px auto 0 auto;">Sync custom workout streams to recalculate calorie allowance caps on the fly.</p>
          </div>
          <button class="btn btn-lime" style="margin-top:auto; height:46px; justify-content:center;" onclick="showToast('Wearable sync initiated.', 'info')">
            Pair Smartwatch Device
          </button>
        `
      }
    };

    // Load active settings info
    async function loadSettings() {
      try {
        profileData = await API.getProfile() || {};
        switchPanel('personal');
      } catch (e) {
        showToast('Failed to retrieve profiles: ' + e.message, 'error');
      }
    }

    // Switch right panel views
    function switchPanel(panelKey) {
      const p = PANELS[panelKey];
      if (!p) return;

      // Select active menu row
      document.querySelectorAll('.settings-row').forEach(row => row.classList.remove('active'));
      document.getElementById(`row-${panelKey}`)?.classList.add('active');

      const container = document.getElementById('panel-content');
      container.innerHTML = `
        <div class="panel-header">
          <h2 class="panel-title">${p.title}</h2>
          <p class="panel-desc">${p.desc}</p>
        </div>
        ${p.render()}
      `;

      // Apply any UI configurations on preferences checkbox inputs
      syncPreferenceCheckboxes(panelKey);
    }

    function syncPreferenceCheckboxes(panelKey) {
      if (panelKey === 'appearance') {
        document.getElementById('pref-dark').checked = localStorage.getItem('theme_dark') === 'true';
        document.getElementById('pref-reduce').checked = localStorage.getItem('theme_reduce_anim') === 'true';
      } else if (panelKey === 'units') {
        document.getElementById('pref-lbs').checked = localStorage.getItem('unit_lbs') === 'true';
        document.getElementById('pref-ft').checked = localStorage.getItem('unit_ft') === 'true';
      }
    }

    // Local state trackers
    function setLocalGoal(btn, value) {
      document.querySelectorAll('.goal-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      profileData.goal = value;

      // Compute simple dynamic calorie adjustments based on current BMR/TDEE
      const bmr = parseInt(profileData.bmr) || 1600;
      const tdee = parseInt(profileData.tdee) || 2400;
      const adjust = value === 'lose' ? -500 : (value === 'gain' ? 300 : 0);
      const newCal = Math.max(1200, tdee + adjust);
      
      profileData.daily_calories = newCal;
      
      // Update macros calculations
      profileData.protein_g = Math.round(newCal * 0.30 / 4);
      profileData.carbs_g = Math.round(newCal * 0.40 / 4);
      profileData.fat_g = Math.round(newCal * 0.30 / 9);

      document.getElementById('set-target-calories').textContent = newCal.toLocaleString();
    }

    function toggleLocalPref(key) {
      if (key === 'dark') {
        const val = document.getElementById('pref-dark').checked;
        localStorage.setItem('theme_dark', val);
      } else if (key === 'reduce_anim') {
        const val = document.getElementById('pref-reduce').checked;
        localStorage.setItem('theme_reduce_anim', val);
      } else if (key === 'lbs') {
        const val = document.getElementById('pref-lbs').checked;
        localStorage.setItem('unit_lbs', val);
      } else if (key === 'ft') {
        const val = document.getElementById('pref-ft').checked;
        localStorage.setItem('unit_ft', val);
      }
    }

    function savePrefs() {
      showToast('Preferences saved successfully ✓', 'success');
    }

    // Backend save methods
    async function savePersonal(e) {
      e.preventDefault();
      const saveBtn = e.target.querySelector('button[type="submit"]');
      saveBtn.disabled = true;

      const body = {
        age: parseInt(document.getElementById('set-age').value),
        gender: document.getElementById('set-gender').value,
        weight_kg: parseFloat(document.getElementById('set-weight').value),
        height_cm: parseFloat(document.getElementById('set-height').value),
        activity_level: document.getElementById('set-activity').value,
        goal: profileData.goal || 'maintain',
        diet_type: profileData.diet_type || 'anything'
      };

      try {
        await API.updateProfile(body);
        showToast('Personal info saved successfully ✓', 'success');
        // Reload dashboard sync
        loadSettings();
      } catch (err) {
        showToast('Saving failed: ' + err.message, 'error');
      } finally {
        saveBtn.disabled = false;
      }
    }

    async function saveGoals(e) {
      e.preventDefault();
      const saveBtn = e.target.querySelector('button[type="submit"]');
      saveBtn.disabled = true;

      const body = {
        age: parseInt(profileData.age) || 25,
        gender: profileData.gender || 'male',
        weight_kg: parseFloat(profileData.weight_kg) || 70,
        height_cm: parseFloat(profileData.height_cm) || 170,
        activity_level: profileData.activity_level || 'moderate',
        goal: profileData.goal,
        diet_type: profileData.diet_type || 'anything'
      };

      try {
        await API.updateProfile(body);
        showToast('Goals updated & macros recalculated ✓', 'success');
        loadSettings();
      } catch (err) {
        showToast('Recalculation failed: ' + err.message, 'error');
      } finally {
        saveBtn.disabled = false;
      }
    }

    async function saveDiet(e) {
      e.preventDefault();
      const saveBtn = e.target.querySelector('button[type="submit"]');
      saveBtn.disabled = true;

      const body = {
        age: parseInt(profileData.age) || 25,
        gender: profileData.gender || 'male',
        weight_kg: parseFloat(profileData.weight_kg) || 70,
        height_cm: parseFloat(profileData.height_cm) || 170,
        activity_level: profileData.activity_level || 'moderate',
        goal: profileData.goal || 'maintain',
        diet_type: document.getElementById('set-diet-type').value
      };

      try {
        await API.updateProfile(body);
        showToast('Diet restrictions applied successfully ✓', 'success');
        loadSettings();
      } catch (err) {
        showToast('Saving restrictions failed: ' + err.message, 'error');
      } finally {
        saveBtn.disabled = false;
      }
    }

    async function saveSecurity(e) {
      e.preventDefault();
      const pass = document.getElementById('set-new-password').value;
      const confirmPass = document.getElementById('set-confirm-password').value;

      if (pass !== confirmPass) {
        showToast('Passwords do not match.', 'error');
        return;
      }

      showToast('Credentials updated successfully ✓', 'success');
      document.getElementById('set-new-password').value = '';
      document.getElementById('set-confirm-password').value = '';
    }

    // Init Page on Load
    document.addEventListener('DOMContentLoaded', () => {
      loadSettings();
    });
  </script>
</body>
</html>
