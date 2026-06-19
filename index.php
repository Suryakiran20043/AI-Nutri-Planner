<?php
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /AI-Nutri-Planner/pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>NutriPlan — Premium Smart Meal Planner & Calorie Calculator</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css" rel="stylesheet">
  <link href="/AI-Nutri-Planner/assets/css/main.css" rel="stylesheet">
  <link href="/AI-Nutri-Planner/assets/css/components.css" rel="stylesheet">
  <link href="/AI-Nutri-Planner/assets/css/animations.css" rel="stylesheet">
  <style>
    :root {
      --primary: #1A73E8;
      --accent: #4285F4;
      --bg-dark: #F8F9FA;
      --panel: #FFFFFF;
      --text: #202124;
    }

    body {
      min-height: 100vh;
      margin: 0;
      font-family: 'Outfit', sans-serif;
      background: var(--bg-dark);
      color: var(--text);
      display: flex;
    }

    /* Left Side - Image & Copy */
    .hero-section {
      flex: 1.2;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 60px;
      overflow: hidden;
      background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,249,250,0.85) 100%), url('https://images.unsplash.com/photo-1490645935967-10de6ba17061?q=80&w=2053&auto=format&fit=crop') center/cover;
    }

    .hero-content {
      position: relative;
      z-index: 2;
      max-width: 600px;
      animation: fadeSlideIn 0.8s ease-out;
    }

    .brand {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      font-size: 32px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 60px;
    }

    .brand .logo-icon {
      background: var(--primary);
      color: #ffffff;
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      box-shadow: 0 1px 3px rgba(60,64,67,0.3);
    }

    h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 64px;
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 24px;
    }

    h1 em {
      color: var(--primary);
      font-style: normal;
      
    }

    .hero-desc {
      font-size: 18px;
      line-height: 1.6;
      color: #5F6368;
      margin-bottom: 40px;
    }

    /* Right Side - Auth Forms */
    .auth-section {
      flex: 0.8;
      background: var(--panel);
      color: var(--text);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px;
      border-left: 1px solid #DADCE0;
    }

    .auth-container {
      width: 100%;
      max-width: 420px;
      animation: fadeSlideInRight 0.6s ease-out forwards;
      opacity: 0;
    }

    @keyframes fadeSlideInRight {
      from { opacity: 0; transform: translateX(20px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .auth-header {
      margin-bottom: 32px;
      text-align: center;
    }

    .auth-header h2 {
      font-size: 28px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 8px;
    }

    .auth-header p {
      color: #5F6368;
    }

    /* Tabs */
    .auth-nav {
      display: flex;
      background: #F1F3F4;
      padding: 6px;
      border-radius: 12px;
      margin-bottom: 24px;
      position: relative;
      border: 1px solid #DADCE0;
    }

    .auth-nav-btn {
      flex: 1;
      text-align: center;
      padding: 12px;
      font-weight: 500;
      font-size: 14px;
      color: #5F6368;
      cursor: pointer;
      border-radius: 8px;
      transition: all 0.3s;
      z-index: 1;
    }

    .auth-nav-btn.active {
      color: var(--text);
      font-weight: 600;
      background: #FFFFFF;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Sub Tabs for Login Methods */
    .login-methods {
      display: flex;
      gap: 12px;
      margin-bottom: 24px;
    }

    .method-btn {
      flex: 1;
      padding: 10px;
      border: 1px solid #DADCE0;
      background: #FFFFFF;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-size: 13px;
      font-weight: 500;
      color: #5F6368;
      transition: 0.2s;
    }

    .method-btn.active {
      border-color: var(--primary);
      color: var(--primary);
      background: rgba(26,115,232,0.1);
    }

    .method-btn:hover:not(.active) {
      background: #F1F3F4;
    }

    /* Forms */
    .form-view {
      display: none;
      animation: fadeIn 0.4s ease;
    }

    .form-view.active {
      display: block;
    }

    .input-group {
      margin-bottom: 20px;
    }

    .input-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #5F6368;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .form-control {
      width: 100%;
      padding: 14px 16px;
      border: 1px solid #DADCE0;
      border-radius: 10px;
      font-size: 15px;
      transition: 0.3s;
      font-family: inherit;
      background: #FFFFFF;
      color: var(--text);
    }

    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      background: #F1F3F4;
      box-shadow: 0 0 0 2px rgba(26,115,232,0.2);
    }
    
    .form-control::placeholder {
      color: #9AA0A6;
    }

    .submit-btn {
      width: 100%;
      padding: 14px;
      background: var(--primary);
      color: #ffffff;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 8px;
      transition: 0.3s;
      box-shadow: 0 1px 2px rgba(60,64,67,0.3);
    }

    .submit-btn:hover {
      background: #174EA6;
      transform: translateY(-2px);
      box-shadow: 0 1px 3px rgba(60,64,67,0.15);
    }

    .submit-btn.btn-accent {
      background: #174EA6;
      color: #020617;
    }
    .submit-btn.btn-accent:hover {
      box-shadow: 0 1px 3px rgba(60,64,67,0.15);
    }

    #otp-section {
      display: none;
      margin-top: 16px;
    }

    /* Responsive */
    @media (max-width: 900px) {
      body {
        flex-direction: column;
      }
      .hero-section {
        flex: none;
        padding: 40px 20px;
      }
      h1 { font-size: 40px; }
      .auth-section {
        padding: 40px 20px;
      }
    }
  </style>
</head>
<body>

  <div class="hero-section">
    <div class="hero-content">
      <div class="brand">
        <div class="logo-icon"><i class="ti ti-leaf"></i></div>
        NutriPlan
      </div>
      
      <h1>Fuel your body.<br>Simplify your <em>choices.</em></h1>
      <p class="hero-desc">
        Calculate your personal metabolism targets using clinical formulas, then let our smart AI generate complete custom meal plans using live, real-time products.
      </p>
    </div>
  </div>

  <div class="auth-section">
    <div class="auth-container">
      <div class="auth-header">
        <h2 id="view-title">Welcome Back</h2>
        <p id="view-subtitle">Enter your details to access your dashboard.</p>
      </div>

      <div class="auth-nav">
        <div class="auth-nav-btn active" onclick="setView('login')">Log In</div>
        <div class="auth-nav-btn" onclick="setView('register')">Sign Up</div>
      </div>

      <!-- Login View -->
      <div id="view-login" class="form-view active">
        <div class="login-methods">
          <button class="method-btn active" onclick="setLoginMethod('email')" id="btn-method-email">
            <i class="ti ti-mail"></i> Email
          </button>
          <button class="method-btn" onclick="setLoginMethod('mobile-pass')" id="btn-method-mobile-pass">
            <i class="ti ti-device-mobile"></i> Mobile + Pass
          </button>
          <button class="method-btn" onclick="setLoginMethod('mobile-otp')" id="btn-method-mobile-otp">
            <i class="ti ti-message-circle"></i> OTP
          </button>
        </div>

        <!-- Email Login Form -->
        <form id="form-login-email" class="sub-form-view" onsubmit="handleAuth(event, 'login-email')">
          <div class="input-group">
            <label>Email Address</label>
            <input type="email" id="login-email" class="form-control" required placeholder="name@example.com">
          </div>
          <div class="input-group">
            <label>Password</label>
            <input type="password" id="login-password" class="form-control" required placeholder="••••••••">
          </div>
          <button type="submit" class="submit-btn" id="btn-login-email-submit">
            Log In <i class="ti ti-arrow-right"></i>
          </button>
        </form>

        <!-- Mobile + Password Form -->
        <form id="form-login-mobile-pass" class="sub-form-view" style="display:none;" onsubmit="handleAuth(event, 'login-mobile-pass')">
          <div class="input-group">
            <label>Mobile Number</label>
            <input type="text" id="login-mp-mobile" class="form-control" required placeholder="+1 234 567 8900">
          </div>
          <div class="input-group">
            <label>Password</label>
            <input type="password" id="login-mp-password" class="form-control" required placeholder="••••••••">
          </div>
          <button type="submit" class="submit-btn" id="btn-login-mp-submit">
            Log In <i class="ti ti-arrow-right"></i>
          </button>
        </form>

        <!-- Mobile + OTP Form -->
        <form id="form-login-mobile-otp" class="sub-form-view" style="display:none;" onsubmit="handleAuth(event, 'login-mobile-otp')">
          <div id="otp-request-section">
            <div class="input-group">
              <label>Mobile Number</label>
              <input type="text" id="login-otp-mobile" class="form-control" required placeholder="+1 234 567 8900">
            </div>
            <button type="button" class="submit-btn btn-accent" onclick="requestOTP()" id="btn-request-otp">
              Send OTP <i class="ti ti-send"></i>
            </button>
          </div>
          
          <div id="otp-verify-section" style="display:none;">
            <div class="input-group">
              <label>Enter 6-Digit OTP</label>
              <input type="text" id="login-otp-code" class="form-control" placeholder="123456" maxlength="6" style="letter-spacing: 4px; font-size: 20px; text-align: center;">
            </div>
            <button type="submit" class="submit-btn" id="btn-verify-otp">
              Verify & Log In <i class="ti ti-check"></i>
            </button>
            <div style="text-align: center; margin-top: 16px;">
              <a href="#" onclick="resetOTP()" style="color: #666; font-size: 13px;">Use a different number</a>
            </div>
          </div>
        </form>
      </div>

      <!-- Register View -->
      <div id="view-register" class="form-view">
        <form id="form-register" onsubmit="handleAuth(event, 'register')">
          <div class="input-group">
            <label>Full Name</label>
            <input type="text" id="reg-name" class="form-control" required placeholder="Jane Doe">
          </div>
          <div class="input-group">
            <label>Email Address</label>
            <input type="email" id="reg-email" class="form-control" required placeholder="jane@example.com">
          </div>
          <div class="input-group">
            <label>Mobile Number (Optional)</label>
            <input type="text" id="reg-mobile" class="form-control" placeholder="+1 234 567 8900">
          </div>
          <div class="input-group">
            <label>Password (min. 6 chars)</label>
            <input type="password" id="reg-password" class="form-control" required placeholder="••••••••">
          </div>
          <button type="submit" class="submit-btn btn-accent" id="btn-register-submit">
            Create Account <i class="ti ti-user-plus"></i>
          </button>
        </form>
      </div>

    </div>
  </div>

  <script src="/AI-Nutri-Planner/assets/js/api.js"></script>
  <script src="/AI-Nutri-Planner/assets/js/main.js"></script>
  <script>
    let currentMobileForOtp = '';

    function setView(view) {
      document.querySelectorAll('.auth-nav-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.form-view').forEach(v => v.classList.remove('active'));
      
      if (view === 'login') {
        document.querySelector('.auth-nav-btn:nth-child(1)').classList.add('active');
        document.getElementById('view-login').classList.add('active');
        document.getElementById('view-title').innerText = 'Welcome Back';
        document.getElementById('view-subtitle').innerText = 'Enter your details to access your dashboard.';
      } else {
        document.querySelector('.auth-nav-btn:nth-child(2)').classList.add('active');
        document.getElementById('view-register').classList.add('active');
        document.getElementById('view-title').innerText = 'Create Account';
        document.getElementById('view-subtitle').innerText = 'Start your nutrition journey today.';
      }
    }

    function setLoginMethod(method) {
      document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.sub-form-view').forEach(f => f.style.display = 'none');
      
      if (method === 'email') {
        document.getElementById('btn-method-email').classList.add('active');
        document.getElementById('form-login-email').style.display = 'block';
      } else if (method === 'mobile-pass') {
        document.getElementById('btn-method-mobile-pass').classList.add('active');
        document.getElementById('form-login-mobile-pass').style.display = 'block';
      } else if (method === 'mobile-otp') {
        document.getElementById('btn-method-mobile-otp').classList.add('active');
        document.getElementById('form-login-mobile-otp').style.display = 'block';
      }
    }

    async function requestOTP() {
      const mobileInput = document.getElementById('login-otp-mobile');
      const mobile = mobileInput.value.trim();
      if (!mobile) {
        showToast('Please enter a mobile number', 'error');
        return;
      }

      const btn = document.getElementById('btn-request-otp');
      const origText = btn.innerHTML;
      btn.innerHTML = 'Sending...';
      btn.disabled = true;

      try {
        const res = await API.sendOtp({ mobile_number: mobile });
        showToast('OTP Sent! (Check console/alert for simulation)', 'success');
        
        // For simulation purposes, log it and alert it
        console.log("SIMULATED OTP:", res.simulated_otp);
        alert(`Simulated OTP for ${mobile} is: ${res.simulated_otp}\n\n(In a real app, this would be an SMS)`);
        
        currentMobileForOtp = mobile;
        document.getElementById('otp-request-section').style.display = 'none';
        document.getElementById('otp-verify-section').style.display = 'block';
      } catch (e) {
        showToast(e.message, 'error');
      } finally {
        btn.innerHTML = origText;
        btn.disabled = false;
      }
    }

    function resetOTP() {
      currentMobileForOtp = '';
      document.getElementById('login-otp-code').value = '';
      document.getElementById('otp-verify-section').style.display = 'none';
      document.getElementById('otp-request-section').style.display = 'block';
    }

    async function handleAuth(event, type) {
      event.preventDefault();
      
      let submitBtn;
      try {
        let redirectUrl = '/AI-Nutri-Planner/pages/calculator.php';
        
        if (type === 'login-email') {
          submitBtn = document.getElementById('btn-login-email-submit');
          setLoading(submitBtn, true);
          const email = document.getElementById('login-email').value;
          const password = document.getElementById('login-password').value;
          const data = await API.login({ email, password });
          if (data && data.profile_completed) redirectUrl = '/AI-Nutri-Planner/pages/dashboard.php';
          
        } else if (type === 'login-mobile-pass') {
          submitBtn = document.getElementById('btn-login-mp-submit');
          setLoading(submitBtn, true);
          const mobile = document.getElementById('login-mp-mobile').value;
          const password = document.getElementById('login-mp-password').value;
          const data = await API.loginMobile({ mobile_number: mobile, password });
          if (data && data.profile_completed) redirectUrl = '/AI-Nutri-Planner/pages/dashboard.php';
          
        } else if (type === 'login-mobile-otp') {
          submitBtn = document.getElementById('btn-verify-otp');
          setLoading(submitBtn, true);
          const otp = document.getElementById('login-otp-code').value;
          const data = await API.verifyOtp({ mobile_number: currentMobileForOtp, otp });
          if (data && data.profile_completed) redirectUrl = '/AI-Nutri-Planner/pages/dashboard.php';
          
        } else if (type === 'register') {
          submitBtn = document.getElementById('btn-register-submit');
          setLoading(submitBtn, true);
          const name = document.getElementById('reg-name').value;
          const email = document.getElementById('reg-email').value;
          const mobile = document.getElementById('reg-mobile').value;
          const password = document.getElementById('reg-password').value;
          await API.register({ name, email, mobile_number: mobile, password });
        }
        
        showToast('Success!', 'success');
        setTimeout(() => window.location.href = redirectUrl, 800);
        
      } catch (e) {
        showToast(e.message, 'error');
        if(submitBtn) setLoading(submitBtn, false);
      }
    }

    function setLoading(btn, isLoading) {
      if (!btn) return;
      if (isLoading) {
        btn.dataset.origText = btn.innerHTML;
        btn.innerHTML = '<div class="spinner-ring" style="width:20px;height:20px;border-width:2px;border-color:#fff;border-right-color:transparent;"></div>';
        btn.disabled = true;
      } else {
        btn.innerHTML = btn.dataset.origText;
        btn.disabled = false;
      }
    }
  </script>
</body>
</html>
