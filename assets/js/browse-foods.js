/**
 * Browse Foods – JS Controller
 * Loads ETM food list, renders sortable table, opens rich detail modal.
 */

/* ───── State ───── */
let browseState = {
  query: '',
  sort: 'name',
  order: 'asc',
  page: 1,
  limit: 20
};

let debounceTimer = null;

/* ───── Init ───── */
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('browse-search-input');
  const searchClear = document.getElementById('browse-search-clear');
  const searchBtn   = document.getElementById('browse-search-btn');
  const sortSelect  = document.getElementById('browse-sort');

  // Debounced search
  searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    searchClear.style.display = searchInput.value ? 'flex' : 'none';
    debounceTimer = setTimeout(() => {
      browseState.query = searchInput.value.trim();
      browseState.page = 1;
      loadFoods();
    }, 400);
  });

  // Clear button
  searchClear.addEventListener('click', () => {
    searchInput.value = '';
    searchClear.style.display = 'none';
    browseState.query = '';
    browseState.page = 1;
    loadFoods();
  });

  // Search button click
  searchBtn.addEventListener('click', () => {
    clearTimeout(debounceTimer);
    browseState.query = searchInput.value.trim();
    browseState.page = 1;
    loadFoods();
  });

  // Enter key
  searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      clearTimeout(debounceTimer);
      browseState.query = searchInput.value.trim();
      browseState.page = 1;
      loadFoods();
    }
  });

  // Sort dropdown
  sortSelect.addEventListener('change', () => {
    browseState.sort = sortSelect.value;
    browseState.page = 1;
    loadFoods();
  });

  // Initial load
  loadFoods();
});


/* ───── Load Foods ───── */
async function loadFoods() {
  const container = document.getElementById('browse-table-container');
  const { query, sort, order, page, limit } = browseState;

  container.innerHTML = `
    <div class="browse-loading">
      <div class="browse-spinner"></div>
      <p>Loading foods...</p>
    </div>
  `;

  try {
    const params = `q=${encodeURIComponent(query)}&sort=${sort}&order=${order}&page=${page}&limit=${limit}`;
    const data = await API.call(`/etm/search_etm.php?${params}`);

    const foods      = data.foods || [];
    const total      = data.total || 0;
    const totalPages = data.total_pages || Math.ceil(total / limit) || 1;

    if (foods.length === 0) {
      container.innerHTML = `
        <div class="browse-empty-state">
          <i class="ti ti-search-off"></i>
          <h3>No Foods Found</h3>
          <p>Try a different search term or clear your filters.</p>
        </div>
      `;
      return;
    }

    renderFoodTable(foods, total, page, totalPages);
  } catch (err) {
    container.innerHTML = `
      <div class="browse-empty-state">
        <i class="ti ti-alert-triangle"></i>
        <h3>Something went wrong</h3>
        <p>${err.message || 'Failed to load foods. Please try again.'}</p>
      </div>
    `;
  }
}


/* ───── Render Food Table ───── */
function renderFoodTable(foods, total, page, totalPages) {
  const container = document.getElementById('browse-table-container');
  const { sort, order } = browseState;

  const sortIcon = (col) => {
    if (sort !== col) return '<span class="sort-arrows">↕</span>';
    return order === 'asc'
      ? '<span class="sort-arrows active">↑</span>'
      : '<span class="sort-arrows active">↓</span>';
  };

  let html = `
    <div class="browse-results-info">
      <span>Showing <strong>${(page - 1) * browseState.limit + 1}–${Math.min(page * browseState.limit, total)}</strong> of <strong>${total}</strong> foods</span>
    </div>
    <div class="browse-table-wrap">
      <table class="browse-table">
        <thead>
          <tr>
            <th class="col-food sortable" data-sort="name" onclick="handleSort('name')">
              Food ${sortIcon('name')}
            </th>
            <th class="col-cal sortable" data-sort="calories" onclick="handleSort('calories')">
              Calories ${sortIcon('calories')}
            </th>
            <th class="col-carbs sortable" data-sort="carbs" onclick="handleSort('carbs')">
              Carbs ${sortIcon('carbs')}
            </th>
            <th class="col-fat sortable" data-sort="fat" onclick="handleSort('fat')">
              Fat ${sortIcon('fat')}
            </th>
            <th class="col-protein sortable" data-sort="protein" onclick="handleSort('protein')">
              Protein ${sortIcon('protein')}
            </th>
          </tr>
        </thead>
        <tbody>
  `;

  foods.forEach(food => {
    const thumb = food.image
      ? `<img src="${food.image}" alt="${food.name}" class="food-thumb" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><rect width=%2260%22 height=%2260%22 fill=%22%23f0ebe3%22/><text x=%2230%22 y=%2235%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2220%22>🍽</text></svg>'">`
      : `<div class="food-thumb-placeholder">🍽️</div>`;

    html += `
      <tr class="food-row" onclick="showETMFoodDetail(${food.id})">
        <td class="col-food">
          <div class="food-cell">
            ${thumb}
            <span class="food-name">${food.name || 'Unknown'}</span>
          </div>
        </td>
        <td class="col-cal"><strong>${Math.round(food.calories || 0)}</strong> kcal</td>
        <td class="col-carbs">${(food.carbs || 0).toFixed(1)}g</td>
        <td class="col-fat">${(food.fat || 0).toFixed(1)}g</td>
        <td class="col-protein">${(food.protein || 0).toFixed(1)}g</td>
      </tr>
    `;
  });

  html += `
        </tbody>
      </table>
    </div>
  `;

  // Pagination
  html += `
    <div class="browse-pagination">
      <button class="btn btn-outline btn-sm" ${page <= 1 ? 'disabled' : ''} onclick="goToPage(${page - 1})">
        <i class="ti ti-chevron-left"></i> Previous
      </button>
      <span class="page-info">Page <strong>${page}</strong> of <strong>${totalPages}</strong></span>
      <button class="btn btn-outline btn-sm" ${page >= totalPages ? 'disabled' : ''} onclick="goToPage(${page + 1})">
        Next <i class="ti ti-chevron-right"></i>
      </button>
    </div>
  `;

  container.innerHTML = html;
}


/* ───── Sort Handler ───── */
function handleSort(col) {
  if (browseState.sort === col) {
    browseState.order = browseState.order === 'asc' ? 'desc' : 'asc';
  } else {
    browseState.sort = col;
    browseState.order = 'asc';
  }
  // Sync the dropdown
  const sortSelect = document.getElementById('browse-sort');
  if (sortSelect) sortSelect.value = col;

  browseState.page = 1;
  loadFoods();
}


/* ───── Pagination ───── */
function goToPage(p) {
  if (p < 1) return;
  browseState.page = p;
  loadFoods();
  // Scroll to top of table
  document.getElementById('browse-table-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}


/* ───── ETM Food Detail Modal ───── */
async function showETMFoodDetail(foodId) {
  if (!foodId) return;
  window.open('recipe.php?id=' + foodId, '_blank');
}


function renderETMFoodDetail(food) {
  const body = document.getElementById('etm-modal-body');

  const cal     = Math.round(food.calories || 0);
  const protein = parseFloat(food.protein || 0);
  const carbs   = parseFloat(food.carbs || 0);
  const fat     = parseFloat(food.fat || 0);
  const totalCal = (protein * 4) + (carbs * 4) + (fat * 9) || 1;
  const pPct = Math.round((protein * 4) / totalCal * 100);
  const cPct = Math.round((carbs * 4) / totalCal * 100);
  const fPct = 100 - pPct - cPct;

  const foodImg = food.image || '';
  const servingWeight = food.serving_weight || food.total_weight || 0;

  let html = '';

  // ── Header: Image + Macro Chart ──
  html += `
    <div class="etm-detail-header">
      <div class="etm-image-wrap">
        ${foodImg
          ? `<img src="${foodImg}" alt="${food.name}" class="etm-food-image">`
          : `<div class="etm-food-image-placeholder"><i class="ti ti-photo"></i></div>`
        }
      </div>
      <div class="etm-chart-side">
        ${renderMacroPieChart(protein, carbs, fat)}
        <div class="etm-macro-summary">
          <div class="macro-stat">
            <span class="macro-val" style="color:var(--amber)">${cal}</span>
            <span class="macro-label">Calories</span>
          </div>
          <div class="macro-stat">
            <span class="macro-val" style="color:var(--coral)">${protein.toFixed(1)}g</span>
            <span class="macro-label">Protein</span>
          </div>
          <div class="macro-stat">
            <span class="macro-val" style="color:var(--amber)">${carbs.toFixed(1)}g</span>
            <span class="macro-label">Carbs</span>
          </div>
          <div class="macro-stat">
            <span class="macro-val" style="color:var(--sky)">${fat.toFixed(1)}g</span>
            <span class="macro-label">Fat</span>
          </div>
        </div>
      </div>
    </div>
  `;

  // ── Description ──
  html += `
    <div class="etm-description">
      <p>1 serving of <strong>${food.name}</strong> contains <strong>${cal} Calories</strong>.
      The macronutrient breakdown is <strong>${cPct}%</strong> carbs, <strong>${fPct}%</strong> fat,
      and <strong>${pPct}%</strong> protein.</p>
    </div>
  `;

  // ── Recipe Badges ──
  if (food.makes || food.prep_time || food.cook_time) {
    html += `<div class="etm-recipe-badges">`;
    if (food.makes) {
      html += `<div class="etm-recipe-badge"><i class="ti ti-users"></i> Makes ${food.makes}</div>`;
    }
    if (food.prep_time) {
      html += `<div class="etm-recipe-badge"><i class="ti ti-clock"></i> Prep ${food.prep_time}</div>`;
    }
    if (food.cook_time) {
      html += `<div class="etm-recipe-badge"><i class="ti ti-flame"></i> Cook ${food.cook_time}</div>`;
    }
    html += `</div>`;
  }

  // ── Ingredients ──
  if (food.ingredients && food.ingredients.length > 0) {
    html += `
      <div class="etm-ingredients-section">
        <h4><i class="ti ti-list-check"></i> Ingredients</h4>
        <div class="etm-ingredients-grid">
    `;
    food.ingredients.forEach(ing => {
      const ingImg = ing.image
        ? `<img src="https://www.eatthismuch.com${ing.image}" alt="${ing.food_name || ''}" class="ingredient-thumb" onerror="this.style.display='none'">`
        : '';
      html += `
        <div class="etm-ingredient-item">
          ${ingImg}
          <div class="ingredient-info">
            <span class="ingredient-name">${ing.food_name || ing.name || 'Ingredient'}</span>
            <span class="ingredient-amount">${ing.amount || ''} ${ing.unit || ''}</span>
          </div>
        </div>
      `;
    });
    html += `</div></div>`;
  }

  // ── Directions ──
  if (food.directions && food.directions.length > 0) {
    html += `
      <div class="etm-directions-section">
        <h4><i class="ti ti-chef-hat"></i> Directions</h4>
        <ol class="etm-directions-list">
    `;
    food.directions.forEach((step, i) => {
      const stepText = typeof step === 'string' ? step : (step.text || step.step || '');
      html += `
        <li class="etm-direction-step">
          <span class="step-number">${i + 1}</span>
          <span class="step-text">${stepText}</span>
        </li>
      `;
    });
    html += `</ol></div>`;
  }

  // ── Nutrition Facts ──
  html += renderNutritionFacts(food);

  // ── Action Buttons ──
  html += `
    <div class="etm-actions">
      <button class="btn btn-primary" onclick="logETMFood(${food.id || 0})">
        <i class="ti ti-plus"></i> Log Food
      </button>
      <button class="btn btn-outline" onclick="addETMToMealPlan(${food.id || 0})">
        <i class="ti ti-calendar-plus"></i> Add to Meal Plan
      </button>
    </div>
  `;

  body.innerHTML = html;
}


/* ───── Macro Pie Chart (conic-gradient) ───── */
function renderMacroPieChart(proteinG, carbsG, fatG) {
  const pCal = proteinG * 4;
  const cCal = carbsG * 4;
  const fCal = fatG * 9;
  const total = pCal + cCal + fCal || 1;
  const pPct = Math.round(pCal / total * 100);
  const cPct = Math.round(cCal / total * 100);
  const fPct = 100 - pPct - cPct;

  return `
    <div class="macro-chart-wrap">
      <div class="macro-donut" style="background: conic-gradient(
        var(--amber) 0% ${cPct}%,
        var(--sky) ${cPct}% ${cPct + fPct}%,
        var(--coral) ${cPct + fPct}% 100%
      );"></div>
      <div class="macro-donut-center">Percent<br>Calories</div>
      <div class="macro-donut-legend">
        <div><span class="legend-dot" style="background:var(--amber)"></span> Carbs ${cPct}%</div>
        <div><span class="legend-dot" style="background:var(--sky)"></span> Fat ${fPct}%</div>
        <div><span class="legend-dot" style="background:var(--coral)"></span> Protein ${pPct}%</div>
      </div>
    </div>
  `;
}


/* ───── Nutrition Facts Panel ───── */
function renderNutritionFacts(food) {
  const n = food.nutrition || food;
  const servingLabel = food.name || 'this food';
  const servingGrams = food.serving_weight || food.total_weight || '';

  const dvCalc = (val, dv) => dv > 0 ? Math.round((val / dv) * 100) + '%' : '—';

  // Daily values for reference (FDA 2000 cal diet)
  const DV = {
    fat: 78, satFat: 20, cholesterol: 300, sodium: 2300,
    carbs: 275, fiber: 28, sugar: 50, protein: 50,
    calcium: 1300, iron: 18, potassium: 4700,
    vitD: 20, vitA: 900, vitC: 90
  };

  const val = (key, fallback) => {
    const v = n[key] ?? food[key] ?? fallback;
    return v !== undefined && v !== null ? v : fallback;
  };

  const calories   = Math.round(val('calories', 0));
  const totalFat   = parseFloat(val('fat', 0));
  const satFat     = parseFloat(val('saturated_fat', 0));
  const transFat   = parseFloat(val('trans_fat', 0));
  const cholesterol = parseFloat(val('cholesterol', 0));
  const sodium     = parseFloat(val('sodium', 0));
  const totalCarbs = parseFloat(val('carbs', 0));
  const fiber      = parseFloat(val('fiber', 0));
  const sugar      = parseFloat(val('sugar', 0));
  const netCarbs   = Math.max(0, totalCarbs - fiber);
  const protein    = parseFloat(val('protein', 0));
  const calcium    = parseFloat(val('calcium', 0));
  const iron       = parseFloat(val('iron', 0));
  const potassium  = parseFloat(val('potassium', 0));
  const vitD       = parseFloat(val('vitamin_d', 0));
  const vitA       = parseFloat(val('vitamin_a', 0));
  const vitC       = parseFloat(val('vitamin_c', 0));

  return `
    <div class="nutrition-facts-panel">
      <div class="nf-title">Nutrition Facts</div>
      <div class="nf-serving">For 1 serving of ${servingLabel}${servingGrams ? ' (' + Math.round(servingGrams) + 'g)' : ''}</div>
      <div class="nf-thick-border"></div>

      <div class="nf-row nf-row-major">
        <span class="nf-label"><strong>Calories</strong></span>
        <span class="nf-value"><strong>${calories}</strong></span>
      </div>
      <div class="nf-divider-thick"></div>

      <div class="nf-row-header">
        <span></span><span>% Daily Value*</span>
      </div>

      <div class="nf-row">
        <span class="nf-label"><strong>Total Fat</strong> ${totalFat.toFixed(1)}g</span>
        <span class="nf-dv"><strong>${dvCalc(totalFat, DV.fat)}</strong></span>
      </div>
      <div class="nf-row nf-indent">
        <span class="nf-label">Saturated Fat ${satFat.toFixed(1)}g</span>
        <span class="nf-dv">${dvCalc(satFat, DV.satFat)}</span>
      </div>
      <div class="nf-row nf-indent">
        <span class="nf-label"><em>Trans</em> Fat ${transFat.toFixed(1)}g</span>
        <span class="nf-dv"></span>
      </div>

      <div class="nf-row">
        <span class="nf-label"><strong>Cholesterol</strong> ${Math.round(cholesterol)}mg</span>
        <span class="nf-dv"><strong>${dvCalc(cholesterol, DV.cholesterol)}</strong></span>
      </div>

      <div class="nf-row">
        <span class="nf-label"><strong>Sodium</strong> ${Math.round(sodium)}mg</span>
        <span class="nf-dv"><strong>${dvCalc(sodium, DV.sodium)}</strong></span>
      </div>

      <div class="nf-row">
        <span class="nf-label"><strong>Total Carbohydrate</strong> ${totalCarbs.toFixed(1)}g</span>
        <span class="nf-dv"><strong>${dvCalc(totalCarbs, DV.carbs)}</strong></span>
      </div>
      <div class="nf-row nf-indent">
        <span class="nf-label">Dietary Fiber ${fiber.toFixed(1)}g</span>
        <span class="nf-dv">${dvCalc(fiber, DV.fiber)}</span>
      </div>
      <div class="nf-row nf-indent">
        <span class="nf-label">Total Sugars ${sugar.toFixed(1)}g</span>
        <span class="nf-dv">${dvCalc(sugar, DV.sugar)}</span>
      </div>
      <div class="nf-row nf-indent">
        <span class="nf-label">Net Carbs ${netCarbs.toFixed(1)}g</span>
        <span class="nf-dv"></span>
      </div>

      <div class="nf-divider-thick"></div>

      <div class="nf-row">
        <span class="nf-label"><strong>Protein</strong> ${protein.toFixed(1)}g</span>
        <span class="nf-dv"><strong>${dvCalc(protein, DV.protein)}</strong></span>
      </div>

      <div class="nf-divider-thick"></div>

      <div class="nf-vitamins-header">Vitamins & Minerals</div>
      <div class="nf-row">
        <span class="nf-label">Calcium ${Math.round(calcium)}mg</span>
        <span class="nf-dv">${dvCalc(calcium, DV.calcium)}</span>
      </div>
      <div class="nf-row">
        <span class="nf-label">Iron ${iron.toFixed(1)}mg</span>
        <span class="nf-dv">${dvCalc(iron, DV.iron)}</span>
      </div>
      <div class="nf-row">
        <span class="nf-label">Potassium ${Math.round(potassium)}mg</span>
        <span class="nf-dv">${dvCalc(potassium, DV.potassium)}</span>
      </div>
      <div class="nf-row">
        <span class="nf-label">Vitamin D ${vitD.toFixed(1)}mcg</span>
        <span class="nf-dv">${dvCalc(vitD, DV.vitD)}</span>
      </div>
      <div class="nf-row">
        <span class="nf-label">Vitamin A ${Math.round(vitA)}mcg</span>
        <span class="nf-dv">${dvCalc(vitA, DV.vitA)}</span>
      </div>
      <div class="nf-row">
        <span class="nf-label">Vitamin C ${vitC.toFixed(1)}mg</span>
        <span class="nf-dv">${dvCalc(vitC, DV.vitC)}</span>
      </div>

      <div class="nf-footnote">
        * Percent Daily Values are based on a 2,000 calorie diet.
      </div>
    </div>
  `;
}


/* ───── Close Modal ───── */
function closeETMModal() {
  document.getElementById('etm-detail-modal')?.classList.remove('open');
}

// Close on backdrop click
document.addEventListener('click', (e) => {
  const modal = document.getElementById('etm-detail-modal');
  if (modal && e.target === modal) {
    closeETMModal();
  }
});

// Close on Escape
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeETMModal();
});


/* ───── Action Stubs ───── */
function logETMFood(foodId) {
  if (typeof showToast === 'function') {
    showToast('Food logged successfully!', 'success');
  }
  closeETMModal();
}

function addETMToMealPlan(foodId) {
  if (typeof showToast === 'function') {
    showToast('Added to meal plan!', 'success');
  }
  closeETMModal();
}
