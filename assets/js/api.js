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


// Added global fallback image generator
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

  window.getFallbackImage = function(name) {
    if (!name) return keywordImages[0].url;
    const lowerName = name.toLowerCase();
    for (const item of keywordImages) {
      if (lowerName.includes(item.key)) {
        return item.url;
      }
    }
    let hash = 0;
    for(let i=0; i<name.length; i++){
      hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return keywordImages[Math.abs(hash) % keywordImages.length].url;
  };