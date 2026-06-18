<?php require_once '../api/config.php'; header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Recipe Details | AI Nutri-Planner</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.36.0/tabler-icons.min.css">
  
  <!-- Reuse existing CSS -->
  <link rel="stylesheet" href="../assets/css/variables.css">
  <link rel="stylesheet" href="../assets/css/global.css">
  <link rel="stylesheet" href="../assets/css/components.css">

  <style>
    body {
      background-color: var(--bg-body);
      color: var(--text);
      font-family: 'Inter', sans-serif;
      margin: 0; padding: 0;
    }
    
    .recipe-page-container {
      max-width: 900px;
      margin: 0 auto;
      background: #fff;
      min-height: 100vh;
      box-shadow: 0 0 40px rgba(0,0,0,0.05);
    }
    
    .recipe-page-header {
      padding: 20px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid var(--border);
    }
    
    .recipe-page-header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 24px;
      color: var(--forest);
      margin: 0;
    }

    .back-btn {
      display: flex; align-items: center; gap: 8px;
      color: var(--forest);
      text-decoration: none;
      font-weight: 500;
      font-size: 14px;
      padding: 8px 16px;
      border-radius: 20px;
      background: var(--cream);
      transition: background 0.2s;
    }
    .back-btn:hover {
      background: var(--sage);
      color: #fff;
    }

    /* We will inject the same HTML structure used in the ETM modal here */
    #recipe-content {
      padding: 30px;
    }
    
    .loading-state {
      padding: 100px 20px;
      text-align: center;
      color: var(--muted);
    }
    
    .spinner-ring {
      width: 40px; height: 40px;
      border: 3px solid var(--border);
      border-top-color: var(--sage);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 20px auto;
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }
  </style>
</head>
<body>

  <div class="recipe-page-container">
    <div class="recipe-page-header">
      <a href="javascript:window.close();" class="back-btn" id="btn-back">
        <i class="ti ti-arrow-left"></i> Close Tab
      </a>
      <h1 id="page-title">Loading Recipe...</h1>
      <div style="width:100px;"></div> <!-- Spacer -->
    </div>

    <div id="recipe-content">
      <div class="loading-state">
        <div class="spinner-ring"></div>
        <p>Fetching recipe details...</p>
      </div>
    </div>
  </div>

  <script src="../assets/js/api.js?v=2"></script>
  <script>
    function esc(str) {
      if (!str) return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    async function loadRecipe() {
      const params = new URLSearchParams(window.location.search);
      const isLocal = params.get('local');
      const id = params.get('id');
      
      let mealData = null;

      try {
        if (isLocal) {
          const stored = localStorage.getItem('temp_recipe');
          if (!stored) throw new Error("Local recipe data missing.");
          mealData = JSON.parse(stored);
          renderClinicalMeal(mealData);
        } else if (id) {
          const res = await API.getETMFood(id);
          renderETMMeal(res);
        } else {
          throw new Error("No recipe ID specified.");
        }
      } catch (e) {
        document.getElementById('recipe-content').innerHTML = `
          <div style="text-align:center; padding: 50px; color: var(--danger);">
            <i class="ti ti-alert-circle" style="font-size:40px; margin-bottom:10px;"></i>
            <h3>Error loading recipe</h3>
            <p>${esc(e.message)}</p>
          </div>
        `;
        document.getElementById('page-title').textContent = "Error";
      }
    }

    function renderETMMeal(food) {
      document.getElementById('page-title').textContent = food.name;
      
      const pG = parseFloat(food.protein) || 0;
      const cG = parseFloat(food.total_carbs) || 0;
      const fG = parseFloat(food.total_fat) || 0;
      const cal = parseFloat(food.calories) || 0;

      const pCal = pG * 4, cCal = cG * 4, fCal = fG * 9;
      const totalMacroCal = pCal + cCal + fCal || 1;
      const cPct = Math.round((cCal / totalMacroCal) * 100);
      const fPct = Math.round((fCal / totalMacroCal) * 100);
      const pPct = 100 - cPct - fPct;

      const descText = `1 serving contains <strong>${Math.round(cal)} Calories</strong>. Breakdown: <strong>${cPct}% carbs</strong>, <strong>${fPct}% fat</strong>, <strong>${pPct}% protein</strong>.`;

      let instructionsHtml = '';
      if (food.directions && food.directions.length > 0) {
        instructionsHtml = `
        <div class="etm-section">
          <h3 class="etm-section-title"><i class="ti ti-chef-hat"></i> Preparation Steps</h3>
          <ol class="etm-directions-list">
            ${food.directions.map((d, idx) => {
              const text = (typeof d === 'string') ? d : (d.text || d.step || '');
              return text ? `<li class="etm-direction-step">${esc(text)}</li>` : '';
            }).join('')}
          </ol>
        </div>`;
      }

      let ingredientsHtml = '';
      if (food.ingredients && food.ingredients.length > 0) {
        ingredientsHtml = `
        <div class="etm-section">
          <h3 class="etm-section-title"><i class="ti ti-list"></i> Ingredients</h3>
          <div class="etm-ingredients-grid">
            ${food.ingredients.map(ing => `
              <div class="etm-ingredient-item">
                <div class="etm-ingredient-thumb">
                  ${ing.image_url ? `<img src="${ing.image_url}" alt="${esc(ing.name)}">` : '<span class="etm-ing-fallback">🥄</span>'}
                </div>
                <div class="etm-ingredient-info">
                  <span class="etm-ingredient-name">${esc(ing.name || '')}</span>
                  <span class="etm-ingredient-amount">${esc(ing.quantity + ' ' + (ing.units || ''))}</span>
                </div>
              </div>
            `).join('')}
          </div>
        </div>`;
      }

      document.getElementById('recipe-content').innerHTML = `
        <div class="etm-detail-header">
          <div class="etm-image-wrap">
            <img src="${food.image_url || 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800&q=80'}" alt="${esc(food.name)}" class="etm-food-image" onerror="this.src='../assets/images/default-meal.png'">
          </div>
          <div class="macro-chart-wrap">
            <div class="macro-donut" style="background: conic-gradient(var(--amber) 0% ${cPct}%, var(--sky) ${cPct}% ${cPct + fPct}%, var(--coral) ${cPct + fPct}% 100%);"></div>
            <div class="macro-donut-center">Percent<br>Calories</div>
            <div class="macro-donut-legend">
              <div><span class="legend-dot" style="background:var(--amber)"></span> Carbs ${cPct}%</div>
              <div><span class="legend-dot" style="background:var(--sky)"></span> Fat ${fPct}%</div>
              <div><span class="legend-dot" style="background:var(--coral)"></span> Protein ${pPct}%</div>
            </div>
          </div>
        </div>
        
        <p class="etm-description" style="font-size:16px;">${descText}</p>

        ${ingredientsHtml}
        ${instructionsHtml}
      `;
    }

    function renderClinicalMeal(meal) {
      document.getElementById('page-title').textContent = meal.name;
      
      const pG = parseFloat(meal.nutrition.protein) || 0;
      const cG = parseFloat(meal.nutrition.carbs) || 0;
      const fG = parseFloat(meal.nutrition.fat) || 0;
      const cal = parseFloat(meal.nutrition.calories) || 0;

      const pCal = pG * 4, cCal = cG * 4, fCal = fG * 9;
      const totalMacroCal = pCal + cCal + fCal || 1;
      const cPct = Math.round((cCal / totalMacroCal) * 100);
      const fPct = Math.round((fCal / totalMacroCal) * 100);
      const pPct = 100 - cPct - fPct;

      const descText = `1 serving contains <strong>${Math.round(cal)} Calories</strong>. Breakdown: <strong>${cPct}% carbs</strong>, <strong>${fPct}% fat</strong>, <strong>${pPct}% protein</strong>.`;

      let instructionsHtml = '';
      let steps = [];
      if (Array.isArray(meal.instructions)) {
          steps = meal.instructions;
      } else if (typeof meal.instructions === 'string') {
          steps = meal.instructions.split('\\n\\n');
      }
      
      if (steps.length > 0) {
        instructionsHtml = `
        <div class="etm-section">
          <h3 class="etm-section-title"><i class="ti ti-chef-hat"></i> Preparation Steps</h3>
          <ol class="etm-directions-list">
            ${steps.filter(s => s.trim()).map((s, idx) => `<li class="etm-direction-step">${esc(s.replace('Step '+ (idx+1)+ ':', '').replace((idx+1)+'.', '').trim())}</li>`).join('')}
          </ol>
        </div>`;
      }

      let ingredientsHtml = '';
      if (meal.personalized_ingredients && meal.personalized_ingredients.length > 0) {
        ingredientsHtml = `
        <div class="etm-section">
          <h3 class="etm-section-title"><i class="ti ti-list"></i> Personalized Ingredients</h3>
          <div style="font-size:13px; color:var(--muted); margin-bottom:16px;">Dynamically scaled for your medical needs</div>
          <div class="etm-ingredients-grid">
            ${meal.personalized_ingredients.map(ing => `
              <div class="etm-ingredient-item">
                <div class="etm-ingredient-thumb">
                  <span class="etm-ing-fallback">🥄</span>
                </div>
                <div class="etm-ingredient-info">
                  <span class="etm-ingredient-name">${esc(ing.name || '')}</span>
                  <span class="etm-ingredient-amount">${esc(ing.quantity + ' ' + ing.unit)}</span>
                </div>
              </div>
            `).join('')}
          </div>
        </div>`;
      }

      let diseaseNotesHtml = '';
      if(meal.disease_notes && meal.disease_notes.length > 0) {
        diseaseNotesHtml = `
        <div class="etm-section" style="background:var(--cream); padding:20px; border-radius:12px;">
          <h5 style="font-size:16px; margin-bottom: 12px; color:var(--forest); font-family:'Playfair Display',serif;">⚕️ Medical Constraints Applied</h5>
          ${meal.disease_notes.map(n => `<div style="font-size:14px; color:var(--text); margin-bottom:6px;">• ${esc(n)}</div>`).join('')}
        </div>`;
      }

      document.getElementById('recipe-content').innerHTML = `
        <div class="etm-detail-header">
          <div class="etm-image-wrap">
            <img src="${meal.image_url || 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800&q=80'}" alt="${esc(meal.name)}" class="etm-food-image" onerror="this.src='../assets/images/default-meal.png'">
          </div>
          <div class="macro-chart-wrap">
            <div class="macro-donut" style="background: conic-gradient(var(--amber) 0% ${cPct}%, var(--sky) ${cPct}% ${cPct + fPct}%, var(--coral) ${cPct + fPct}% 100%);"></div>
            <div class="macro-donut-center">Percent<br>Calories</div>
            <div class="macro-donut-legend">
              <div><span class="legend-dot" style="background:var(--amber)"></span> Carbs ${cPct}%</div>
              <div><span class="legend-dot" style="background:var(--sky)"></span> Fat ${fPct}%</div>
              <div><span class="legend-dot" style="background:var(--coral)"></span> Protein ${pPct}%</div>
            </div>
          </div>
        </div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:24px;">
          <p class="etm-description" style="margin:0; font-size:16px;">${descText}</p>
          <div class="health-score-circle" style="margin:0; width:70px; height:70px;">
            <span id="modal-score" style="font-size:24px;">${meal.health_compatibility_score || 'N/A'}</span>
            <span style="font-size:11px;">SCORE</span>
          </div>
        </div>

        ${diseaseNotesHtml}
        ${ingredientsHtml}
        ${instructionsHtml}
      `;
    }

    document.addEventListener('DOMContentLoaded', loadRecipe);
  </script>
</body>
</html>
