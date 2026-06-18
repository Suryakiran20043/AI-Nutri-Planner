<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /AI-Nutri-Planner/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Browse Foods — NutriPlan</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css" rel="stylesheet">
  <link href="../assets/css/main.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/components.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/animations.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/pages/browse-foods.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>

  <div class="progress-bar" id="pbar" style="width:90%"></div>

  <div class="app">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main">
      <div class="topbar animate-in">
        <div>
          <div class="page-title">Browse <span>Foods</span></div>
          <div style="font-size:13px;color:var(--muted);margin-top:4px">Explore our curated food database with detailed nutrition info</div>
        </div>
        <div class="topbar-right">
          <div class="topbar-actions" style="display: flex; align-items: center; gap: 12px;">
            <a href="/AI-Nutri-Planner/pages/settings.php" class="topbar-action-btn" title="Settings" style="width: 38px; height: 38px; border-radius: 50%; background: var(--white); border: 1.5px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--muted); text-decoration: none; font-size: 18px; transition: var(--transition);" onmouseover="this.style.borderColor='var(--sage)'; this.style.color='var(--sage)';" onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--muted)';">
              <i class="ti ti-settings"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Browse Controls: Search + Sort -->
      <div class="browse-controls card animate-in">
        <div class="browse-search-wrap">
          <i class="ti ti-search browse-search-icon"></i>
          <input type="text" id="browse-search-input" class="browse-search-field" placeholder="Search foods by name...">
          <button id="browse-search-clear" class="browse-search-clear" style="display:none;">
            <i class="ti ti-x"></i>
          </button>
        </div>
        <div class="browse-sort-wrap">
          <label for="browse-sort" class="browse-sort-label">Sort by</label>
          <select id="browse-sort" class="browse-sort-select">
            <option value="name">Name</option>
            <option value="calories">Calories</option>
            <option value="protein">Protein</option>
            <option value="carbs">Carbs</option>
            <option value="fat">Fat</option>
          </select>
        </div>
        <button class="btn btn-primary" id="browse-search-btn">
          <i class="ti ti-search"></i> Search
        </button>
      </div>

      <!-- Food Table -->
      <div id="browse-table-container" class="animate-in">
        <div class="browse-empty-state">
          <i class="ti ti-salad"></i>
          <h3>Discover Curated Foods</h3>
          <p>Browse our collection of foods with complete nutrition information, recipes, and ingredients.</p>
        </div>
      </div>

    </main>
  </div>

  <!-- ETM Food Detail Modal -->
  <div class="modal" id="etm-detail-modal">
    <div class="modal-container" style="max-width:900px;">
      <div class="modal-header">
        <h3>Food Details</h3>
        <button class="modal-close" onclick="closeETMModal()">&times;</button>
      </div>
      <div class="modal-body" id="etm-modal-body"></div>
    </div>
  </div>

  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/main.js"></script>
  <script src="../assets/js/browse-foods.js"></script>
</body>
</html>
