// Activity multipliers
const ACTIVITY = {
  sedentary  : 1.2,
  light      : 1.375,
  moderate   : 1.55,
  active     : 1.725,
  very_active: 1.9,
};

const ACTIVITY_DESC = {
  sedentary  : 'Sedentary: Little or no exercise, desk job.',
  light      : 'Light: Light exercise 1-3 days/week.',
  moderate   : 'Moderate: Moderate exercise 3-5 days/week.',
  active     : 'Active: Hard exercise 6-7 days/week.',
  very_active: 'Very Active: Heavy physical job or daily training.',
};

const GOAL_ADJUST = { lose: -500, maintain: 0, gain: 300 };
const GOAL_LABEL  = {
  lose    : 'Weight loss (−500 kcal/day)',
  maintain: 'Maintenance',
  gain    : 'Muscle gain (+300 kcal/day)',
};

// State
let selectedActivity = 'moderate';
let selectedGoal     = 'maintain';

function calcBMR(weight, height, age, gender) {
  if (gender === 'male') return (10 * weight) + (6.25 * height) - (5 * age) + 5;
  return (10 * weight) + (6.25 * height) - (5 * age) - 161;
}

function runCalculator() {
  const age    = parseFloat(document.getElementById('inp-age').value)    || 0;
  const weight = parseFloat(document.getElementById('inp-weight').value) || 0;
  const height = parseFloat(document.getElementById('inp-height').value) || 0;
  const gender = document.querySelector('[name="gender"]:checked')?.value || 'male';

  if (!age || !weight || !height) return;

  const bmr       = calcBMR(weight, height, age, gender);
  const tdee      = Math.round(bmr * ACTIVITY[selectedActivity]);
  let dailyCal  = tdee + GOAL_ADJUST[selectedGoal];
  
  if (dailyCal < 1200) dailyCal = 1200; // Safe threshold limit

  const proteinG  = Math.round(dailyCal * 0.30 / 4);
  const carbsG    = Math.round(dailyCal * 0.40 / 4);
  const fatG      = Math.round(dailyCal * 0.30 / 9);

  // Update UI
  document.getElementById('res-bmr').textContent     = Math.round(bmr).toLocaleString();
  document.getElementById('res-tdee').textContent    = tdee.toLocaleString();
  document.getElementById('res-goal-cal').textContent = dailyCal.toLocaleString();
  document.getElementById('res-goal-label').textContent = GOAL_LABEL[selectedGoal];
  document.getElementById('res-protein').textContent = proteinG + 'g';
  document.getElementById('res-carbs').textContent   = carbsG   + 'g';
  document.getElementById('res-fat').textContent     = fatG     + 'g';

  // Animate macro progress bars
  const total = proteinG * 4 + carbsG * 4 + fatG * 9;
  document.getElementById('bar-protein').style.width = ((proteinG * 4 / total) * 100).toFixed(1) + '%';
  document.getElementById('bar-carbs').style.width   = ((carbsG * 4 / total) * 100).toFixed(1) + '%';
  document.getElementById('bar-fat').style.width     = ((fatG * 9 / total) * 100).toFixed(1) + '%';

  // Show results panel
  document.getElementById('results-panel').classList.add('show');
}

// Bind interactive toggle buttons
document.querySelectorAll('.activity-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.activity-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selectedActivity = btn.dataset.value;
    document.getElementById('activity-desc').textContent = ACTIVITY_DESC[selectedActivity];
    runCalculator();
  });
});

document.querySelectorAll('.goal-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.goal-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selectedGoal = btn.dataset.value;
    runCalculator();
  });
});

// Live recalculate on inputs
document.querySelectorAll('#inp-age,#inp-weight,#inp-height').forEach(inp => {
  inp.addEventListener('input', runCalculator);
});
document.querySelectorAll('[name="gender"]').forEach(r => {
  r.addEventListener('change', runCalculator);
});

// Fetch saved settings on page load to prefill values
async function loadSavedProfile() {
  try {
    const profile = await API.getProfile();
    if (profile && profile.age) {
      document.getElementById('inp-age').value = profile.age;
      document.getElementById('inp-weight').value = profile.weight_kg;
      document.getElementById('inp-height').value = profile.height_cm;
      
      // Select gender
      document.querySelectorAll('[name="gender"]').forEach(r => {
        r.checked = (r.value === profile.gender);
      });
      
      // Select activity pill
      if (profile.activity_level) {
        document.querySelectorAll('.activity-btn').forEach(b => {
          b.classList.toggle('active', b.dataset.value === profile.activity_level);
        });
        selectedActivity = profile.activity_level;
        document.getElementById('activity-desc').textContent = ACTIVITY_DESC[selectedActivity];
      }

      // Select goal pill
      if (profile.goal) {
        document.querySelectorAll('.goal-btn').forEach(b => {
          b.classList.toggle('active', b.dataset.value === profile.goal);
        });
        selectedGoal = profile.goal;
      }

      if (profile.diet_type) {
        document.getElementById('inp-diet').value = profile.diet_type;
      }
      
      runCalculator();
    }
  } catch (e) {
    // Silently ignore if profile is not filled yet
    runCalculator();
  }
}

// Save targets to database and autogenerate meal plan
document.getElementById('btn-save-targets')?.addEventListener('click', async () => {
  const btn = document.getElementById('btn-save-targets');
  const originalText = btn.innerHTML;
  btn.disabled = true; 
  btn.innerHTML = '<span>Saving targets...</span> <div class="spinner-ring" style="width: 16px; height: 16px; display: inline-block; border-width: 2px;"></div>';
  
  try {
    const body = {
      age           : parseFloat(document.getElementById('inp-age').value),
      weight_kg     : parseFloat(document.getElementById('inp-weight').value),
      height_cm     : parseFloat(document.getElementById('inp-height').value),
      gender        : document.querySelector('[name="gender"]:checked').value,
      activity_level: selectedActivity,
      goal          : selectedGoal,
      diet_type     : document.getElementById('inp-diet').value,
    };
    
    // Save profile metrics
    await API.updateProfile(body);
    
    // Auto-generate meal plan for today
    showToast('Targets saved successfully! Generating meal plan...', 'success');
    btn.innerHTML = '<span>Generating meals...</span>';
    
    const today = new Date().toISOString().split('T')[0];
    await API.generatePlan(today);
    
    showToast('Plan created! Opening meal planner...', 'success');
    setTimeout(() => {
      window.location.href = '/nutriplan/pages/planner.php';
    }, 1200);
    
  } catch (e) {
    showToast('Saving failed: ' + e.message, 'error');
    btn.disabled = false; 
    btn.innerHTML = originalText;
  }
});

// Init on load
document.addEventListener('DOMContentLoaded', () => {
  loadSavedProfile();
});
