let searchTimer = null;

async function searchFoods(query) {
  if (!query.trim()) return;
  const grid = document.getElementById('food-grid');
  const counter = document.getElementById('result-count');

  grid.innerHTML = '<div class="loading-state"><div class="spinner-ring"></div><p>Searching food database...</p></div>';
  counter.textContent = 'Searching...';

  try {
    const data = await API.searchFood(query, 20);
    counter.textContent = data.totalHits.toLocaleString() + ' foods found';
    renderFoodCards(data.foods, grid);
  } catch (e) {
    grid.innerHTML = `
      <div class="error-state" style="grid-column: span 3; text-align: center; padding: 40px;">
        <i class="ti ti-alert-triangle" style="font-size: 48px; color: var(--danger); margin-bottom: 16px;"></i>
        <h3 style="color: var(--danger)">Search Failed</h3>
        <p style="margin: 8px 0; color: var(--muted);">${e.message}</p>
      </div>
    `;
    counter.textContent = 'Error';
  }
}

function renderFoodCards(foods, container) {
  if (!foods || !foods.length) {
    container.innerHTML = `
      <div class="empty-state" style="grid-column: span 3; text-align: center; padding: 60px 0;">
        <i class="ti ti-mood-empty" style="font-size: 54px; color: var(--sage); margin-bottom: 16px;"></i>
        <h3>No Foods Found</h3>
        <p style="color: var(--muted)">Try different keywords, check for spelling, or explore other query parameters.</p>
      </div>
    `;
    return;
  }

  container.innerHTML = foods.map(f => {
    const safeDescription = f.description.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    return `
      <div class="food-card card hover-scale animate-in" data-fdc="${f.fdcId}">
        <div class="food-card-header">
          <div class="food-emoji">${getFoodEmoji(f.description)}</div>
          <div class="food-info">
            <h4 class="food-name" title="${escHtml(f.description)}">${escHtml(f.description)}</h4>
            <span class="food-type">${f.dataType || 'Product'}</span>
            ${f.brandOwner ? `<span class="food-brand">${escHtml(f.brandOwner)}</span>` : ''}
          </div>
        </div>
        
        <div class="food-macros">
          <div class="macro-chip macro-cal">
            <span>${f.calories}</span>
            <label>kcal</label>
          </div>
          <div class="macro-chip macro-prot">
            <span>${f.protein_g}g</span>
            <label>Protein</label>
          </div>
          <div class="macro-chip macro-carb">
            <span>${f.carbs_g}g</span>
            <label>Carbs</label>
          </div>
          <div class="macro-chip macro-fat">
            <span>${f.fat_g}g</span>
            <label>Fat</label>
          </div>
        </div>
        
        <div class="food-serving">Per ${escHtml(f.servingSize || '100 g')}</div>
        
        <div class="food-actions">
          <button class="btn btn-outline btn-sm" onclick="viewFoodDetail(${f.fdcId})">View Details</button>
          <button class="btn btn-lime btn-sm" onclick="addToLog(${f.fdcId}, '${safeDescription}', ${f.calories}, ${f.protein_g}, ${f.carbs_g}, ${f.fat_g}, '${escHtml(f.servingSize || '100 g').replace(/'/g, "\\'")}')">
            + Log Food
          </button>
        </div>
      </div>
    `;
  }).join('');
}

// Map food name descriptors to relevant visual emojis
function getFoodEmoji(name) {
  const n = name.toLowerCase();
  if (/chicken|poultry|turkey/.test(n)) return '🍗';
  if (/beef|steak|pork|mutton|meat/.test(n)) return '🥩';
  if (/fish|salmon|tuna|shrimp|seafood/.test(n)) return '🐟';
  if (/egg/.test(n)) return '🥚';
  if (/milk|dairy|butter|whey|protein powder/.test(n)) return '🥛';
  if (/rice|quinoa|grain/.test(n)) return '🍚';
  if (/bread|toast|bun/.test(n)) return '🍞';
  if (/pasta|spaghetti|noodle/.test(n)) return '🍝';
  if (/apple|pear|peach/.test(n)) return '🍎';
  if (/banana/.test(n)) return '🍌';
  if (/orange|lemon|citrus/.test(n)) return '🍊';
  if (/berry|strawberry|blueberry|raspberry/.test(n)) return '🍓';
  if (/broccoli|cabbage|cauliflower/.test(n)) return '🥦';
  if (/salad|spinach|lettuce|cucumber/.test(n)) return '🥗';
  if (/oat|porridge|cereal/.test(n)) return '🥣';
  if (/cheese/.test(n)) return '🧀';
  if (/yogurt|curd/.test(n)) return '🍦';
  if (/almond|nut|cashew|peanut|walnut/.test(n)) return '🥜';
  if (/avocado/.test(n)) return '🥑';
  if (/cookie|biscuit|cake|chocolate|candy/.test(n)) return '🍪';
  return '🥘';
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

// Load detail modals on user demand
async function viewFoodDetail(fdcId) {
  const modal = document.getElementById('food-modal');
  const body  = document.getElementById('modal-body');
  
  body.innerHTML = '<div class="loading-state"><div class="spinner-ring"></div><p>Pulling detailed nutrients...</p></div>';
  modal.classList.add('open');
  
  try {
    const f = await API.getFood(fdcId);
    const safeDescription = f.description.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    
    body.innerHTML = `
      <h3 style="font-family:'Playfair Display',serif; color:var(--forest); margin-bottom:4px">${escHtml(f.description)}</h3>
      <p class="food-type" style="color:var(--muted); font-size:12px; margin-bottom: 24px;">${f.dataType} · FDC ID: ${f.fdcId}</p>
      
      <div class="detail-macros">
        <div class="detail-macro" style="border-top-color:var(--text)">
          <span>${f.calories}</span>
          <label>Calories (kcal)</label>
        </div>
        <div class="detail-macro" style="border-top-color:var(--coral)">
          <span>${f.protein_g}g</span>
          <label>Protein</label>
        </div>
        <div class="detail-macro" style="border-top-color:var(--amber)">
          <span>${f.carbs_g}g</span>
          <label>Carbs</label>
        </div>
        <div class="detail-macro" style="border-top-color:var(--sky)">
          <span>${f.fat_g}g</span>
          <label>Fat</label>
        </div>
        <div class="detail-macro" style="border-top-color:var(--sage)">
          <span>${f.fiber_g}g</span>
          <label>Fiber</label>
        </div>
        <div class="detail-macro" style="border-top-color:var(--muted)">
          <span>${f.sugar_g}g</span>
          <label>Sugar</label>
        </div>
        <div class="detail-macro" style="border-top-color:var(--muted)">
          <span>${f.sodium_mg}mg</span>
          <label>Sodium</label>
        </div>
      </div>
      
      <p class="serving-note" style="font-size:12px; color:var(--muted); margin: 20px 0; text-align: center;">Serving Size Reference: <strong>${escHtml(f.servingSize || '100 g')}</strong></p>
      
      <button class="btn btn-lime" style="width:100%; justify-content: center; height: 48px;" 
              onclick="addToLog(${f.fdcId}, '${safeDescription}', ${f.calories}, ${f.protein_g}, ${f.carbs_g}, ${f.fat_g}, '${escHtml(f.servingSize || '100 g').replace(/'/g, "\\'")}'); closeModal()">
        <i class="ti ti-plus"></i> Add to Today's Eaten Log
      </button>
    `;
  } catch (e) {
    body.innerHTML = `
      <div style="text-align:center; padding: 20px 0;">
        <i class="ti ti-alert-triangle" style="font-size: 36px; color: var(--danger);"></i>
        <p style="color:var(--danger); margin-top: 10px;">Failed to retrieve details: ${e.message}</p>
      </div>
    `;
  }
}

function closeModal() {
  document.getElementById('food-modal').classList.remove('open');
}

// Log food eaten directly to backend
async function addToLog(fdcId, name, calories, protein = 0, carbs = 0, fat = 0, serving = '100 g') {
  try {
    const today = new Date().toISOString().split('T')[0];
    await API.logMeal({
      fdc_id: fdcId,
      food_name: name,
      calories: calories,
      protein_g: protein,
      carbs_g: carbs,
      fat_g: fat,
      quantity: 1.0,
      unit: serving,
      log_date: today
    });
    
    showToast(`${name} added to today's log ✓`, 'success');
  } catch (e) {
    showToast('Failed to add: ' + e.message, 'error');
  }
}

// Bind query events
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('search-input');
  const searchClear = document.getElementById('search-clear');
  const searchBtn = document.getElementById('search-btn');

  // Input debouncer logic
  searchInput?.addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    const q = e.target.value;
    
    if (searchClear) {
      searchClear.style.display = q.trim() ? 'flex' : 'none';
    }
    
    if (q.trim().length < 2) return;
    
    searchTimer = setTimeout(() => {
      searchETMFoods(q);
    }, 600);
  });

  searchClear?.addEventListener('click', () => {
    searchInput.value = '';
    searchClear.style.display = 'none';
    document.getElementById('food-grid').innerHTML = `
      <div class="empty-state-intro">
        <i class="ti ti-database-search" style="font-size: 54px; color: var(--sage); margin-bottom: 16px; display: block;"></i>
        <h3>Explore the Food Library</h3>
        <p style="color: var(--muted); max-width: 380px; margin: 8px auto 0 auto;">Type any food name in the search bar above to fetch beautiful, high-quality recipe details.</p>
      </div>
    `;
    document.getElementById('result-count').textContent = '';
  });

  searchBtn?.addEventListener('click', () => {
    searchETMFoods(searchInput.value);
  });

  searchInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      clearTimeout(searchTimer);
      searchETMFoods(searchInput.value);
    }
  });

  // Quick Chips search clicks
  document.querySelectorAll('.quick-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      // ETM chip special handler
      if (chip.dataset.etm) {
        searchETMFoods('');
        return;
      }
      const q = chip.dataset.query;
      if (searchInput) {
        searchInput.value = q;
      }
      if (searchClear) {
        searchClear.style.display = 'flex';
      }
      searchETMFoods(q);
    });
  });
});

/* ─────────────────────────────────────────────────────────
   ETM (EatThisMuch) Food Search & Detail Modal
   ───────────────────────────────────────────────────────── */

async function searchETMFoods(query) {
  const grid = document.getElementById('food-grid');
  const counter = document.getElementById('result-count');

  grid.innerHTML = '<div class="loading-state"><div class="spinner-ring"></div><p>Loading curated recipes...</p></div>';
  counter.textContent = 'Searching...';

  try {
    const data = await API.searchETMFoods(query, 'name', 'asc', 1, 50);
    const foods = data.foods || [];
    counter.textContent = (data.total || foods.length) + ' curated foods';
    renderETMCards(foods, grid);
  } catch (e) {
    grid.innerHTML = `
      <div class="error-state" style="grid-column: span 3; text-align: center; padding: 40px;">
        <i class="ti ti-alert-triangle" style="font-size: 48px; color: var(--danger); margin-bottom: 16px;"></i>
        <h3 style="color: var(--danger)">Failed to Load</h3>
        <p style="margin: 8px 0; color: var(--muted);">${e.message}</p>
        <p style="font-size:12px; color:var(--muted);">Make sure you've run the scraper first: <a href="../api/scrape_etm" target="_blank" style="color:var(--sage)">Run Scraper</a></p>
      </div>
    `;
    counter.textContent = 'Error';
  }
}

function renderETMCards(foods, container) {
  if (!foods || !foods.length) {
    container.innerHTML = `
      <div class="empty-state" style="grid-column: span 3; text-align: center; padding: 60px 0;">
        <i class="ti ti-bowl" style="font-size: 54px; color: var(--sage); margin-bottom: 16px;"></i>
        <h3>No Foods Found</h3>
        <p style="color: var(--muted)">Run the scraper first to populate the database.</p>
        <a href="../api/scrape_etm" target="_blank" class="btn btn-primary" style="margin-top:16px">Run Scraper</a>
      </div>
    `;
    return;
  }

  container.innerHTML = foods.map(f => `
    <div class="food-card etm-food-card card hover-scale animate-in" onclick="showETMFoodDetail(${f.id})" style="cursor:pointer;">
      <div class="etm-card-image-wrap">
        <img src="${escHtml(f.image_url || '')}" alt="${escHtml(f.name)}" class="etm-card-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="etm-card-image-fallback" style="display:none;">${getFoodEmoji(f.name)}</div>
      </div>
      <div class="etm-card-body">
        <h4 class="food-name" title="${escHtml(f.name)}">${escHtml(f.name)}</h4>
        <div class="food-macros">
          <div class="macro-chip macro-cal">
            <span>${Math.round(f.calories || 0)}</span>
            <label>kcal</label>
          </div>
          <div class="macro-chip macro-prot">
            <span>${Math.round(f.protein || 0)}g</span>
            <label>Protein</label>
          </div>
          <div class="macro-chip macro-carb">
            <span>${Math.round(f.total_carbs || 0)}g</span>
            <label>Carbs</label>
          </div>
          <div class="macro-chip macro-fat">
            <span>${Math.round(f.total_fat || 0)}g</span>
            <label>Fat</label>
          </div>
        </div>
        ${f.prep_time_minutes || f.cook_time_minutes ? `
        <div class="etm-card-time">
          <i class="ti ti-clock"></i>
          ${f.total_time_minutes ? f.total_time_minutes + ' min' : (f.prep_time_minutes || 0) + (f.cook_time_minutes || 0) + ' min'}
        </div>` : ''}
      </div>
    </div>
  `).join('');
}

/* ─── ETM Food Detail Modal ─── */
async function showETMFoodDetail(foodId) {
  if (!foodId) return;
  window.location.href = 'recipe?id=' + foodId;
}

function closeETMModal() {
  // Deprecated
}

/* Nutrition Facts helpers */
function nfVal(val, unit) {
  if (val === null || val === undefined || val === '') return '-';
  const n = parseFloat(val);
  if (isNaN(n)) return '-';
  return (n < 10 ? n.toFixed(1) : Math.round(n)) + unit;
}

function nfDV(val, dv) {
  if (val === null || val === undefined || val === '' || !dv) return '';
  const n = parseFloat(val);
  if (isNaN(n)) return '';
  const pct = Math.round(n / dv * 100);
  return pct + '%';
}

function nfVitaminRow(name, val, unit, dv) {
  if (val === null || val === undefined || val === '') return '';
  const n = parseFloat(val);
  if (isNaN(n) || n === 0) return '';
  return `<tr><th>${name}</th><td>${nfVal(val, unit)}</td><td>${nfDV(val, dv)}</td></tr>`;
}


