let weekStart = getMonday(new Date());

function getMonday(d) {
  d = new Date(d);
  const day = d.getDay();
  const diff = d.getDate() - day + (day === 0 ? -6 : 1);
  return new Date(d.setDate(diff)).toISOString().split('T')[0];
}

function formatWeekRange(startStr) {
  const start = new Date(startStr);
  const end = new Date(start);
  end.setDate(start.getDate() + 6);
  
  const options = { month: 'short', day: 'numeric' };
  return `${start.toLocaleDateString('en-US', options)} – ${end.toLocaleDateString('en-US', options)}`;
}

function updateWeekBar() {
  document.getElementById('week-display-range').textContent = formatWeekRange(weekStart);
}

async function loadGroceryList() {
  const container = document.getElementById('grocery-sections');
  if (!container) return;

  container.innerHTML = '<div class="loading-state" style="padding:40px;"><div class="spinner-ring"></div><p>Fetching your grocery items...</p></div>';

  try {
    const data = await API.getGrocery(weekStart);
    renderGroceryList(data.items);
  } catch (e) {
    container.innerHTML = `<div class="error-state"><p>Loading failed: ${e.message}</p></div>`;
  }
}

function renderGroceryList(groupedItems) {
  const container = document.getElementById('grocery-sections');
  
  let totalItems = 0;
  let checkedItems = 0;
  const categories = ['produce', 'protein', 'dairy', 'grains', 'pantry', 'other'];
  
  categories.forEach(cat => {
    if (groupedItems && groupedItems[cat]) {
      totalItems += groupedItems[cat].length;
      checkedItems += groupedItems[cat].filter(i => i.is_checked).length;
    }
  });

  // Update topbar subtitle
  document.getElementById('grocery-items-subtitle').textContent = `For this week's meal plan · ${totalItems} items`;

  // Update progress strip
  const percent = totalItems > 0 ? Math.round((checkedItems / totalItems) * 100) : 0;
  document.getElementById('grocery-summary-title').textContent = `${checkedItems} of ${totalItems} items checked`;
  
  const progressFill = document.getElementById('grocery-progress-fill');
  if (progressFill) progressFill.style.width = `${percent}%`;

  if (totalItems === 0) {
    container.innerHTML = `
      <div class="empty-state card" style="text-align: center; padding: 40px 20px;">
        <i class="ti ti-shopping-cart-x" style="font-size: 48px; color: var(--sage); margin-bottom: 12px; display: block;"></i>
        <h3>Your Shopping List is Empty</h3>
        <p style="color: var(--muted); margin: 8px auto 20px auto; max-width: 320px; font-size:13px;">Build a grocery checklist automatically based on your planned meals for this week.</p>
        <button class="btn btn-lime" onclick="generateGroceryFromPlan()">
          <i class="ti ti-refresh"></i> Build Grocery Checklist
        </button>
      </div>
    `;
    return;
  }

  const catLabels = {
    produce: 'Produce',
    protein: 'Protein',
    dairy: 'Dairy',
    grains: 'Pantry & Grains',
    pantry: 'Pantry Essentials',
    other: 'Other Ingredients'
  };

  const catEmojis = {
    produce: '🥬',
    protein: '🥩',
    dairy: '🥛',
    grains: '🌾',
    pantry: '🥫',
    other: '📦'
  };

  let listHTML = '';

  categories.forEach(cat => {
    const items = groupedItems[cat] || [];
    if (items.length === 0) return;

    listHTML += `
      <div class="grocery-section animate-in">
        <div class="grocery-head">
          <span class="grocery-head-icon">${catEmojis[cat]}</span>
          ${catLabels[cat]}
        </div>
        
        <div class="checklist-items">
          ${items.map(item => `
            <div class="grocery-item ${item.is_checked ? 'checked' : ''}" 
                 id="item-row-${item.id}" onclick="toggleChecklistItemChecked(${item.id})">
              <div class="grocery-check" id="check-box-${item.id}" 
                   style="${item.is_checked ? 'background:var(--sage); color:#fff; border-color:var(--sage);' : ''}">✓</div>
              <div class="grocery-name">${escHtml(item.food_name)}</div>
              <div class="grocery-amt">${escHtml(item.quantity)}</div>
            </div>
          `).join('')}
        </div>
      </div>
    `;
  });

  container.innerHTML = listHTML;
}

async function toggleChecklistItemChecked(id) {
  const row = document.getElementById(`item-row-${id}`);
  const checkBox = document.getElementById(`check-box-${id}`);
  
  const wasChecked = row.classList.contains('checked');
  const isCheckedNow = wasChecked ? 0 : 1;
  
  if (isCheckedNow) {
    row.classList.add('checked');
    checkBox.style.background = 'var(--sage)';
    checkBox.style.color = '#fff';
    checkBox.style.borderColor = 'var(--sage)';
  } else {
    row.classList.remove('checked');
    checkBox.style.background = 'var(--cream)';
    checkBox.style.color = 'transparent';
    checkBox.style.borderColor = 'var(--border)';
  }
  
  try {
    await API.toggleItem(id, isCheckedNow);
    // Reload dynamically to update summary percentages
    const data = await API.getGrocery(weekStart);
    renderGroceryList(data.items);
  } catch (e) {
    showToast('Update failed: ' + e.message, 'error');
    // Revert visual change
    if (wasChecked) {
      row.classList.add('checked');
      checkBox.style.background = 'var(--sage)';
      checkBox.style.color = '#fff';
      checkBox.style.borderColor = 'var(--sage)';
    } else {
      row.classList.remove('checked');
      checkBox.style.background = 'var(--cream)';
      checkBox.style.color = 'transparent';
      checkBox.style.borderColor = 'var(--border)';
    }
  }
}

async function generateGroceryFromPlan() {
  const btn = document.getElementById('btn-generate-grocery');
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner-ring" style="width: 16px; height: 16px; border-width: 2px;"></div>';
  
  showToast('Generating checklist from plan...', 'info');

  try {
    const res = await API.generateGrocery(weekStart);
    if (res.items_added === 0) {
      showToast('No meal plans found for this week to export.', 'warning');
    } else {
      showToast(`Shopping list created with ${res.items_added} ingredients!`, 'success');
    }
    loadGroceryList();
  } catch (e) {
    showToast('Failed to compile: ' + e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalText;
  }
}

// Clear checks instantly matching mockup script trigger
window.clearAllChecklist = async function() {
  if (!confirm('Clear all checked items for this week?')) return;
  
  try {
    const data = await API.getGrocery(weekStart);
    const categories = ['produce', 'protein', 'dairy', 'grains', 'pantry', 'other'];
    
    // Toggle all checked items to unchecked
    for (const cat of categories) {
      const checkedItems = (data.items && data.items[cat]) ? data.items[cat].filter(i => i.is_checked) : [];
      for (const item of checkedItems) {
        await API.toggleItem(item.id, 0);
      }
    }
    
    showToast('All checks cleared successfully', 'success');
    loadGroceryList();
  } catch (e) {
    showToast('Clear action failed: ' + e.message, 'error');
  }
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

// Bind Navigation and triggers
document.addEventListener('DOMContentLoaded', () => {
  updateWeekBar();
  loadGroceryList();

  document.getElementById('btn-generate-grocery')?.addEventListener('click', generateGroceryFromPlan);

  document.getElementById('btn-prev-week')?.addEventListener('click', () => {
    const d = new Date(weekStart);
    d.setDate(d.getDate() - 7);
    weekStart = d.toISOString().split('T')[0];
    updateWeekBar();
    loadGroceryList();
  });

  document.getElementById('btn-next-week')?.addEventListener('click', () => {
    const d = new Date(weekStart);
    d.setDate(d.getDate() + 7);
    weekStart = d.toISOString().split('T')[0];
    updateWeekBar();
    loadGroceryList();
  });
});
