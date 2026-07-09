const API = {
  BASE: 'http://localhost:8080/api',

  async call(endpoint, method = 'GET', body = null) {
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      cache: 'no-store'
    };
    const token = localStorage.getItem('jwt_token');
    if (token) {
      opts.headers['Authorization'] = 'Bearer ' + token;
    }
    if (body) opts.body = JSON.stringify(body);
    
    // Add cache busting query param for GET requests to prevent aggressive browser caching
    let url = this.BASE + endpoint;
    if (method === 'GET') {
      const sep = url.includes('?') ? '&' : '?';
      url += sep + '_t=' + Date.now();
    }
    
    const res  = await fetch(url, opts);
    
    if (res.status === 401 || res.status === 403) {
      // Redirect to login gate if unauthorized
      if (!window.location.pathname.endsWith('index') && window.location.pathname !== '/') {
        localStorage.removeItem('jwt_token');
        window.location.href = '/';
        return;
      }
    }
    
    let data;
    try {
      data = await res.json();
    } catch(e) {
      data = null;
    }
    if (res.status >= 400 || (data && data.status === 'error')) {
       throw new Error((data && data.message) || 'An error occurred');
    }
    return data.data !== undefined ? data.data : data;
  },

  // Auth
  register  : (body) => API.call('/auth/register',  'POST', body),
  login     : async (body) => {
    const res = await API.call('/auth/login', 'POST', body);
    if (res && res.accessToken) {
      localStorage.setItem('jwt_token', res.accessToken);
    }
    return res;
  },
  loginMobile: (body) => API.call('/auth/login_mobile', 'POST', body),
  sendOtp   : (body) => API.call('/auth/send_otp',  'POST', body),
  verifyOtp : (body) => API.call('/auth/verify_otp', 'POST', body),
  logout    : ()     => {
    localStorage.removeItem('jwt_token');
    return Promise.resolve();
  },

  // Profile
  getProfile    : ()     => API.call('/user/profile'),
  updateProfile : (body) => API.call('/user/profile', 'PUT', body),

  // Local ETM Dataset
  searchFood : (query, page = 1) =>
    API.call(`/etm/search_etm?q=${encodeURIComponent(query)}&page=${page}`),
  getFood    : (fdcId) => API.call(`/usda/get-food?fdcId=${fdcId}`),

  // Spoonacular Proxy
  searchRecipes : (query, maxCalories = 0, diet = 'anything') =>
    API.call(`/spoonacular/search-recipes?query=${encodeURIComponent(query)}&maxCalories=${maxCalories}&diet=${encodeURIComponent(diet)}`),
  getRecipe     : (id) => API.call(`/spoonacular/get-recipe?id=${id}`),

  // Planner
  generatePlan : (date) => API.call('/planner/generate',  'POST', { date }),
  getPlan      : (date) => API.call(`/planner/plan?date=${date}`),
  swapMeal     : (body) => API.call('/planner/swap-meal', 'POST', body),
  savePlan     : (body) => API.call('/planner/save-plan', 'POST', body),

  // Grocery
  generateGrocery : (week_start) => API.call('/grocery/generate-list', 'POST', { week_start }),
  getGrocery      : (week_start) => API.call(`/grocery/get-list?week_start=${week_start}`),
  toggleItem      : (id,checked) => API.call('/grocery/toggle-item','POST',{id,checked}),

  // Food Log
  logMeal    : (body) => API.call('/food-log/log-meal', 'POST', body),
  getLog     : (date) => API.call(`/food-log/get-log?date=${date}`),
  deleteLog  : (id)   => API.call('/food-log/delete-log', 'POST', { id }),

  // Health report upload
  async uploadReport(file) {
    const formData = new FormData();
    formData.append('report', file);
    
    const res = await fetch(this.BASE + '/health/upload', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });
    
    if (res.status === 401) {
      window.location.href = '/';
      return;
    }
    
    const data = await res.json();
    if (data.status === 'error') throw new Error(data.message);
    return data.data;
  },

  getLatestReport() {
    return API.call('/health/get-latest');
  },

  // EatThisMuch Foods
  searchETMFoods : (query = '', sort = 'name', order = 'asc', page = 1, limit = 20) =>
    API.call(`/etm/search_etm?q=${encodeURIComponent(query)}&sort=${encodeURIComponent(sort)}&order=${encodeURIComponent(order)}&page=${page}&limit=${limit}`),
  getETMFood     : (id) => API.call(`/etm/get_etm_food?id=${id}`),
};


// Added global fallback image generator
const keywordImages = [
  { key: 'roll-up', url: 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600&q=80' },
  { key: 'ham', url: 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=600&q=80' },
  { key: 'lox', url: 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=600&q=80' },
  { key: 'salmon', url: 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=600&q=80' },
  { key: 'strawberry', url: 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=600&q=80' },
  { key: 'egg', url: 'https://images.unsplash.com/photo-1587486913049-53fc88980cfc?w=600&q=80' },
  { key: 'yogurt', url: 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=600&q=80' },
  { key: 'oat', url: 'https://images.unsplash.com/photo-1517673400267-0251440c45dc?w=600&q=80' },
  { key: 'granola', url: 'https://images.unsplash.com/photo-1517673400267-0251440c45dc?w=600&q=80' },
  { key: 'blueberr', url: 'https://images.unsplash.com/photo-1428080922855-87bd63624e52?w=600&q=80' },
  { key: 'milk', url: 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=600&q=80' },
  { key: 'flake', url: 'https://images.unsplash.com/photo-1521406796677-448c90967756?w=600&q=80' },
  { key: 'cheese', url: 'https://images.unsplash.com/photo-1555505019-8c3f1c4aba5f?w=600&q=80' },
  { key: 'chia', url: 'https://images.unsplash.com/photo-1555505019-8c3f1c4aba5f?w=600&q=80' },
  { key: 'peanut', url: 'https://images.unsplash.com/photo-1584852924157-fb9d76e7379f?w=600&q=80' },
  { key: 'bread', url: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&q=80' },
  { key: 'toast', url: 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=600&q=80' },
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
  { key: 'shrimp', url: '/assets/img/default-food.png' },
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
  { key: 'salad', url: 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80' },
  { key: 'smoothie', url: 'https://images.unsplash.com/photo-1505252585461-04db1eb84625?w=600&q=80' },
  { key: 'pancake', url: 'https://images.unsplash.com/photo-1528207776546-384111d0bb89?w=600&q=80' },
  { key: 'waffle', url: 'https://images.unsplash.com/photo-1504113886839-4c2865912443?w=600&q=80' },
  { key: 'burger', url: 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80' },
  { key: 'pizza', url: 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&q=80' },
  { key: 'sandwich', url: 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=600&q=80' },
  { key: 'soup', url: 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&q=80' },
  { key: 'curry', url: 'https://images.unsplash.com/photo-1565557613262-d27a1f59235e?w=600&q=80' },
  { key: 'taco', url: 'https://images.unsplash.com/photo-1551504734-5ee1c4a1479b?w=600&q=80' },
  { key: 'burrito', url: 'https://images.unsplash.com/photo-1626804475297-4160aae013eb?w=600&q=80' },
  { key: 'muffin', url: 'https://images.unsplash.com/photo-1557925923-33b251d5928f?w=600&q=80' },
  { key: 'cookie', url: 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=600&q=80' },
  { key: 'fruit', url: 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=600&q=80' },
  { key: 'vegetable', url: 'https://images.unsplash.com/photo-1566385101042-1a0aa0c1268c?w=600&q=80' }
];

  window.getFallbackImage = function(name) {
    if (!name) return '/assets/img/default-food.png';
    const lowerName = name.toLowerCase();
    for (const item of keywordImages) {
      if (lowerName.includes(item.key)) {
        return item.url;
      }
    }
    // Ultimate fallback is the local premium default image, guaranteed never to 404
    return '/assets/img/default-food.png';
  };

