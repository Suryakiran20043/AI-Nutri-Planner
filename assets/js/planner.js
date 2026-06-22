let currentDate = new Date().toISOString().split('T')[0];
let currentSlotForSwap = '';
let targets = { calories: 2000, protein: 150, carbs: 200, fat: 65 };
let activeProfileDiet = 'anything';

// Setup daily calendar tabs starting from Monday this week
function renderCalendarTabs() {
  const daysContainer = document.getElementById('calendar-days');
  if (!daysContainer) return;

  const daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  
  // Calculate Monday of this week
  const today = new Date();
  const day = today.getDay();
  const diff = today.getDate() - day + (day === 0 ? -6 : 1);
  const monday = new Date(today.setDate(diff));

  let tabsHTML = '';
  for (let i = 0; i < 7; i++) {
    const loopDate = new Date(monday);
    loopDate.setDate(monday.getDate() + i);
    const dateStr = loopDate.toISOString().split('T')[0];
    const isToday = (new Date().toISOString().split('T')[0] === dateStr);
    const isActive = (currentDate === dateStr);

    tabsHTML += `
      <div class="day-pill ${isActive ? 'active' : ''} ${isToday ? 'today' : ''}" 
           onclick="changeSelectedDate('${dateStr}')" style="cursor:pointer; user-select:none;">
        ${daysOfWeek[i]}${isToday ? ' · Today' : ''}
      </div>
    `;
  }
  
  daysContainer.innerHTML = tabsHTML;
}

function changeSelectedDate(date) {
  currentDate = date;
  renderCalendarTabs();
  loadPlanForDate();
}

async function loadPlanForDate() {
  const grid = document.getElementById('planner-meals');
  if (!grid) return;

  grid.innerHTML = '<div class="loading-state" style="grid-column:span 2;"><div class="spinner-ring"></div><p>Retrieving your meal plan...</p></div>';

  try {
    // 1. Fetch user targets
    try {
      const profile = await API.getProfile();
      targets.calories = profile.daily_calories || 2000;
      targets.protein = profile.protein_g || 150;
      targets.carbs = profile.carbs_g || 200;
      targets.fat = profile.fat_g || 65;
      activeProfileDiet = profile.diet_type || 'anything';
    } catch (e) {
      grid.innerHTML = `
        <div class="empty-state card" style="grid-column: span 2; text-align: center; padding: 40px;">
          <i class="ti ti-alert-circle" style="font-size: 48px; color: var(--sage); margin-bottom: 16px;"></i>
          <h3>Targets Not Set</h3>
          <p style="margin: 8px 0 20px 0; color: var(--muted);">Please complete the Calorie Calculator first to establish daily metabolic needs.</p>
          <a href="/AI-Nutri-Planner/pages/calculator.php" class="btn btn-lime">Go to Calculator</a>
        </div>
      `;
      return;
    }

    // 2. Fetch saved plan
    const data = await API.getPlan(currentDate);
    renderMealGrid(data.plan);
    updateDailySummaryTotals(data.plan);

  } catch (e) {
    grid.innerHTML = `<div class="error-state" style="grid-column: span 2;"><p>Loading failed: ${e.message}</p></div>`;
  }
}

function updateDailySummaryTotals(plan) {
  let plannedCal = 0;
  let plannedProt = 0;
  let plannedCarbs = 0;
  let plannedFat = 0;

  const slots = ['breakfast', 'lunch', 'dinner', 'snack'];
  slots.forEach(slot => {
    if (plan && plan[slot]) {
      plannedCal += parseInt(plan[slot].calories) || 0;
      plannedProt += parseFloat(plan[slot].protein) || 0;
      plannedCarbs += parseFloat(plan[slot].carbs) || 0;
      plannedFat += parseFloat(plan[slot].fat) || 0;
    }
  });

  // Daily totals progress bar strip matching mockup
  const percent = Math.min(100, Math.round((plannedCal / targets.calories) * 100)) || 0;
  const bar = document.getElementById('val-day-progress-bar');
  if (bar) bar.style.width = `${percent}%`;

  document.getElementById('val-day-progress-text').textContent = `${plannedCal} / ${targets.calories} kcal today`;

  // Dynamic bottom card total statistics
  document.getElementById('lbl-total-prot').textContent = `Protein ${Math.round(plannedProt)}g`;
  document.getElementById('lbl-total-carbs').textContent = `Carbs ${Math.round(plannedCarbs)}g`;
  document.getElementById('lbl-total-fat').textContent = `Fat ${Math.round(plannedFat)}g`;
  
  document.getElementById('lbl-total-cal').textContent = `${plannedCal.toLocaleString()} kcal`;
}

function renderMealGrid(plan) {
  const grid = document.getElementById('planner-meals');
  const slots = ['breakfast', 'lunch', 'dinner', 'snack'];
  const emojis = { breakfast: '🌅', lunch: '🌞', dinner: '🌙', snack: '🍎' };
  const labels = { breakfast: 'Breakfast', lunch: 'Lunch', dinner: 'Dinner', snack: 'Snack' };
  
  const keywordImages = [
    { key: 'strawberry', url: 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=600&q=80' },
    { key: 'egg', url: 'https://images.unsplash.com/photo-1587486913049-53fc88980cfc?w=600&q=80' },
    { key: 'yogurt', url: 'https://images.unsplash.com/photo-1481391243146-5e913a0c0e5a?w=600&q=80' },
    { key: 'oat', url: 'https://images.unsplash.com/photo-1517673400267-0251440c45dc?w=600&q=80' },
    { key: 'granola', url: 'https://images.unsplash.com/photo-1517673400267-0251440c45dc?w=600&q=80' },
    { key: 'blueberr', url: 'https://images.unsplash.com/photo-1428080922855-87bd63624e52?w=600&q=80' },
    { key: 'milk', url: 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=600&q=80' },
    { key: 'flake', url: 'https://images.unsplash.com/photo-1521406796677-448c90967756?w=600&q=80' },
    { key: 'cheese', url: 'https://images.unsplash.com/photo-1555505019-8c3f1c4aba5f?w=600&q=80' },
    { key: 'chia', url: 'https://images.unsplash.com/photo-1555505019-8c3f1c4aba5f?w=600&q=80' },
    { key: 'peanut', url: 'https://images.unsplash.com/photo-1584852924157-fb9d76e7379f?w=600&q=80' },
    { key: 'bread', url: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&q=80' },
    { key: 'honey', url: 'https://images.unsplash.com/photo-1587049352847-4d4b124a5697?w=600&q=80' },
    { key: 'chicken', url: 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=600&q=80' },
    { key: 'turkey', url: 'https://images.unsplash.com/photo-1574672280600-4accfa5b6f98?w=600&q=80' },
    { key: 'paneer', url: 'https://images.unsplash.com/photo-1565557613262-d27a1f59235e?w=600&q=80' },
    { key: 'avocado', url: 'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=600&q=80' },
    { key: 'lettuce', url: 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80' },
    { key: 'tomato', url: 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=600&q=80' },
    { key: 'cucumber', url: 'https://images.unsplash.com/photo-1604543519968-3e5f1f1d1fb8?w=600&q=80' },
    { key: 'hummus', url: 'https://images.unsplash.com/photo-1625944230945-1b7dd12ce240?w=600&q=80' },
    { key: 'spinach', url: 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=600&q=80' },
    { key: 'wrap', url: 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600&q=80' },
    { key: 'quinoa', url: 'https://images.unsplash.com/photo-1586201375761-83865001e8ac?w=600&q=80' },
    { key: 'chickpea', url: 'https://images.unsplash.com/photo-1515543904379-3d757afe72e4?w=600&q=80' },
    { key: 'olive', url: 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&q=80' },
    { key: 'feta', url: 'https://images.unsplash.com/photo-1559561853-08451507cbe7?w=600&q=80' },
    { key: 'tuna', url: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80' },
    { key: 'salmon', url: 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=600&q=80' },
    { key: 'steak', url: 'https://images.unsplash.com/photo-1432139555190-58524dae6a55?w=600&q=80' },
    { key: 'beef', url: 'https://images.unsplash.com/photo-1432139555190-58524dae6a55?w=600&q=80' },
    { key: 'tofu', url: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80' },
    { key: 'rice', url: 'https://images.unsplash.com/photo-1536304929831-ee1ca9d44906?w=600&q=80' },
    { key: 'lentil', url: 'https://images.unsplash.com/photo-1515543904379-3d757afe72e4?w=600&q=80' },
    { key: 'pasta', url: 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?w=600&q=80' },
    { key: 'potato', url: 'https://images.unsplash.com/photo-1596646194726-5b4fc7c22bfd?w=600&q=80' },
    { key: 'shrimp', url: 'https://images.unsplash.com/photo-1565557613262-d27a1f59235e?w=600&q=80' },
    { key: 'broccoli', url: 'https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?w=600&q=80' },
    { key: 'asparagus', url: 'https://images.unsplash.com/photo-1555541786-89d81d2df0f0?w=600&q=80' },
    { key: 'cod', url: 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80' },
    { key: 'coconut', url: 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=600&q=80' },
    { key: 'cauliflower', url: 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80' },
    { key: 'apple', url: 'https://images.unsplash.com/photo-1560806887-1e4cd0b6fac6?w=600&q=80' },
    { key: 'almond', url: 'https://images.unsplash.com/photo-1508061461528-ce15f91753c1?w=600&q=80' },
    { key: 'cashew', url: 'https://images.unsplash.com/photo-1508061461528-ce15f91753c1?w=600&q=80' },
    { key: 'walnut', url: 'https://images.unsplash.com/photo-1508061461528-ce15f91753c1?w=600&q=80' },
    { key: 'berr', url: 'https://images.unsplash.com/photo-1428080922855-87bd63624e52?w=600&q=80' },
    { key: 'protein', url: 'https://images.unsplash.com/photo-1579722820308-d74e571900a9?w=600&q=80' },
    { key: 'chocolate', url: 'https://images.unsplash.com/photo-1606312619070-d48b4c652a52?w=600&q=80' },
    { key: 'popcorn', url: 'https://images.unsplash.com/photo-1578849278619-e73505e9610f?w=600&q=80' },
    { key: 'carrot', url: 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=600&q=80' },
    { key: 'pistachio', url: 'https://images.unsplash.com/photo-1508061461528-ce15f91753c1?w=600&q=80' },
    { key: 'raisin', url: 'https://images.unsplash.com/photo-1522856339183-5a7071db8c1b?w=600&q=80' },
    { key: 'celery', url: 'https://images.unsplash.com/photo-1604543519968-3e5f1f1d1fb8?w=600&q=80' },
    { key: 'salad', url: 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80' }
  ];

  function getFallbackImage(name) {
    if (!name) return keywordImages[0].url;
    const lowerName = name.toLowerCase();
    for (const item of keywordImages) {
      if (lowerName.includes(item.key)) {
        return item.url;
      }
    }
    // Hash fallback if no keyword matches
    let hash = 0;
    for(let i=0; i<name.length; i++){
      hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return keywordImages[Math.abs(hash) % keywordImages.length].url;
  }

  let gridHTML = '';

  slots.forEach(slot => {
    const meal = plan ? plan[slot] : null;

    if (meal) {
      const displayImage = meal.image_url || getFallbackImage(meal.name);
      gridHTML += `
        <div class="plan-row animate-in">
          <div class="plan-meal-type" style="width: 80px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; flex-shrink: 0; cursor: pointer;" onclick="showETMFoodDetail(${meal.etm_food_id || meal.fdc_id})">
            <img src="${displayImage}" class="plan-meal-thumbnail">
            <span style="font-size: 10px; font-weight: 600; text-transform: uppercase; color: var(--muted);">${labels[slot]}</span>
          </div>
          <div class="plan-meal-content" style="cursor: pointer;"
               onmousemove="showRecipePopover(event, '${escHtml(meal.name).replace(/'/g, "\\'")}', '${meal.instructions ? meal.instructions.replace(/'/g, "\\'").replace(/"/g, '&quot;') : ''}')"
               onmouseleave="hideRecipePopover()"
               onclick="showETMFoodDetail(${meal.etm_food_id || meal.fdc_id})">
            <div class="plan-meal-name">${escHtml(meal.name)}</div>
            <div class="plan-meal-meta">
              <span>🕗 ${slot === 'breakfast' ? '8:00 AM' : (slot === 'lunch' ? '1:00 PM' : (slot === 'dinner' ? '7:30 PM' : '4:00 PM'))}</span>
              <span>⏱ ${slot === 'breakfast' || slot === 'snack' ? '15 min' : '30 min'}</span>
              <span>${meal.is_locked ? '<i class="ti ti-lock"></i> Locked' : '<i class="ti ti-lock-open"></i> Recipe inside'}</span>
            </div>
          </div>
          <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 12px;">
            <div class="plan-cal-badge">${meal.calories} kcal</div>
            <button class="plan-meal-swap-btn" onclick="openSwapSearch('${slot}')" title="Swap Meal">
              <i class="ti ti-arrows-exchange"></i> Swap
            </button>
          </div>
        </div>
      `;
    } else {
      // Empty meal slot fallback card
      gridHTML += `
        <div class="plan-row animate-in" onclick="regenerateSlot('${slot}')" style="cursor: pointer;">
          <div class="plan-meal-type" style="width: 80px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; flex-shrink: 0;">
            <div class="plan-meal-icon">${emojis[slot]}</div>
            <span style="font-size: 10px; font-weight: 600; text-transform: uppercase; color: var(--muted);">${labels[slot]}</span>
          </div>
          <div class="plan-meal-content">
            <div class="plan-meal-name" style="color:var(--muted); font-style:italic">No meal planned for today</div>
            <div class="plan-meal-meta"><span>Tap to autogenerate suggestion</span></div>
          </div>
          <div class="plan-cal-badge">-- kcal</div>
        </div>
      `;
    }
  });

  grid.innerHTML = gridHTML;
}

async function showETMFoodDetail(foodId) {
  if (!foodId) return;
  if (typeof openAIFoodModal === 'function') {
      openAIFoodModal(foodId, "Planner Recipe");
  } else {
      window.open('recipe.php?id=' + foodId, '_blank');
  }
}

function closeETMModal() {
  // Deprecated
}

// Swaps item instantly using quick-algorithm recommendations
async function regenerateSlot(slot) {
  showToast('Swapping meal slot...', 'info');
  try {
    await API.swapMeal({
      action: 'regenerate',
      date: currentDate,
      slot: slot
    });
    showToast('New meal selected successfully ✓', 'success');
    loadPlanForDate();
  } catch (e) {
    showToast('Swapping failed: ' + e.message, 'error');
  }
}

// Regenerate all unlocked meals for the selected date
document.getElementById('btn-generate-day')?.addEventListener('click', async () => {
  const btn = document.getElementById('btn-generate-day');
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner-ring" style="width: 16px; height: 16px; border-width: 2px;"></div>';
  
  try {
    await API.generatePlan(currentDate);
    showToast('Unlocked slots regenerated successfully ✓', 'success');
    loadPlanForDate();
  } catch (e) {
    showToast('Failed to generate: ' + e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '↻ Regenerate day';
  }
});

// Custom Search Swapping modal controllers
function openSwapSearch(slot) {
  currentSlotForSwap = slot;
  const modal = document.getElementById('swap-modal');
  const input = document.getElementById('swap-search-input');
  
  document.getElementById('swap-modal-title').textContent = `Search Recipes to Swap in ${slot.charAt(0).toUpperCase() + slot.slice(1)}`;
  document.getElementById('swap-results-grid').innerHTML = '<div class="empty-state"><p>Type recipes or keywords above to search recipe and food databases...</p></div>';
  
  input.value = '';
  modal.classList.add('open');
  input.focus();
}

function closeSwapModal() {
  document.getElementById('swap-modal').classList.remove('open');
}

// Debounced search logic for swap modal
let modalSearchTimer = null;
async function searchFoodsForModal(query) {
  if (!query.trim()) return;
  const resultsContainer = document.getElementById('swap-results-grid');
  resultsContainer.innerHTML = '<div class="loading-state"><div class="spinner-ring"></div><p>Searching meal alternatives...</p></div>';

  try {
    const data = await API.searchFood(query);
    renderETMModalResults(data.foods);
  } catch (e) {
    resultsContainer.innerHTML = `<p style="color:var(--danger); text-align: center; padding: 20px 0;">Search failed: ${e.message}</p>`;
  }
}

function renderETMModalResults(foods) {
  const container = document.getElementById('swap-results-grid');
  if (!foods || !foods.length) {
    container.innerHTML = '<div class="empty-state"><p>No culinary recipes found. Refine your keywords.</p></div>';
    return;
  }

  container.innerHTML = foods.map(f => {
    const safeTitle = f.name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    const safeImage = f.image_url ? f.image_url.replace(/'/g, "\\'") : '';
    
    return `
      <div class="swap-result-item hover-scale animate-in" style="gap: 14px; display:flex; justify-content:space-between; align-items:center; padding:12px; border:1px solid var(--border); border-radius:var(--radius-md);">
        ${f.image_url ? `<img src="${f.image_url}" style="width:46px; height:46px; border-radius:var(--radius-md); object-fit:cover; flex-shrink:0;">` : ''}
        <div class="result-left" style="flex:1; overflow:hidden;">
          <h5 class="result-name" style="margin:0; font-size:13.5px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escHtml(f.name)}</h5>
          <div class="result-meta" style="font-size:11.5px; color:var(--muted)">
            <span class="result-val" style="font-weight:600; color:var(--text)">${parseFloat(f.calories).toFixed(0)} kcal</span> · 
            <span>P: ${parseFloat(f.protein).toFixed(1)}g</span> · 
            <span>C: ${parseFloat(f.total_carbs).toFixed(1)}g</span> · 
            <span>F: ${parseFloat(f.total_fat).toFixed(1)}g</span>
          </div>
        </div>
        <button class="btn btn-lime btn-sm" onclick="selectSwapRecipeTarget(${f.id}, '${safeTitle}', ${f.calories}, ${f.protein}, ${f.total_carbs}, ${f.total_fat}, ${f.dietary_fiber}, '${escHtml(f.serving_size || '1 serving').replace(/'/g, "\\'")}', '${safeImage}')">
          Select
        </button>
      </div>
    `;
  }).join('');
}

async function selectSwapRecipeTarget(id, name, cal, prot, carb, fat, fiber, serving, image = '', defaultInstructions = '') {
  closeSwapModal();
  showToast('Swapping in progress...', 'info');
  
  let instructions = defaultInstructions;
  
  if (id && !instructions && image) {
    try {
      const detailed = await API.getRecipe(id);
      instructions = detailed.instructions || '';
    } catch (e) {
      instructions = 'Follow standard preparation procedures.';
    }
  }

  try {
    await API.swapMeal({
      action: 'replace',
      date: currentDate,
      slot: currentSlotForSwap,
      fdc_id: id,
      food_name: name,
      calories: cal,
      protein_g: prot,
      carbs_g: carb,
      fat_g: fat,
      fiber_g: fiber,
      serving_size: serving,
      image_url: image,
      instructions: instructions
    });
    
    showToast('Slot updated successfully ✓', 'success');
    loadPlanForDate();
  } catch (e) {
    showToast('Failed to swap: ' + e.message, 'error');
  }
}

// Binds search actions in Modal
document.getElementById('swap-search-input')?.addEventListener('input', (e) => {
  clearTimeout(modalSearchTimer);
  const q = e.target.value.trim();
  if (q.length < 2) return;
  modalSearchTimer = setTimeout(() => searchFoodsForModal(q), 500);
});

document.getElementById('btn-swap-search')?.addEventListener('click', () => {
  searchFoodsForModal(document.getElementById('swap-search-input').value);
});

document.querySelectorAll('#swap-modal .quick-chip').forEach(chip => {
  chip.addEventListener('click', () => {
    const q = chip.dataset.query;
    document.getElementById('swap-search-input').value = q;
    searchFoodsForModal(q);
  });
});

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

// Init execution
document.addEventListener('DOMContentLoaded', () => {
  renderCalendarTabs();
  loadPlanForDate();
});
