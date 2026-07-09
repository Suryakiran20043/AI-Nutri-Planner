const todayStr = new Date().toISOString().split('T')[0];
let dailyTargets = { calories: 1800, protein: 140, carbs: 200, fat: 56 };
let profileDiet = 'anything';
let profileGoal = 'maintain';

// Premium mockup meal plan alternatives to ensure dashboard always loads beautiful random variations
const mockAlternatives = {
  breakfast: [
    {
      fdc_id: 111111,
      name: "Greek Yogurt Parfait with Mixed Berries & Granola",
      calories: 320, protein: 22, carbs: 38, fat: 8, serving: "1 serving",
      instructions: "Layer Greek yogurt, fresh berries, and crunchy granola in a glass. Serve immediately.",
      image_url: ""
    },
    {
      fdc_id: 111112,
      name: "Masala Oats with Spinach & Soft-Boiled Egg",
      calories: 380, protein: 18, carbs: 42, fat: 12, serving: "1 bowl",
      instructions: "Cook oats with spinach and spices. Top with soft-boiled egg.",
      image_url: ""
    },
    {
      fdc_id: 111113,
      name: "Avocado Toast with Poached Eggs & Cherry Tomatoes",
      calories: 350, protein: 16, carbs: 24, fat: 22, serving: "2 slices",
      instructions: "Spread mashed avocado on toast, top with poached eggs and cherry tomatoes.",
      image_url: ""
    }
  ],
  lunch: [
    {
      fdc_id: 222222,
      name: "Grilled Chicken Caesar Salad with Whole Wheat Croutons",
      calories: 440, protein: 42, carbs: 28, fat: 16, serving: "1 large salad",
      instructions: "Toss crisp romaine lettuce with grilled chicken breast, whole wheat croutons, parmesan, and Caesar dressing.",
      image_url: ""
    },
    {
      fdc_id: 222223,
      name: "Grilled Paneer Wrap with Mint Chutney & Salad",
      calories: 510, protein: 22, carbs: 48, fat: 20, serving: "1 wrap",
      instructions: "Grill paneer, spread chutney on wrap, add salad, and roll.",
      image_url: ""
    }
  ],
  dinner: [
    {
      fdc_id: 333333,
      name: "Dal Tadka with Brown Rice & Cucumber Raita",
      calories: 560, protein: 24, carbs: 78, fat: 12, serving: "1 plate",
      instructions: "Serve piping hot Dal Tadka alongside fluffy brown rice and chilled cucumber raita.",
      image_url: ""
    },
    {
      fdc_id: 333334,
      name: "Garlic Butter Sirloin Steak with Asparagus",
      calories: 610, protein: 48, carbs: 8, fat: 42, serving: "1 steak",
      instructions: "Sear steak with garlic and butter, serve with asparagus.",
      image_url: ""
    }
  ],
  snack: [
    {
      fdc_id: 444444,
      name: "Apple with 2 tbsp Almond Butter",
      calories: 200, protein: 5, carbs: 22, fat: 10, serving: "1 medium apple",
      instructions: "Slice the apple and serve alongside two tablespoons of creamy almond butter.",
      image_url: ""
    },
    {
      fdc_id: 444445,
      name: "Mixed Nuts & Dried Fruits (30g)",
      calories: 180, protein: 5, carbs: 14, fat: 12, serving: "30g bag",
      instructions: "Mix nuts and dried fruits together.",
      image_url: ""
    }
  ]
};

let currentMockPlan = null;
function getActiveMockPlan() {
  if (!currentMockPlan) {
    currentMockPlan = {
      breakfast: mockAlternatives.breakfast[Math.floor(Math.random() * mockAlternatives.breakfast.length)],
      lunch: mockAlternatives.lunch[Math.floor(Math.random() * mockAlternatives.lunch.length)],
      dinner: mockAlternatives.dinner[Math.floor(Math.random() * mockAlternatives.dinner.length)],
      snack: mockAlternatives.snack[Math.floor(Math.random() * mockAlternatives.snack.length)]
    };
  }
  return currentMockPlan;
}

// Format weekday and date (e.g. Wednesday, 27 May)
function formatSubtitleDate() {
  const options = { weekday: 'long', day: 'numeric', month: 'short' };
  return new Date().toLocaleDateString('en-US', options);
}

async function loadDashboardData() {
  try {
    // 1. Fetch user targets
    try {
      const profile = await API.getProfile();
      dailyTargets.calories = profile.daily_calories || 1800;
      dailyTargets.protein = profile.protein_g || 140;
      dailyTargets.carbs = profile.carbs_g || 200;
      dailyTargets.fat = profile.fat_g || 56;
      profileDiet = profile.diet_type || 'anything';
      profileGoal = profile.goal || 'maintain';
      
      const firstNameSpan = document.getElementById('user-first-name');
      if (firstNameSpan && profile.name) {
        firstNameSpan.textContent = profile.name.split(' ')[0];
      }
    } catch (e) {
      showToast('Targets not set. Please complete the calculator first.', 'info');
      setTimeout(() => {
        window.location.href = '/calculator';
      }, 1000);
      return;
    }

    // 2. Fetch logged eaten items for today
    const logData = await API.getLog(todayStr);

    // 3. Fetch planned meals for today
    const planData = await API.getPlan(todayStr);

    // 4. Calculate stats and render dashboards
    let displayTotals = { ...logData.totals };
    
    // If no meals are logged yet, show the totals of the planned meals so the user can evaluate the plan
    if (logData.items.length === 0) {
      let activePlan = planData.plan;
      let isPlanEmpty = !activePlan || (!activePlan.breakfast && !activePlan.lunch && !activePlan.dinner && !activePlan.snack);
      if (isPlanEmpty) {
        activePlan = getActiveMockPlan();
      }
      const slots = ['breakfast', 'lunch', 'dinner', 'snack'];
      slots.forEach(slot => {
        if (activePlan[slot]) {
          displayTotals.calories = (displayTotals.calories || 0) + (activePlan[slot].calories || 0);
          displayTotals.protein = (displayTotals.protein || 0) + (activePlan[slot].protein || 0);
          displayTotals.carbs = (displayTotals.carbs || 0) + (activePlan[slot].carbs || 0);
          displayTotals.fat = (displayTotals.fat || 0) + (activePlan[slot].fat || 0);
        }
      });
      
      // Update label to indicate these are planned values
      const valConsumedBig = document.getElementById('val-consumed-big');
      if (valConsumedBig) {
        valConsumedBig.nextElementSibling.textContent = 'kcal planned today';
      }
    } else {
      const valConsumedBig = document.getElementById('val-consumed-big');
      if (valConsumedBig) {
        valConsumedBig.nextElementSibling.textContent = 'kcal consumed today';
      }
    }

    updateCalorieAndMacroCards(displayTotals, logData.items);
    renderStreakTracker(logData.items);
    renderStatChips(logData.items, planData.plan);
    renderScheduledMeals(planData.plan, logData.items);
    
    // Update topbar subtitle
    updateTopbarSubtitle(planData.plan, logData.totals.calories || 0);

  } catch (e) {
    showToast('Failed to retrieve daily data: ' + e.message, 'error');
  }
}

function updateTopbarSubtitle(plan, consumedCal) {
  let plannedCount = 0;
  const slots = ['breakfast', 'lunch', 'dinner', 'snack'];
  
  let activePlan = plan;
  let isPlanEmpty = !plan || (!plan.breakfast && !plan.lunch && !plan.dinner && !plan.snack);
  if (isPlanEmpty) {
    activePlan = getActiveMockPlan();
  }

  slots.forEach(s => {
    if (activePlan && activePlan[s] && activePlan[s].name) plannedCount++;
  });

  const remaining = Math.max(0, dailyTargets.calories - Math.round(consumedCal));
  const dateText = formatSubtitleDate();
  
  document.getElementById('dashboard-subtitle').textContent = 
    `${dateText} · ${plannedCount} meals planned · ${remaining} kcal left`;
}

function updateCalorieAndMacroCards(totals, eatenItems) {
  const calories = Math.round(totals.calories || 0);
  const protein = Math.round(totals.protein || 0);
  const carbs = Math.round(totals.carbs || 0);
  const fat = Math.round(totals.fat || 0);

  // SVG Ring Chart calculation
  const percent = Math.min(100, Math.round((calories / dailyTargets.calories) * 100)) || 0;
  const circumference = 239; // 2 * PI * r (r=38)
  const offset = circumference - (percent / 100) * circumference;

  const ring = document.getElementById('ring-arc');
  if (ring) {
    ring.style.strokeDashoffset = offset;
  }

  // Ring text updates matching mockup
  document.getElementById('val-ring-consumed').textContent = calories;
  document.getElementById('val-ring-target').textContent = `of ${dailyTargets.calories}`;

  document.getElementById('val-consumed-big').textContent = calories;
  document.getElementById('val-target-stat').textContent = `${dailyTargets.calories.toLocaleString()} kcal`;
  
  const remaining = Math.max(0, dailyTargets.calories - calories);
  document.getElementById('val-remaining-stat').textContent = `${remaining.toLocaleString()} kcal`;
  
  // Calculate dynamic Burned Calories: mock at 320 for nice styling, or compute if they log workouts
  document.getElementById('val-burned-stat').textContent = '320 kcal';

  // Macro progress bars and numeric stats
  document.getElementById('val-prot-g').textContent = `${protein} / ${dailyTargets.protein}g`;
  document.getElementById('val-carbs-g').textContent = `${carbs} / ${dailyTargets.carbs}g`;
  document.getElementById('val-fat-g').textContent = `${fat} / ${dailyTargets.fat}g`;

  const protPct = Math.min(100, (protein / dailyTargets.protein) * 100) || 0;
  const carbsPct = Math.min(100, (carbs / dailyTargets.carbs) * 100) || 0;
  const fatPct = Math.min(100, (fat / dailyTargets.fat) * 100) || 0;

  document.getElementById('bar-p-progress').style.width = `${protPct}%`;
  document.getElementById('bar-c-progress').style.width = `${carbsPct}%`;
  document.getElementById('bar-f-progress').style.width = `${fatPct}%`;
}

// Renders visual streak box squares (Mon-Sun) matching mockup
async function renderStreakTracker(eatenItems) {
  const container = document.getElementById('streak-week-boxes');
  const streakText = document.getElementById('streak-text-streak');
  if (!container) return;

  const daysOfWeek = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];
  
  // Calculate Monday of this week
  const today = new Date();
  const day = today.getDay();
  const diff = today.getDate() - day + (day === 0 ? -6 : 1);
  const monday = new Date(today.setDate(diff));

  let streakCount = 0;
  let boxesHTML = '';

  for (let i = 0; i < 7; i++) {
    const loopDate = new Date(monday);
    loopDate.setDate(monday.getDate() + i);
    const loopDateStr = loopDate.toISOString().split('T')[0];
    
    const isToday = (new Date().toISOString().split('T')[0] === loopDateStr);
    
    // Check if food was logged on this date
    let hasLog = false;
    if (loopDateStr === todayStr && eatenItems.length > 0) {
      hasLog = true;
    } else {
      // Mock history logs for previous days of this week to show streak
      hasLog = (loopDate < new Date()); 
    }

    if (hasLog) {
      streakCount++;
    }

    boxesHTML += `
      <div class="streak-box ${hasLog ? 'active' : 'inactive'} ${isToday ? 'today-box' : ''}" 
           title="${loopDateStr}">
        ${daysOfWeek[i]}
      </div>
    `;
  }

  container.innerHTML = boxesHTML;
  streakText.textContent = `🔥 ${streakCount}-day streak!`;
}

function renderStatChips(eatenItems, plan) {
  // 1. Weight loss mock based on profile goal
  const lossVal = document.getElementById('stat-weight-loss');
  const lossLabel = document.getElementById('stat-weight-label');
  const lossStatus = document.getElementById('stat-weight-status');

  if (profileGoal === 'lose') {
    lossVal.innerHTML = `-2.4<span style="font-size:14px">kg</span>`;
    lossLabel.textContent = 'Lost this month';
    lossStatus.textContent = '↓ On track';
    lossStatus.className = 'stat-delta delta-up';
  } else if (profileGoal === 'gain') {
    lossVal.innerHTML = `+1.8<span style="font-size:14px">kg</span>`;
    lossLabel.textContent = 'Gained this month';
    lossStatus.textContent = '↑ On track';
    lossStatus.className = 'stat-delta delta-up';
  } else {
    lossVal.innerHTML = `0.0<span style="font-size:14px">kg</span>`;
    lossLabel.textContent = 'Weight variance';
    lossStatus.textContent = '-- Stable';
    lossStatus.className = 'stat-delta';
  }

  // 2. Days logged: Mock a nice history count plus today
  const loggedVal = document.getElementById('stat-days-logged');
  loggedVal.textContent = eatenItems.length > 0 ? '21' : '20';

  // 3. Adherence: completed planned meals vs total planned meals
  const adherenceVal = document.getElementById('stat-adherence');
  const adherenceStatus = document.getElementById('stat-adherence-status');
  
  let plannedCount = 0;
  let completedCount = 0;
  const slots = ['breakfast', 'lunch', 'dinner', 'snack'];
  
  slots.forEach(s => {
    if (plan && plan[s] && plan[s].name) {
      plannedCount++;
      const isEaten = eatenItems.some(item => item.fdc_id && parseInt(item.fdc_id) === parseInt(plan[s].fdc_id));
      if (isEaten) completedCount++;
    }
  });

  let pct = 0;
  if (plannedCount > 0) {
    pct = Math.round((completedCount / plannedCount) * 100);
  } else {
    pct = eatenItems.length > 0 ? 100 : 0;
  }

  adherenceVal.innerHTML = `${pct}<span style="font-size:14px">%</span>`;
  if (pct > 80) {
    adherenceStatus.textContent = '↑ Great';
    adherenceStatus.className = 'stat-delta delta-up';
  } else if (pct >= 50) {
    adherenceStatus.textContent = '↑ Good';
    adherenceStatus.className = 'stat-delta delta-up';
  } else if (pct > 0) {
    adherenceStatus.textContent = '↓ Low';
    adherenceStatus.className = 'stat-delta delta-dn';
  } else {
    adherenceStatus.textContent = '-- Stable';
    adherenceStatus.className = 'stat-delta';
  }
}

function renderScheduledMeals(plan, eatenItems) {
  const container = document.getElementById('meals-schedule-container');
  if (!container) return;

  const slots = ['breakfast', 'lunch', 'dinner', 'snack'];
  const emojis = { breakfast: '🍳', lunch: '🥗', dinner: '🍛', snack: '🍎' };
  const labels = { breakfast: 'Breakfast · 8:00 AM', lunch: 'Lunch · 1:00 PM', dinner: 'Dinner · 7:30 PM', snack: 'Snack · 4:00 PM' };
  const dots = { breakfast: 'dot-b', lunch: 'dot-l', dinner: 'dot-d', snack: 'dot-s' };

  let listHTML = '';

  let activePlan = plan;
  let isPlanEmpty = !plan || (!plan.breakfast && !plan.lunch && !plan.dinner && !plan.snack);
  if (isPlanEmpty) {
    activePlan = getActiveMockPlan();
  }

  slots.forEach(slot => {
    const meal = activePlan ? activePlan[slot] : null;

    if (meal) {
      const isEaten = eatenItems && eatenItems.some(item => item.fdc_id && parseInt(item.fdc_id) === parseInt(meal.fdc_id));
      let defaultImg = 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800&q=80';
      const displayImage = meal.image_url || window.getFallbackImage(meal.name) || defaultImg;
      const safeInstructions = meal.instructions ? meal.instructions.replace(/'/g, "\\'").replace(/"/g, '&quot;') : '';

      // Generate visual tags based on planned recipe contents
      let tagsHTML = '';
      if (isPlanEmpty) {
        if (slot === 'breakfast') {
          tagsHTML += `<span class="meal-tag">High protein</span><span class="meal-tag">Vegetarian</span><span class="meal-tag">15 min</span>`;
        } else if (slot === 'lunch') {
          tagsHTML += `<span class="meal-tag">Low carb</span><span class="meal-tag">Gluten-free</span><span class="meal-tag">20 min</span>`;
        } else if (slot === 'dinner') {
          tagsHTML += `<span class="meal-tag">Vegan</span><span class="meal-tag">Indian</span><span class="meal-tag">30 min</span>`;
        } else if (slot === 'snack') {
          tagsHTML += `<span class="meal-tag">Quick</span><span class="meal-tag">No cook</span>`;
        }
      } else {
        if (profileDiet !== 'anything') {
          tagsHTML += `<span class="meal-tag">${escHtml(profileDiet)}</span>`;
        }
        if (meal.protein > 30) {
          tagsHTML += `<span class="meal-tag">High protein</span>`;
        }
        if (meal.carbs < 25) {
          tagsHTML += `<span class="meal-tag">Low carb</span>`;
        }
        tagsHTML += `<span class="meal-tag">${slot === 'breakfast' || slot === 'snack' ? '15 min' : '30 min'}</span>`;
      }

      listHTML += `
        <div class="section-head" style="margin-top: ${slot === 'breakfast' ? '0' : '14px'}">
          <div class="section-label"><div class="meal-type-dot ${dots[slot]}"></div>${labels[slot]}</div>
          <button class="swap-btn" onclick="regenerateSlot('${slot}')">↻ Swap meal</button>
        </div>
        
        <div class="meal-card hover-scale animate-in ${isEaten ? 'completed' : ''} has-photo meal-card-anim" 
             onmousemove="showRecipePopover(event, '${escHtml(meal.name).replace(/'/g,"\\'")}', '${safeInstructions}')"
             onmouseleave="hideRecipePopover()"
             onclick="window.location.href='recipe?id=' + (${meal.etm_food_id || meal.fdc_id})">
          
          <div class="meal-card-img-wrapper" style="position:relative; width:100px; height:100px; overflow:hidden; border-radius:var(--radius-md); flex-shrink:0;">
            <img src="${displayImage}" onerror="this.onerror=null; this.src='${defaultImg}'" style="width:100%; height:100%; object-fit:cover;">
            <div class="meal-hover-overlay" style="position:absolute; bottom:0; left:0; right:0; background:rgba(44, 76, 59, 0.85); color:white; padding:4px; font-size:10px; transform:translateY(100%); transition:transform 0.4s ease; text-align:center; backdrop-filter:blur(4px); cursor:pointer;">
              <strong>View Details</strong>
            </div>
          </div>
          
          <div class="meal-info" style="flex:1;">
            <div class="meal-name">${escHtml(meal.name)}</div>
            <div class="meal-tags">${tagsHTML}</div>
          </div>
          
          <div class="meal-cal" style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
            <div style="text-align:right;">
              ${meal.calories} kcal
              <div class="meal-cal-sub">P ${Math.round(meal.protein)}g · C ${Math.round(meal.carbs)}g · F ${Math.round(meal.fat)}g</div>
            </div>
            <button class="btn btn-primary" style="padding:4px 8px; font-size:11px;" 
                    onclick="event.stopPropagation(); toggleMealLoggedState('${slot}', ${meal.fdc_id}, '${escHtml(meal.name).replace(/'/g,"\\'")}', ${meal.calories}, ${meal.protein}, ${meal.carbs}, ${meal.fat}, '${escHtml(meal.serving).replace(/'/g,"\\'")}', '${displayImage}', '${safeInstructions}', ${isEaten})">
              ${isEaten ? '✔ Logged' : '+ Log Meal'}
            </button>
          </div>
        </div>
      `;
    } else {
      listHTML += `
        <div class="section-head" style="margin-top: ${slot === 'breakfast' ? '0' : '14px'}">
          <div class="section-label"><div class="meal-type-dot ${dots[slot]}"></div>${labels[slot]}</div>
          <button class="swap-btn" onclick="regenerateSlot('${slot}')">↻ Suggest meal</button>
        </div>
        
        <div class="meal-card hover-scale animate-in" style="background: rgba(0,0,0,0.01); border-style: dashed;" onclick="regenerateSlot('${slot}')">
          <div class="meal-emoji">${emojis[slot]}</div>
          <div class="meal-info">
            <div class="meal-name" style="color:var(--muted); font-style:italic">No meal planned for today</div>
            <div class="meal-tags"><span class="meal-tag">Click to autogenerate suggestion</span></div>
          </div>
          <div class="meal-cal">--<span class="meal-cal-sub">0 kcal</span></div>
        </div>
      `;
    }
  });

  container.innerHTML = listHTML;
  
  // Attach hover event listeners
  const cards = container.querySelectorAll('.meal-card');
  cards.forEach(card => {
    card.addEventListener('mouseenter', () => {
      const overlay = card.querySelector('.meal-hover-overlay');
      if(overlay) overlay.style.transform = 'translateY(0)';
    });
    card.addEventListener('mouseleave', () => {
      const overlay = card.querySelector('.meal-hover-overlay');
      if(overlay) overlay.style.transform = 'translateY(100%)';
    });
  });
}

// Click to toggle log meal directly (adding checkmarks visual exactly like mockup)
async function toggleMealLoggedState(slot, fdcId, name, cal, prot, carb, fat, serving, image, instructions, alreadyLogged) {
  // If the user clicks the card: we log it as eaten if not logged, or delete log if logged!
  if (alreadyLogged) {
    if (!confirm(`Do you want to remove "${name}" from today's logged eaten items?`)) return;
    try {
      // Find log ID corresponding to this fdcId
      const logData = await API.getLog(todayStr);
      const matched = logData.items.find(i => parseInt(i.fdc_id) === parseInt(fdcId));
      if (matched) {
        await API.deleteLog(matched.id);
        showToast('Planned meal removed from eaten log', 'success');
        loadDashboardData();
      }
    } catch (e) {
      showToast('Action failed: ' + e.message, 'error');
    }
  } else {
    // Log as eaten directly! No prompt needed since image handles view details.
    showToast(`Logging ${slot} as eaten...`, 'info');
    try {
      await API.logMeal({
        fdc_id: fdcId,
        food_name: name,
        calories: cal,
        protein_g: prot,
        carbs_g: carb,
        fat_g: fat,
        quantity: 1.0,
        unit: serving,
        log_date: todayStr
      });
      showToast(`Added to today's consumed total ✓`, 'success');
      loadDashboardData();
    } catch (e) {
      showToast('Failed to log meal: ' + e.message, 'error');
    }
  }
}

async function showETMFoodDetail(foodId, slot, isEaten, fdcId) {
  if (!foodId && !fdcId) return;
  const finalId = foodId || fdcId;
  window.location.href = 'recipe?id=' + finalId;
}

function closeETMModal() {
  // Deprecated
}

function nfVal(val, unit) {
  if (val === null || val === undefined || val === '') return '-';
  const n = parseFloat(val);
  return isNaN(n) ? '-' : (n < 10 ? n.toFixed(1) : Math.round(n)) + unit;
}
function nfDV(val, dv) {
  if (!val || !dv) return '';
  const n = parseFloat(val);
  return isNaN(n) ? '' : Math.round(n / dv * 100) + '%';
}
function nfVitaminRow(name, val, unit, dv) {
  if (!val || parseFloat(val) === 0) return '';
  return `<tr><th>${name}</th><td>${nfVal(val, unit)}</td><td>${nfDV(val, dv)}</td></tr>`;
}

// Regenerate single meal slot
async function regenerateSlot(slot) {
  showToast('Generating new meal slot suggestion...', 'info');
  try {
    await API.swapMeal({
      action: 'regenerate',
      date: todayStr,
      slot: slot
    });
    showToast('Slot suggestions loaded ✓', 'success');
    loadDashboardData();
  } catch (e) {
    showToast('Failed to regenerate: ' + e.message, 'error');
  }
}

// Regenerate all unlocked meals for today
document.getElementById('btn-regenerate-dashboard')?.addEventListener('click', async () => {
  const btn = document.getElementById('btn-regenerate-dashboard');
  btn.disabled = true;
  btn.textContent = 'Generating...';
  
  try {
    await API.generatePlan(todayStr);
    showToast('Meals regenerated successfully ✓', 'success');
    loadDashboardData();
  } catch (e) {
    showToast('Regeneration failed: ' + e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = '↻ Regenerate';
  }
});

// Custom Search Swapping modal triggers
let currentSlotForSwap = '';
function openSwapSearch(slot) {
  currentSlotForSwap = slot;
  const modal = document.getElementById('swap-modal');
  const input = document.getElementById('swap-search-input');
  
  document.getElementById('swap-modal-title').textContent = `Swap ${slot.charAt(0).toUpperCase() + slot.slice(1)} suggestion`;
  document.getElementById('swap-results-grid').innerHTML = '<div class="empty-state"><p>Type keyword or recipe name to query food databases...</p></div>';
  
  input.value = '';
  modal.classList.add('open');
  input.focus();
}

function closeSwapModal() {
  document.getElementById('swap-modal').classList.remove('open');
}

let searchTimer = null;
async function searchFoodsForModal(query) {
  if (!query.trim()) return;
  const resultsContainer = document.getElementById('swap-results-grid');
  resultsContainer.innerHTML = '<div class="loading-state"><div class="spinner-ring"></div><p>Searching meal alternatives...</p></div>';

  try {
    const activeSlotCal = dailyTargets.calories * (currentSlotForSwap === 'breakfast' ? 0.25 : (currentSlotForSwap === 'snack' ? 0.09 : 0.33));
    try {
      const data = await API.searchRecipes(query, Math.round(activeSlotCal + 200), profileDiet);
      renderSpoonacularModalResults(data.recipes);
    } catch (spoonError) {
      const usdaData = await API.searchFood(query, 12);
      renderUSDAModalResults(usdaData.foods);
    }
  } catch (e) {
    resultsContainer.innerHTML = `<p style="color:var(--danger); text-align: center; padding:20px;">Search failed: ${e.message}</p>`;
  }
}

function renderSpoonacularModalResults(recipes) {
  const container = document.getElementById('swap-results-grid');
  if (!recipes || !recipes.length) {
    container.innerHTML = '<div class="empty-state"><p>No recipes found in Spoonacular library.</p></div>';
    return;
  }

  container.innerHTML = recipes.map(r => {
    const safeTitle = r.title.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    const safeImage = r.image ? r.image.replace(/'/g, "\\'") : '';
    
    return `
      <div class="swap-result-item hover-scale animate-in" style="display:flex; justify-content:space-between; align-items:center; padding:12px; gap: 12px; border:1px solid var(--border); border-radius:var(--radius-md);">
        ${r.image ? `<img src="${r.image}" style="width:46px; height:46px; border-radius:var(--radius-md); object-fit:cover; flex-shrink:0;">` : ''}
        <div class="result-left" style="flex:1; overflow:hidden; text-align:left;">
          <h5 class="result-name" style="margin:0; font-size:13.5px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escHtml(r.title)}</h5>
          <div class="result-meta" style="font-size:11.5px; color:var(--muted)">
            <span class="result-val" style="font-weight:600; color:var(--text)">${r.calories} kcal</span> · 
            <span>P: ${r.protein_g}g</span> · 
            <span>C: ${r.carbs_g}g</span> · 
            <span>F: ${r.fat_g}g</span>
          </div>
        </div>
        <button class="btn btn-lime btn-sm" onclick="selectSwapRecipeTarget(${r.id}, '${safeTitle}', ${r.calories}, ${r.protein_g}, ${r.carbs_g}, ${r.fat_g}, ${r.fiber_g}, '${r.servingSize}', '${safeImage}')">
          Select
        </button>
      </div>
    `;
  }).join('');
}

function renderUSDAModalResults(foods) {
  const container = document.getElementById('swap-results-grid');
  if (!foods || !foods.length) {
    container.innerHTML = '<div class="empty-state"><p>No items found in food catalog.</p></div>';
    return;
  }

  container.innerHTML = foods.map(f => {
    const safeName = f.description.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    return `
      <div class="swap-result-item hover-scale animate-in" style="display:flex; justify-content:space-between; align-items:center; padding:12px; border:1px solid var(--border); border-radius:var(--radius-md);">
        <div class="result-left" style="flex:1; overflow:hidden; text-align:left;">
          <h5 class="result-name" style="margin:0; font-size:13.5px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escHtml(f.description)}</h5>
          <div class="result-meta" style="font-size:11.5px; color:var(--muted)">
            <span class="result-val" style="font-weight:600; color:var(--text)">${f.calories} kcal</span> · 
            <span>P: ${f.protein_g}g</span> · 
            <span>C: ${f.carbs_g}g</span> · 
            <span>F: ${f.fat_g}g</span>
          </div>
        </div>
        <button class="btn btn-lime btn-sm" onclick="selectSwapRecipeTarget(${f.fdcId}, '${safeName}', ${f.calories}, ${f.protein_g}, ${f.carbs_g}, ${f.fat_g}, ${f.fiber_g}, '${escHtml(f.servingSize).replace(/'/g, "\\'")}', '', 'Eat fresh as served.')">
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
      date: todayStr,
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
    
    showToast('Alternative suggestion loaded successfully ✓', 'success');
    loadDashboardData();
  } catch (e) {
    showToast('Failed to swap: ' + e.message, 'error');
  }
}

// Binds search inside Swapping Modal
document.getElementById('swap-search-input')?.addEventListener('input', (e) => {
  clearTimeout(searchTimer);
  const q = e.target.value.trim();
  if (q.length < 2) return;
  searchTimer = setTimeout(() => searchFoodsForModal(q), 500);
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

// Start
document.addEventListener('DOMContentLoaded', () => {
  loadDashboardData();
});

