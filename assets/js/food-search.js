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
      searchFoods(q);
    }, 600);
  });

  searchClear?.addEventListener('click', () => {
    searchInput.value = '';
    searchClear.style.display = 'none';
    document.getElementById('food-grid').innerHTML = `
      <div class="empty-state-intro">
        <i class="ti ti-database-search" style="font-size: 54px; color: var(--sage); margin-bottom: 16px; display: block;"></i>
        <h3>Explore the Food Library</h3>
        <p style="color: var(--muted); max-width: 380px; margin: 8px auto 0 auto;">Type any food name in the search bar above to fetch accurate nutrition details directly from our databases.</p>
      </div>
    `;
    document.getElementById('result-count').textContent = '';
  });

  searchBtn?.addEventListener('click', () => {
    searchFoods(searchInput.value);
  });

  searchInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      clearTimeout(searchTimer);
      searchFoods(searchInput.value);
    }
  });

  // Quick Chips search clicks
  document.querySelectorAll('.quick-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      const q = chip.dataset.query;
      if (searchInput) {
        searchInput.value = q;
      }
      if (searchClear) {
        searchClear.style.display = 'flex';
      }
      searchFoods(q);
    });
  });
});
