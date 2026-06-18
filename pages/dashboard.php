<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /AI-Nutri-Planner/index.php');
    exit;
}
$current_user_name = $_SESSION['name'] ?? 'User';
$firstName = explode(' ', $current_user_name)[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Dashboard — NutriPlan</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css" rel="stylesheet">
  <link href="../assets/css/main.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/components.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/animations.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/pages/dashboard.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>
  
  <div class="progress-bar" id="pbar" style="width:35%"></div>

  <div class="app">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main">
    
    <!-- Topbar matching mockup -->
    <div class="topbar animate-in">
      <div>
        <div class="page-title">Good morning, <span><?= htmlspecialchars($firstName) ?>.</span></div>
        <div style="font-size:13px;color:var(--muted);margin-top:4px" id="dashboard-subtitle">Loading daily plan stats...</div>
      </div>
      <div class="topbar-right">
        <button class="btn btn-outline" id="btn-regenerate-dashboard">↻ Regenerate</button>
        <a href="/AI-Nutri-Planner/pages/food-search.php" class="btn btn-lime">+ Log Meal</a>
        <div class="topbar-actions" style="display: flex; align-items: center; gap: 12px; margin-left: 12px; border-left: 1px solid var(--border); padding-left: 16px;">
          <a href="/AI-Nutri-Planner/pages/settings.php" class="topbar-action-btn" title="Settings" style="width: 38px; height: 38px; border-radius: 50%; background: var(--white); border: 1.5px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--muted); text-decoration: none; font-size: 18px; transition: var(--transition);" onmouseover="this.style.borderColor='var(--sage)'; this.style.color='var(--sage)';" onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--muted)';">
            <i class="ti ti-settings"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Calories + Macros Grid matching mockup -->
    <div class="grid-2 style-dashboard-top animate-in" style="animation-delay: 0.05s; margin-bottom: 16px;">
      
      <!-- Calorie card with custom ring graphics and stats on the right -->
      <div class="cal-card">
        <div class="cal-label">Today's Calories</div>
        <div class="cal-ring-wrap">
          <div class="ring-container">
            <svg width="100" height="100" viewBox="0 0 100 100" class="ring-svg">
              <circle cx="50" cy="50" r="38" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="8"/>
              <circle id="ring-arc" cx="50" cy="50" r="38" fill="none" stroke="#B8E04A" stroke-width="8" stroke-linecap="round" stroke-dasharray="239" stroke-dashoffset="239" transform="rotate(-90 50 50)" style="transition:stroke-dashoffset 1.2s cubic-bezier(.25,.46,.45,.94)"/>
              <text x="50" y="46" text-anchor="middle" fill="white" font-size="14" font-weight="600" font-family="Playfair Display" id="val-ring-consumed">0</text>
              <text x="50" y="60" text-anchor="middle" fill="rgba(255,255,255,.4)" font-size="9" font-family="DM Sans" id="val-ring-target">of 2000</text>
            </svg>
          </div>
          <div class="cal-stats">
            <div class="cal-num" id="val-consumed-big">0</div>
            <div class="cal-sub">kcal consumed today</div>
            <div class="cal-row"><span class="cal-row-label">Goal</span><span class="cal-row-val" id="val-target-stat">2,000 kcal</span></div>
            <div class="cal-row"><span class="cal-row-label">Remaining</span><span class="cal-row-val" style="color:var(--lime)" id="val-remaining-stat">2,000 kcal</span></div>
            <div class="cal-row"><span class="cal-row-label">Burned</span><span class="cal-row-val" id="val-burned-stat">320 kcal</span></div>
          </div>
        </div>
      </div>

      <!-- Macros Card with progress bars and weekly streak tracker -->
      <div class="card card-p">
        <div style="font-size:13px;font-weight:600;margin-bottom:16px;color:var(--text)">Macros Today</div>
        <div class="macro-row">
          <div class="macro-label" style="color:var(--coral)">Protein</div>
          <div class="macro-bar-wrap"><div class="macro-bar bar-p" id="bar-p-progress" style="width:0%"></div></div>
          <div class="macro-g" id="val-prot-g">0 / 150g</div>
        </div>
        <div class="macro-row">
          <div class="macro-label" style="color:var(--amber)">Carbs</div>
          <div class="macro-bar-wrap"><div class="macro-bar bar-c" id="bar-c-progress" style="width:0%"></div></div>
          <div class="macro-g" id="val-carbs-g">0 / 200g</div>
        </div>
        <div class="macro-row">
          <div class="macro-label" style="color:var(--sky)">Fat</div>
          <div class="macro-bar-wrap"><div class="macro-bar bar-f" id="bar-f-progress" style="width:0%"></div></div>
          <div class="macro-g" id="val-fat-g">0 / 65g</div>
        </div>
        <div style="border-top:1px solid var(--border);margin-top:16px;padding-top:12px">
          <div style="font-size:11px;color:var(--muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Weekly streak</div>
          <div style="display:flex;gap:5px" id="streak-week-boxes">
            <!-- Mon - Sun streak boxes populated dynamically -->
          </div>
          <div style="font-size:12px;color:var(--sage);font-weight:600;margin-top:8px" id="streak-text-streak">🔥 0-day streak!</div>
        </div>
      </div>

    </div>

    <!-- Stat Chips Grid matching mockup -->
    <div class="stat-grid animate-in" style="animation-delay: 0.1s; margin-bottom: 16px;">
      <div class="stat-chip">
        <div class="stat-val" id="stat-weight-loss">-2.4<span style="font-size:14px">kg</span></div>
        <div class="stat-label" id="stat-weight-label">Lost this month</div>
        <div class="stat-delta delta-up" id="stat-weight-status">↓ On track</div>
      </div>
      <div class="stat-chip">
        <div class="stat-val" id="stat-days-logged">0</div>
        <div class="stat-label">Days logged</div>
        <div class="stat-delta delta-up">↑ Keep going</div>
      </div>
      <div class="stat-chip">
        <div class="stat-val" id="stat-adherence">0<span style="font-size:14px">%</span></div>
        <div class="stat-label">Plan adherence</div>
        <div class="stat-delta delta-up" id="stat-adherence-status">-- Stable</div>
      </div>
    </div>
    <!-- Today's meals schedule matching mockup visual lists -->
    <div class="meal-section animate-in" style="animation-delay: 0.15s" id="meals-schedule-container">
      <!-- Populated dynamically by JS -->
    </div>

    <!-- Food Swap Search Modal -->
    <div class="modal" id="swap-modal">
      <div class="modal-container" style="max-width: 650px;">
        <div class="modal-header">
          <h3 id="swap-modal-title">Search Food to Swap</h3>
          <button class="modal-close" onclick="closeSwapModal()">&times;</button>
        </div>
        <div class="modal-body">
          <div class="swap-search-bar" style="display: flex; gap: 12px; margin-bottom: 16px;">
            <input type="text" id="swap-search-input" class="form-input" placeholder="Search oatmeal, chicken salad, protein powder...">
            <button class="btn btn-primary" id="btn-swap-search">Search</button>
          </div>
          
          <div class="quick-chips-row" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
            <button class="quick-chip" data-query="egg">Egg</button>
            <button class="quick-chip" data-query="greek yogurt">Yogurt</button>
            <button class="quick-chip" data-query="chicken breast">Chicken</button>
            <button class="quick-chip" data-query="rice">Rice</button>
            <button class="quick-chip" data-query="salmon">Salmon</button>
            <button class="quick-chip" data-query="avocado">Avocado</button>
          </div>
          
          <div id="swap-results-grid" class="swap-results-list" style="display: flex; flex-direction: column; gap: 12px; max-height: 320px; overflow-y: auto;">
            <!-- Results rendered here -->
          </div>
        </div>
      </div>
    </div>

    <!-- ETM Food Detail Modal -->
    <div class="modal" id="etm-detail-modal">
      <div class="modal-container" style="max-width:900px; padding: 0;">
        <div class="modal-header" style="padding: 16px 24px; border-bottom: 1px solid var(--border);">
          <h3 id="etm-modal-title">Food Details</h3>
          <button class="modal-close" onclick="closeETMModal()">&times;</button>
        </div>
        <div class="modal-body" id="etm-modal-body" style="padding: 0;"></div>
      </div>
    </div>

    </main>
  </div>

  <script src="../assets/js/api.js?v=2"></script>
  <script src="../assets/js/main.js?v=2"></script>
  <script src="../assets/js/dashboard.js?v=2"></script>
</body>
</html>
