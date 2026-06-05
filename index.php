<?php
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /nutriplan/pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>NutriPlan — Premium Smart Meal Planner & Calorie Calculator</title>
  <meta name="description" content="Calculate your daily calorie needs and autogenerate weekly meal plans loaded with real USDA nutrients.">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <link href="assets/css/components.css" rel="stylesheet">
  <link href="assets/css/animations.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: radial-gradient(circle at 10% 20%, rgba(126, 200, 164, 0.15) 0%, rgba(247, 245, 240, 1) 90.2%);
      padding: 20px;
    }
    
    .landing-container {
      width: 100%;
      max-width: 1100px;
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: 48px;
      align-items: center;
      animation: fadeSlideIn 0.5s ease-out forwards;
    }

    .hero-side {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .brand-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 28px;
      font-weight: 700;
      color: var(--forest);
    }
    
    .brand-logo .logo-icon {
      background: var(--forest);
      color: var(--lime);
      width: 42px;
      height: 42px;
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }

    .hero-title {
      font-size: 48px;
      line-height: 1.15;
    }

    .hero-title em {
      color: var(--sage);
    }

    .hero-description {
      font-size: 16px;
      color: var(--muted);
      max-width: 480px;
    }

    .features-list {
      display: flex;
      flex-direction: column;
      gap: 16px;
      margin-top: 12px;
    }

    .feature-item {
      display: flex;
      align-items: flex-start;
      gap: 14px;
    }

    .feature-icon {
      background: rgba(124, 200, 164, 0.2);
      color: var(--forest);
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      flex-shrink: 0;
    }

    .feature-text h4 {
      font-size: 15px;
      font-weight: 600;
      margin-bottom: 2px;
    }

    .feature-text p {
      font-size: 13px;
      color: var(--muted);
    }

    .auth-side {
      perspective: 1000px;
    }

    .auth-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 40px;
      box-shadow: var(--shadow-lg);
    }

    .auth-tabs {
      display: flex;
      background: var(--cream);
      border-radius: var(--radius-pill);
      padding: 4px;
      margin-bottom: 32px;
      border: 1px solid var(--border);
    }

    .auth-tab {
      flex: 1;
      text-align: center;
      padding: 10px;
      font-size: 14px;
      font-weight: 600;
      color: var(--muted);
      cursor: pointer;
      border-radius: var(--radius-pill);
      transition: var(--transition);
    }

    .auth-tab.active {
      background: var(--forest);
      color: var(--white);
      box-shadow: 0 4px 10px rgba(27, 61, 47, 0.15);
    }

    .auth-form {
      display: none;
    }

    .auth-form.active {
      display: flex;
      flex-direction: column;
    }

    .auth-submit-btn {
      width: 100%;
      padding: 14px;
      margin-top: 12px;
      font-size: 15px;
    }

    @media (max-width: 900px) {
      .landing-container {
        grid-template-columns: 1fr;
        gap: 40px;
      }
      .hero-title {
        font-size: 36px;
      }
      .auth-card {
        padding: 24px;
      }
    }
  </style>
</head>
<body>

  <div class="landing-container">
    <!-- Left Hero Panel -->
    <div class="hero-side">
      <div class="brand-logo">
        <div class="logo-icon"><i class="ti ti-leaf"></i></div>
        Nutri<em>Plan</em>
      </div>
      
      <h1 class="hero-title">
        Fuel your body. <br>
        Simplify your <em>choices.</em>
      </h1>
      
      <p class="hero-description">
        NutriPlan calculates your personal metabolism targets using Mifflin-St Jeor formulas, then autogenerates complete custom meal plans using live, real-time products from our smart food database.
      </p>

      <div class="features-list">
        <div class="feature-item">
          <div class="feature-icon"><i class="ti ti-flame"></i></div>
          <div class="feature-text">
            <h4>Precision Calorie Calculator</h4>
            <p>Calculate BMR & TDEE macro splits based on weight, height, age, and activity parameters.</p>
          </div>
        </div>

        <div class="feature-item">
          <div class="feature-icon"><i class="ti ti-adjustments-horizontal"></i></div>
          <div class="feature-text">
            <h4>Smart Auto-Meal Generation</h4>
            <p>Unlock recipes mapped precisely to your daily caloric thresholds in seconds.</p>
          </div>
        </div>

        <div class="feature-item">
          <div class="feature-icon"><i class="ti ti-shopping-cart-discount"></i></div>
          <div class="feature-text">
            <h4>Dynamic Grocery Checklist</h4>
            <p>Export all meal plan ingredients instantly into grouped shopping items.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Login/Signup Card Panel -->
    <div class="auth-side">
      <div class="auth-card">
        <div class="auth-tabs">
          <div class="auth-tab active" id="tab-login" onclick="switchTab('login')">Log In</div>
          <div class="auth-tab" id="tab-register" onclick="switchTab('register')">Sign Up</div>
        </div>

        <!-- Login Form -->
        <form class="auth-form active" id="form-login" onsubmit="handleAuth(event, 'login')">
          <div class="form-group">
            <label class="form-label" for="login-email">Email Address</label>
            <input type="email" id="login-email" class="form-input" required placeholder="name@example.com" autocomplete="email">
          </div>
          
          <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label" for="login-password">Password</label>
            <input type="password" id="login-password" class="form-input" required placeholder="••••••••" autocomplete="current-password">
          </div>

          <button type="submit" class="btn btn-primary auth-submit-btn" id="btn-login-submit">
            <span>Access Dashboard</span> <i class="ti ti-arrow-right"></i>
          </button>
        </form>

        <!-- Register Form -->
        <form class="auth-form" id="form-register" onsubmit="handleAuth(event, 'register')">
          <div class="form-group">
            <label class="form-label" for="reg-name">Full Name</label>
            <input type="text" id="reg-name" class="form-input" required placeholder="Jane Doe" autocomplete="name">
          </div>

          <div class="form-group">
            <label class="form-label" for="reg-email">Email Address</label>
            <input type="email" id="reg-email" class="form-input" required placeholder="jane@example.com" autocomplete="email">
          </div>
          
          <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label" for="reg-password">Password (min. 6 chars)</label>
            <input type="password" id="reg-password" class="form-input" required placeholder="••••••••" autocomplete="new-password">
          </div>

          <button type="submit" class="btn btn-lime auth-submit-btn" id="btn-register-submit">
            <span>Create Account</span> <i class="ti ti-arrow-right"></i>
          </button>
        </form>
      </div>
    </div>
  </div>

  <script src="assets/js/api.js"></script>
  <script src="assets/js/main.js"></script>
  <script>
    function switchTab(type) {
      document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
      
      if (type === 'login') {
        document.getElementById('tab-login').classList.add('active');
        document.getElementById('form-login').classList.add('active');
      } else {
        document.getElementById('tab-register').classList.add('active');
        document.getElementById('form-register').classList.add('active');
      }
    }

    async function handleAuth(event, type) {
      event.preventDefault();
      const submitBtn = document.getElementById(`btn-${type}-submit`);
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<div class="spinner-ring" style="width: 20px; height: 20px; border-width: 2px;"></div>';
      
      try {
        let redirectUrl = '/nutriplan/pages/calculator.php';
        if (type === 'login') {
          const email = document.getElementById('login-email').value;
          const password = document.getElementById('login-password').value;
          const loginData = await API.login({ email, password });
          showToast('Login successful! Welcome back.', 'success');
          if (loginData && loginData.profile_completed) {
            redirectUrl = '/nutriplan/pages/dashboard.php';
          }
        } else {
          const name = document.getElementById('reg-name').value;
          const email = document.getElementById('reg-email').value;
          const password = document.getElementById('reg-password').value;
          await API.register({ name, email, password });
          showToast('Account registered successfully!', 'success');
        }
        
        // Wait briefly for toast to show and redirect to calculator/dashboard
        setTimeout(() => {
          window.location.href = redirectUrl;
        }, 800);
        
      } catch (e) {
        showToast(e.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    }
  </script>
</body>
</html>
