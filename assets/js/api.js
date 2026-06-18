const API = {
  BASE: '/AI-Nutri-Planner/api',

  async call(endpoint, method = 'GET', body = null) {
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      cache: 'no-store'
    };
    if (body) opts.body = JSON.stringify(body);
    
    // Add cache busting query param for GET requests to prevent aggressive browser caching
    let url = this.BASE + endpoint;
    if (method === 'GET') {
      const sep = url.includes('?') ? '&' : '?';
      url += sep + '_t=' + Date.now();
    }
    
    const res  = await fetch(url, opts);
    
    if (res.status === 401) {
      // Redirect to login gate if unauthorized
      if (!window.location.pathname.endsWith('index.php') && window.location.pathname !== '/AI-Nutri-Planner/') {
        window.location.href = '/AI-Nutri-Planner/index.php';
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
  loginMobile: (body) => API.call('/auth/login_mobile.php', 'POST', body),
  sendOtp   : (body) => API.call('/auth/send_otp.php',  'POST', body),
  verifyOtp : (body) => API.call('/auth/verify_otp.php', 'POST', body),
  logout    : ()     => API.call('/auth/logout.php',    'POST'),

  // Profile
  getProfile    : ()     => API.call('/user/get-profile.php'),
  updateProfile : (body) => API.call('/user/update-profile.php', 'POST', body),

  // Local ETM Dataset
  searchFood : (query, page = 1) =>
    API.call(`/etm/search_etm.php?q=${encodeURIComponent(query)}&page=${page}`),
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

  // Health report upload
  async uploadReport(file) {
    const formData = new FormData();
    formData.append('report', file);
    
    const res = await fetch(this.BASE + '/health/analyze.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    
    if (res.status === 401) {
      window.location.href = '/AI-Nutri-Planner/index.php';
      return;
    }
    
    const data = await res.json();
    if (data.status === 'error') throw new Error(data.message);
    return data.data;
  },

  getLatestReport() {
    return API.call('/health/get-latest.php');
  },

  // EatThisMuch Foods
  searchETMFoods : (query = '', sort = 'name', order = 'asc', page = 1, limit = 20) =>
    API.call(`/etm/search_etm.php?q=${encodeURIComponent(query)}&sort=${encodeURIComponent(sort)}&order=${encodeURIComponent(order)}&page=${page}&limit=${limit}`),
  getETMFood     : (id) => API.call(`/etm/get_etm_food.php?id=${id}`),
};
