const API = {
  BASE: '/nutriplan/api',

  async call(endpoint, method = 'GET', body = null) {
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
    };
    if (body) opts.body = JSON.stringify(body);
    const res  = await fetch(this.BASE + endpoint, opts);
    
    if (res.status === 401) {
      // Redirect to login gate if unauthorized
      if (!window.location.pathname.endsWith('index.php') && window.location.pathname !== '/nutriplan/') {
        window.location.href = '/nutriplan/index.php';
        return;
      }
    }
    
    const data = await res.json();
    if (data.status === 'error') throw new Error(data.message);
    return data.data;
  },

  // Auth
  register  : (body) => API.call('/auth/register.php',  'POST', body),
  login     : (body) => API.call('/auth/login.php',     'POST', body),
  logout    : ()     => API.call('/auth/logout.php',    'POST'),

  // Profile
  getProfile    : ()     => API.call('/user/get-profile.php'),
  updateProfile : (body) => API.call('/user/update-profile.php', 'POST', body),

  // USDA
  searchFood : (query, pageSize = 15) =>
    API.call(`/usda/search.php?query=${encodeURIComponent(query)}&pageSize=${pageSize}`),
  getFood    : (fdcId) => API.call(`/usda/get-food.php?fdcId=${fdcId}`),

  // Spoonacular Proxy
  searchRecipes : (query, maxCalories = 0, diet = 'anything') =>
    API.call(`/spoonacular/search-recipes.php?query=${encodeURIComponent(query)}&maxCalories=${maxCalories}&diet=${encodeURIComponent(diet)}`),
  getRecipe     : (id) => API.call(`/spoonacular/get-recipe.php?id=${id}`),

  // Planner
  generatePlan : (date) => API.call('/planner/generate.php',  'POST', { date }),
  getPlan      : (date) => API.call(`/planner/get-plan.php?date=${date}`),
  swapMeal     : (body) => API.call('/planner/swap-meal.php', 'POST', body),
  savePlan     : (body) => API.call('/planner/save-plan.php', 'POST', body),

  // Grocery
  generateGrocery : (week_start) => API.call('/grocery/generate-list.php', 'POST', { week_start }),
  getGrocery      : (week_start) => API.call(`/grocery/get-list.php?week_start=${week_start}`),
  toggleItem      : (id,checked) => API.call('/grocery/toggle-item.php','POST',{id,checked}),

  // Food Log
  logMeal    : (body) => API.call('/food-log/log-meal.php', 'POST', body),
  getLog     : (date) => API.call(`/food-log/get-log.php?date=${date}`),
  deleteLog  : (id)   => API.call('/food-log/delete-log.php', 'POST', { id }),
};
