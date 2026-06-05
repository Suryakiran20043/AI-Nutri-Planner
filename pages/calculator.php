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
  <title>Calorie Calculator — NutriPlan</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css" rel="stylesheet">
  <link href="../assets/css/main.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/components.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/animations.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/pages/calculator.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>

  <div class="progress-bar" id="pbar" style="width:80%"></div>

  <div class="app">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main">
      <div class="topbar animate-in">
        <div>
          <div class="page-title">Calorie <span>Calculator</span></div>
          <div style="font-size:13px;color:var(--muted);margin-top:4px">Find your personal daily calorie target</div>
        </div>
        <div class="topbar-right">
          <div class="topbar-actions" style="display: flex; align-items: center; gap: 12px;">
            <a href="/nutriplan/pages/settings.php" class="topbar-action-btn" title="Settings" style="width: 38px; height: 38px; border-radius: 50%; background: var(--white); border: 1.5px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--muted); text-decoration: none; font-size: 18px; transition: var(--transition);" onmouseover="this.style.borderColor='var(--sage)'; this.style.color='var(--sage)';" onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--muted)';">
              <i class="ti ti-settings"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="calc-layout">
        <!-- Left: Form -->
        <div class="calc-form-col">
          <div class="card animate-in">
            <div class="form-section">
              <span class="form-section-title">Personal Information</span>
              <div class="field-grid-2">
                <div class="form-group">
                  <label class="form-label" for="inp-age">Age</label>
                  <input type="number" id="inp-age" class="form-input" value="28" min="10" max="100" placeholder="Years">
                </div>
                <div class="form-group">
                  <label class="form-label" for="inp-weight">Weight (kg)</label>
                  <input type="number" id="inp-weight" class="form-input" value="70" min="30" placeholder="kg">
                </div>
                <div class="form-group">
                  <label class="form-label" for="inp-height">Height (cm)</label>
                  <input type="number" id="inp-height" class="form-input" value="170" min="100" placeholder="cm">
                </div>
                <div class="form-group">
                  <label class="form-label">Gender</label>
                  <div class="radio-row">
                    <label class="radio-pill">
                      <input type="radio" name="gender" value="male" checked> <span>Male</span>
                    </label>
                    <label class="radio-pill">
                      <input type="radio" name="gender" value="female"> <span>Female</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <span class="form-section-title">Activity Level</span>
              <div class="toggle-group" id="activity-group">
                <button class="toggle-pill activity-btn" data-value="sedentary">Sedentary</button>
                <button class="toggle-pill activity-btn" data-value="light">Light</button>
                <button class="toggle-pill activity-btn active" data-value="moderate">Moderate</button>
                <button class="toggle-pill activity-btn" data-value="active">Active</button>
                <button class="toggle-pill activity-btn" data-value="very_active">Very Active</button>
              </div>
              <p class="section-desc" id="activity-desc">Moderate: Exercise 3-5 times a week.</p>
            </div>

            <div class="form-section">
              <span class="form-section-title">Your Goal</span>
              <div class="toggle-group" id="goal-group">
                <button class="toggle-pill goal-btn" data-value="lose">🏃 Lose Weight</button>
                <button class="toggle-pill goal-btn active" data-value="maintain">⚖️ Maintain</button>
                <button class="toggle-pill goal-btn" data-value="gain">💪 Gain Muscle</button>
              </div>
            </div>

            <div class="form-section" style="border: none; padding-bottom: 0;">
              <span class="form-section-title">Diet Preference</span>
              <select id="inp-diet" class="form-select">
                <option value="anything">No restrictions</option>
                <option value="vegetarian">Vegetarian</option>
                <option value="vegan">Vegan</option>
                <option value="keto">Keto</option>
                <option value="paleo">Paleo</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Right: Results -->
        <div class="calc-result-col">
          <div id="results-panel" class="card-dark result-panel animate-in" style="animation-delay: 0.15s">
            <div class="result-header">
              <span class="result-eyebrow">Your Results</span>
              <h2 style="font-family:'Playfair Display',serif; font-size:22px; color:#fff; margin-top:4px">
                Daily Calorie Target
              </h2>
            </div>

            <div class="result-grid-2">
              <div class="result-box">
                <div class="result-big" id="res-bmr">1,743</div>
                <div class="result-label">BMR</div>
                <div class="result-sub">Calories at rest</div>
              </div>
              <div class="result-box">
                <div class="result-big" id="res-tdee">2,701</div>
                <div class="result-label">TDEE</div>
                <div class="result-sub">With your activity</div>
              </div>
            </div>

            <div class="goal-highlight">
              <div class="goal-label" id="res-goal-label">Maintenance calories</div>
              <div class="goal-number" id="res-goal-cal">2,701</div>
              <div class="goal-unit">kcal / day</div>
            </div>

            <div class="macro-results">
              <div class="macro-result-label">Recommended Macros</div>
              
              <div class="macro-row">
                <div class="macro-info">
                  <div class="macro-lbl"><span class="macro-dot" style="background:var(--coral)"></span>Protein</div>
                  <div class="macro-val" id="res-protein">180g</div>
                </div>
                <div class="macro-bar-wrap">
                  <div class="macro-bar bar-protein" id="bar-protein" style="width: 30%"></div>
                </div>
              </div>

              <div class="macro-row">
                <div class="macro-info">
                  <div class="macro-lbl"><span class="macro-dot" style="background:var(--amber)"></span>Carbs</div>
                  <div class="macro-val" id="res-carbs">240g</div>
                </div>
                <div class="macro-bar-wrap">
                  <div class="macro-bar bar-carbs" id="bar-carbs" style="width: 40%"></div>
                </div>
              </div>

              <div class="macro-row">
                <div class="macro-info">
                  <div class="macro-lbl"><span class="macro-dot" style="background:var(--sky)"></span>Fat</div>
                  <div class="macro-val" id="res-fat">80g</div>
                </div>
                <div class="macro-bar-wrap">
                  <div class="macro-bar bar-fat" id="bar-fat" style="width: 30%"></div>
                </div>
              </div>
            </div>

            <button class="btn btn-lime" style="width: 100%; margin-top: 32px;" id="btn-save-targets">
              <span>Save & Generate Plan</span> <i class="ti ti-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/main.js"></script>
  <script src="../assets/js/calculator.js"></script>
</body>
</html>
