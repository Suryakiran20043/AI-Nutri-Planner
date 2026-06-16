<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /AI-Nutri-Planner/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Clinical Health Report — NutriPlan</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css" rel="stylesheet">
  <link href="../assets/css/main.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/components.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/animations.css?v=<?= time() ?>" rel="stylesheet">
  
  <style>
    /* Premium Page Styling */
    .health-container {
      max-width: 1100px;
      margin: 0 auto;
      padding-bottom: 40px;
    }
    
    /* Upload Section */
    .upload-card {
      background: var(--white);
      border: 1.5px dashed var(--border);
      border-radius: var(--radius-lg);
      padding: 48px 24px;
      text-align: center;
      transition: var(--transition);
      cursor: pointer;
      box-shadow: var(--shadow-sm);
    }
    .upload-card:hover, .upload-card.dragover {
      border-color: var(--sage);
      background: var(--cream);
    }
    .upload-icon {
      font-size: 48px;
      color: var(--sage);
      margin-bottom: 16px;
      display: inline-block;
    }
    .upload-title {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      font-weight: 600;
      color: var(--forest);
      margin-bottom: 8px;
    }
    .upload-sub {
      font-size: 13.5px;
      color: var(--muted);
      margin-bottom: 24px;
    }
    
    /* Loading Pipeline visualizer */
    .pipeline-loader {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 40px;
      max-width: 550px;
      margin: 40px auto;
      box-shadow: var(--shadow-md);
      display: none;
    }
    .pipeline-title {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      font-weight: 600;
      color: var(--forest);
      text-align: center;
      margin-bottom: 24px;
    }
    .pipeline-steps {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }
    .step-item {
      display: flex;
      align-items: center;
      gap: 14px;
      font-size: 13.5px;
      color: var(--muted);
      font-weight: 500;
    }
    .step-icon {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      border: 2px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      color: var(--muted);
      font-weight: bold;
      transition: var(--transition);
    }
    .step-item.active {
      color: var(--text);
    }
    .step-item.active .step-icon {
      border-color: var(--sage);
      background: var(--sage);
      color: white;
    }
    .step-item.completed {
      color: var(--sage);
    }
    .step-item.completed .step-icon {
      border-color: var(--lime);
      background: var(--lime);
      color: var(--forest);
    }
    .step-item.completed .step-icon::after {
      content: "✓";
    }

    /* Dashboard Layout */
    .dashboard-view {
      display: none;
      animation: fadeIn 0.4s ease forwards;
    }
    
    /* Biomarker Grid */
    .biomarkers-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    .biomarker-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 16px;
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: var(--transition);
    }
    .biomarker-card:hover {
      border-color: var(--sage);
      transform: translateY(-2px);
    }
    .biomarker-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 12px;
    }
    .biomarker-name {
      font-size: 13px;
      font-weight: 600;
      color: var(--text);
    }
    .biomarker-status {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      padding: 3px 8px;
      border-radius: 12px;
      letter-spacing: 0.03em;
    }
    .status-normal { background: #E6F4EA; color: #137333; }
    .status-high { background: #FCE8E6; color: #C5221F; }
    .status-low { background: #E8F0FE; color: #1A73E8; }
    
    .biomarker-value-row {
      display: flex;
      align-items: baseline;
      gap: 4px;
      margin-bottom: 8px;
    }
    .biomarker-val {
      font-size: 26px;
      font-weight: 600;
      font-family: 'Playfair Display', serif;
      color: var(--forest);
    }
    .biomarker-unit {
      font-size: 12px;
      color: var(--muted);
    }
    .biomarker-range {
      font-size: 11px;
      color: var(--muted);
      border-top: 1px solid var(--cream);
      padding-top: 8px;
      display: flex;
      justify-content: space-between;
    }
    
    /* Risk Alert Box */
    .risk-alert-card {
      background: #FFF7F0;
      border: 1px solid #FFE2CC;
      border-radius: var(--radius-md);
      padding: 16px 20px;
      margin-bottom: 24px;
      display: flex;
      gap: 16px;
      align-items: flex-start;
    }
    .risk-alert-icon {
      font-size: 24px;
      color: #FF6600;
    }
    .risk-alert-content h4 {
      font-size: 14px;
      font-weight: 600;
      color: #8A3B00;
      margin-bottom: 4px;
    }
    .risk-alert-content p {
      font-size: 13px;
      color: #663300;
      line-height: 1.4;
    }

    /* Dietary Rules Grid */
    .diet-split-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 28px;
    }
    .diet-list-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 20px;
      box-shadow: var(--shadow-sm);
    }
    .diet-list-title {
      font-size: 13.5px;
      font-weight: 600;
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .diet-list-items {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .diet-badge {
      font-size: 12px;
      padding: 5px 12px;
      border-radius: var(--radius-sm);
      font-weight: 500;
      border: 1px solid var(--border);
    }
    .badge-recommend {
      background: #E8F5E9;
      border-color: #C8E6C9;
      color: #2E7D32;
    }
    .badge-avoid {
      background: #FFEBEE;
      border-color: #FFCDD2;
      color: #C62828;
    }
    
    /* Medical Meal Plan Grid */
    .medical-plan-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }
    .medical-meals-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    .medical-meal-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      transition: var(--transition);
    }
    .medical-meal-card:hover {
      border-color: var(--sage);
      transform: translateY(-2px);
    }
    .medical-meal-body {
      padding: 16px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .medical-meal-slot {
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
      color: var(--sage);
      letter-spacing: 0.08em;
      margin-bottom: 6px;
    }
    .medical-meal-name {
      font-size: 14.5px;
      font-weight: 600;
      color: var(--text);
      line-height: 1.3;
      margin-bottom: 12px;
    }
    .medical-meal-macros {
      display: flex;
      gap: 10px;
      font-size: 11.5px;
      color: var(--muted);
      margin-bottom: 12px;
      border-top: 1px solid var(--cream);
      padding-top: 8px;
    }
    .medical-meal-inst {
      font-size: 12px;
      color: var(--text);
      line-height: 1.4;
      background: var(--cream);
      padding: 8px 12px;
      border-radius: var(--radius-sm);
    }

    /* Desktop layout for main results and gauge */
    .desktop-main-results {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 20px;
      margin-bottom: 24px;
    }
    
    @media (max-width: 800px) {
      .desktop-main-results {
        grid-template-columns: 1fr !important;
      }
    }

    /* Staggered card entry animations */
    @keyframes cardFadeIn {
      from {
        opacity: 0;
        transform: translateY(15px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .biomarker-card-animated {
      opacity: 0;
      animation: cardFadeIn 0.5s ease-out forwards;
    }
  </style>
</head>
<body>
  
  <div class="progress-bar" id="pbar" style="width:10%"></div>

  <div class="app">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main">
      
      <!-- Topbar -->
      <div class="topbar animate-in">
        <div>
          <div class="page-title">Clinical <span>Health Report</span></div>
          <div style="font-size:13px;color:var(--muted);margin-top:4px" id="report-subtitle">Analyze clinical blood test metrics to construct targeted meal plans</div>
        </div>
        <div class="topbar-right" id="topbar-actions-view">
          <button class="btn btn-outline" id="btn-upload-new" style="display:none;" onclick="showUploadView()">📤 Upload New Report</button>
        </div>
      </div>

      <div class="health-container animate-in" style="animation-delay: 0.05s">
        
        <!-- Upload Dropzone View -->
        <div id="upload-view">
          <div class="card card-p" style="margin-bottom: 24px;">
            <p style="font-size:14px;color:var(--text);line-height:1.5;margin-bottom:16px;">
              <strong>Upload your latest blood test report</strong> (Image/PDF) to generate a personalized diet plan based on your exact biomarkers.
              Our pipeline parses clinical indicators like glucose, cholesterol panels, hemoglobin (iron), Vitamin D/B12, and blood pressure to identify deficiencies, flags risk criteria, and designs meals targeting those deficits.
            </p>
            <div class="upload-card" id="drop-zone" onclick="document.getElementById('report-file').click()">
              <div class="upload-icon">📄</div>
              <div class="upload-title">Drag & drop your lab report here</div>
              <div class="upload-sub">Supports PDF, PNG, JPG, JPEG (Max 10MB)</div>
              <button class="btn btn-lime" style="display:inline-flex;margin:0 auto;">Browse Files</button>
              <input type="file" id="report-file" style="display:none;" accept=".pdf,.png,.jpg,.jpeg,.tiff,.bmp" onchange="handleFileSelect(event)">
            </div>
          </div>
        </div>

        <!-- Pipeline Loader Screen -->
        <div class="pipeline-loader" id="pipeline-loader">
          <div class="pipeline-title">AI Clinical Pipeline Processing</div>
          <div class="pipeline-steps">
            <div class="step-item" id="step-1">
              <div class="step-icon">1</div>
              <span>Uploading lab report file securely...</span>
            </div>
            <div class="step-item" id="step-2">
              <div class="step-icon">2</div>
              <span>Extracting raw report text via OCR engine...</span>
            </div>
            <div class="step-item" id="step-3">
              <div class="step-icon">3</div>
              <span>Running Clinical NLP matching & biomarker parsing...</span>
            </div>
            <div class="step-item" id="step-4">
              <div class="step-icon">4</div>
              <span>Predicting dietary constraints & health risks...</span>
            </div>
            <div class="step-item" id="step-5">
              <div class="step-icon">5</div>
              <span>Compiling medically tailored meal plan...</span>
            </div>
          </div>
          <div style="margin-top:30px; text-align:center; font-size:12px; color:var(--muted)">
            This may take up to 15 seconds. Please do not close this page.
          </div>
        </div>

        <!-- Dashboard / Results View -->
        <div class="dashboard-view" id="dashboard-view">
          
          <!-- Report Header Info -->
          <div class="card card-p" style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-size:12px; font-weight:600; text-transform:uppercase; color:var(--sage); letter-spacing:0.05em;">Active Health Profile</div>
              <h3 style="font-family:'Playfair Display',serif; font-size:18px; font-weight:600; color:var(--forest); margin:4px 0 0 0;" id="txt-active-report-title">Blood Report: report.pdf</h3>
            </div>
            <div style="font-size:12px; color:var(--muted); text-align:right;">
              Parsed: <span id="txt-active-report-date">June 8, 2026</span>
            </div>
          </div>

          <!-- Flex/Grid container for Health Risks and the Circular Gauge -->
          <div class="desktop-main-results">
            <!-- Left: Health Risks list -->
            <div style="display: flex; flex-direction: column; gap: 16px;" id="risk-alerts-container">
              <!-- Populated dynamically -->
            </div>
            
            <!-- Right: Circular animated health score gauge -->
            <div class="card card-p" style="text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 250px;">
              <h4 style="font-size: 13px; font-weight: 600; color: var(--forest); margin-bottom: 20px; font-family:'DM Sans', sans-serif; text-transform: uppercase; letter-spacing: 0.08em;">Overall Health Risk</h4>
              
              <div class="gauge-wrapper" style="position: relative; width: 140px; height: 140px;">
                <svg width="140" height="140" viewBox="0 0 140 140">
                  <!-- Background circle -->
                  <circle cx="70" cy="70" r="58" fill="none" stroke="var(--border)" stroke-width="10"></circle>
                  <!-- Foreground animated circle -->
                  <circle id="gauge-progress-circle" cx="70" cy="70" r="58" fill="none" stroke="var(--coral)" stroke-width="10" 
                          stroke-dasharray="364.4" stroke-dashoffset="364.4" stroke-linecap="round"
                          transform="rotate(-90 70 70)" style="transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1), stroke 1s;"></circle>
                </svg>
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                  <div style="display: flex; align-items: baseline; justify-content: center;">
                    <span id="gauge-value-text" style="font-size: 36px; font-weight: 700; font-family: 'Playfair Display', serif; color: var(--forest);">0</span>
                    <span style="font-size: 14px; color: var(--muted); font-weight: 600; margin-left: 2px;">%</span>
                  </div>
                </div>
              </div>
              <div id="gauge-label-text" style="font-size: 13px; font-weight: 600; color: var(--muted); margin-top: 14px;">Calculating...</div>
            </div>
          </div>

          <!-- Section title: Biomarkers -->
          <h4 style="font-family:'Playfair Display',serif; font-size:17px; font-weight:600; color:var(--forest); margin-bottom:12px;">Clinical Biomarker Metrics</h4>
          
          <div class="biomarkers-grid" id="biomarkers-container">
            <!-- Populated dynamically -->
          </div>

          <!-- Section: Food Recommendations -->
          <div class="diet-split-grid">
            
            <!-- Recommend Foods -->
            <div class="diet-list-card">
              <div class="diet-list-title" style="color:#2E7D32;">
                <span>✅</span> Priority Foods (To Include)
              </div>
              <div class="diet-list-items" id="recommend-foods-container">
                <!-- badges -->
              </div>
            </div>

            <!-- Avoid Foods -->
            <div class="diet-list-card">
              <div class="diet-list-title" style="color:#C62828;">
                <span>❌</span> Foods to Restrict
              </div>
              <div class="diet-list-items" id="avoid-foods-container">
                <!-- badges -->
              </div>
            </div>

          </div>

          <!-- Section: Medical Meal Plan -->
          <div class="medical-plan-header">
            <h4 style="font-family:'Playfair Display',serif; font-size:17px; font-weight:600; color:var(--forest); margin:0;">Medically Tailored Meal Plan</h4>
            <div style="font-size:12px; color:var(--muted);" id="txt-meal-totals">Totals: 0 kcal | 0g protein | 0g carbs</div>
          </div>

          <div class="medical-meals-grid" id="meals-container">
            <!-- Populated dynamically -->
          </div>

        </div>

      </div>

    </main>
  </div>

  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/main.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // 1. Initial check: Load existing report dashboard if available
      loadLatestReport();
      
      // Bind drag and drop events
      const dropZone = document.getElementById('drop-zone');
      
      ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
          e.preventDefault();
          dropZone.classList.add('dragover');
        }, false);
      });

      ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
          e.preventDefault();
          dropZone.classList.remove('dragover');
        }, false);
      });

      dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
          uploadReportFile(files[0]);
        }
      });
    });

    async function loadLatestReport() {
      try {
        const data = await API.getLatestReport();
        if (data && data.has_report) {
          renderDashboard(data);
        } else {
          showUploadView();
        }
      } catch (err) {
        showToast('Error loading report dashboard: ' + err.message, 'error');
        showUploadView();
      }
    }

    function showUploadView() {
      document.getElementById('upload-view').style.display = 'block';
      document.getElementById('dashboard-view').style.display = 'none';
      document.getElementById('pipeline-loader').style.display = 'none';
      document.getElementById('btn-upload-new').style.display = 'none';
      document.getElementById('pbar').style.width = '10%';
    }

    function handleFileSelect(e) {
      const files = e.target.files;
      if (files.length > 0) {
        uploadReportFile(files[0]);
      }
    }

    async function uploadReportFile(file) {
      // 1. Toggle loader UI
      document.getElementById('upload-view').style.display = 'none';
      document.getElementById('dashboard-view').style.display = 'none';
      const loader = document.getElementById('pipeline-loader');
      loader.style.display = 'block';
      
      const pbar = document.getElementById('pbar');
      pbar.style.width = '20%';

      // Reset steps
      resetSteps();
      
      // Run progress simulation on UI
      activateStep(1); // Uploading...
      
      try {
        // Run simulated increments for processing visual response
        setTimeout(() => { activateStep(2); pbar.style.width = '40%'; }, 1800);
        setTimeout(() => { activateStep(3); pbar.style.width = '60%'; }, 3500);
        setTimeout(() => { activateStep(4); pbar.style.width = '80%'; }, 5000);
        setTimeout(() => { activateStep(5); pbar.style.width = '95%'; }, 6500);

        // Make API Call
        const result = await API.uploadReport(file);
        
        // Wait a small moment to show step 5 completed
        setTimeout(async () => {
          completeStep(5);
          pbar.style.width = '100%';
          showToast('Health analysis completed successfully!', 'success');
          
          // Fetch latest processed results to render dashboard
          const data = await API.getLatestReport();
          renderDashboard(data);
        }, 7500);

      } catch (err) {
        showToast('Processing report failed: ' + err.message, 'error');
        showUploadView();
      }
    }

    function resetSteps() {
      for (let i = 1; i <= 5; i++) {
        const item = document.getElementById('step-' + i);
        item.className = 'step-item';
      }
    }

    function activateStep(num) {
      // Complete previous steps
      for (let i = 1; i < num; i++) {
        const prev = document.getElementById('step-' + i);
        prev.className = 'step-item completed';
      }
      const item = document.getElementById('step-' + num);
      if (item) {
        item.className = 'step-item active';
      }
    }

    function completeStep(num) {
      const item = document.getElementById('step-' + num);
      if (item) {
        item.className = 'step-item completed';
      }
    }

    function renderDashboard(data) {
      document.getElementById('upload-view').style.display = 'none';
      document.getElementById('pipeline-loader').style.display = 'none';
      document.getElementById('dashboard-view').style.display = 'block';
      document.getElementById('btn-upload-new').style.display = 'inline-flex';
      document.getElementById('pbar').style.width = '100%';

      // 1. Report Metadata
      document.getElementById('txt-active-report-title').textContent = 'Blood Report: ' + data.report.file_name;
      const parsedDate = new Date(data.report.uploaded_at).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
      });
      document.getElementById('txt-active-report-date').textContent = parsedDate;

      // 2. Risk Alerts Card & SVG Circular Gauge
      const risksContainer = document.getElementById('risk-alerts-container');
      risksContainer.innerHTML = '';
      if (data.health_risks && data.health_risks.length > 0) {
        data.health_risks.forEach(risk => {
          const alert = document.createElement('div');
          alert.className = 'risk-alert-card';
          alert.style.display = 'flex';
          alert.style.flexDirection = 'column';
          alert.style.width = '100%';
          
          let barColor = 'var(--success)';
          if (risk.severity === 'HIGH') {
            barColor = 'var(--coral)';
          } else if (risk.severity === 'MODERATE') {
            barColor = 'var(--amber)';
          }
          
          alert.innerHTML = `
            <div style="display: flex; gap: 16px; align-items: flex-start; width: 100%;">
              <div class="risk-alert-icon">⚠️</div>
              <div class="risk-alert-content" style="flex-grow: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                  <h4 style="margin: 0; font-size: 14px; font-weight:600; color: var(--forest);">${risk.condition} Detected</h4>
                  <span class="biomarker-status" style="background: rgba(0,0,0,0.05); color: var(--text); border-radius:12px; padding: 3px 8px; font-size: 10px; font-weight:700;">${risk.severity} (${risk.risk_percentage}%)</span>
                </div>
                <p style="font-size: 13px; color: var(--muted); line-height: 1.45;">Your blood biomarkers show metrics associated with ${risk.condition.toLowerCase()}. Our meal planner has adjusted your nutritional targets and prioritized foods to help mitigate these levels.</p>
              </div>
            </div>
            <!-- Progress Bar -->
            <div class="progress-bar-container" style="height: 6px; background: rgba(0,0,0,0.05); border-radius: 3px; overflow: hidden; margin-top: 14px; width: 100%;">
              <div class="progress-bar-fill" style="width: 0%; height: 100%; border-radius: 3px; background: ${barColor}; transition: width 1.5s cubic-bezier(0.1, 0.8, 0.25, 1);" data-target="${risk.risk_percentage}"></div>
            </div>
          `;
          risksContainer.appendChild(alert);
        });

        // Trigger slide-in animation for progress bars
        setTimeout(() => {
          document.querySelectorAll('.progress-bar-fill').forEach(bar => {
            const target = bar.getAttribute('data-target');
            bar.style.width = target + '%';
          });
        }, 100);
      } else {
        risksContainer.innerHTML = `
          <div class="risk-alert-card" style="background: #F4FBF7; border-color: #D3F2E4; color: #137333; display: flex; gap: 16px; align-items: flex-start; width: 100%;">
            <div class="risk-alert-icon" style="color: #137333;">✓</div>
            <div class="risk-alert-content">
              <h4 style="color: #137333; margin:0 0 4px 0;">No Significant Health Risks Detected</h4>
              <p style="font-size: 13px; color: #137333;">Great! All parsed biomarkers are within normal reference ranges. Continue maintaining a balanced lifestyle.</p>
            </div>
          </div>
        `;
      }

      // Animate Overall Circular Risk Score Gauge
      const score = data.report.overall_risk_score || 0;
      const progressCircle = document.getElementById('gauge-progress-circle');
      const valueText = document.getElementById('gauge-value-text');
      const labelText = document.getElementById('gauge-label-text');
      
      // Calculate stroke-dashoffset (total length is 364.4)
      const offset = 364.4 - (364.4 * score / 100);
      progressCircle.style.strokeDashoffset = offset;
      
      // Configure gauge color and label
      if (score >= 70) {
        progressCircle.style.stroke = 'var(--coral)'; // high
        labelText.textContent = 'High Health Risk';
        labelText.style.color = 'var(--coral)';
      } else if (score >= 40) {
        progressCircle.style.stroke = 'var(--amber)'; // moderate
        labelText.textContent = 'Moderate Health Risk';
        labelText.style.color = 'var(--amber)';
      } else {
        progressCircle.style.stroke = 'var(--success)'; // low
        labelText.textContent = 'Low Health Risk (Healthy)';
        labelText.style.color = 'var(--success)';
      }

      // Number count-up animation
      let currentVal = 0;
      const duration = 1500; // 1.5s
      const steps = 30;
      const stepTime = duration / steps;
      const increment = score / steps;
      
      const timer = setInterval(() => {
        currentVal += increment;
        if (currentVal >= score) {
          valueText.textContent = Math.round(score);
          clearInterval(timer);
        } else {
          valueText.textContent = Math.round(currentVal);
        }
      }, stepTime);

      // 3. Biomarkers grid (Staggered Load)
      const bioContainer = document.getElementById('biomarkers-container');
      bioContainer.innerHTML = '';
      let index = 0;
      for (const [key, bio] of Object.entries(data.biomarkers)) {
        const card = document.createElement('div');
        card.className = 'biomarker-card biomarker-card-animated';
        card.style.animationDelay = (index * 0.08) + 's';
        index++;
        
        let statusClass = 'status-normal';
        if (bio.status === 'HIGH') statusClass = 'status-high';
        else if (bio.status === 'LOW') statusClass = 'status-low';

        card.innerHTML = `
          <div class="biomarker-header">
            <div class="biomarker-name">${bio.display_name}</div>
            <div class="biomarker-status ${statusClass}">${bio.status}</div>
          </div>
          <div class="biomarker-value-row">
            <div class="biomarker-val">${bio.value}</div>
            <div class="biomarker-unit">${bio.unit}</div>
          </div>
          <div class="biomarker-range">
            <span>Ref: ${bio.reference_range} ${bio.unit}</span>
          </div>
        `;
        bioContainer.appendChild(card);
      }

      // 4. Priority Foods & Avoided Foods
      const recContainer = document.getElementById('recommend-foods-container');
      recContainer.innerHTML = '';
      if (data.dietary_summary.recommend_foods && data.dietary_summary.recommend_foods.length > 0) {
        data.dietary_summary.recommend_foods.forEach(food => {
          const badge = document.createElement('div');
          badge.className = 'diet-badge badge-recommend';
          badge.textContent = food;
          recContainer.appendChild(badge);
        });
      } else {
        recContainer.innerHTML = '<div style="font-size:12px;color:var(--muted)">No specific foods highlighted. Eat a balanced diet.</div>';
      }

      const avoidContainer = document.getElementById('avoid-foods-container');
      avoidContainer.innerHTML = '';
      if (data.dietary_summary.avoid_foods && data.dietary_summary.avoid_foods.length > 0) {
        data.dietary_summary.avoid_foods.forEach(food => {
          const badge = document.createElement('div');
          badge.className = 'diet-badge badge-avoid';
          badge.textContent = food;
          avoidContainer.appendChild(badge);
        });
      } else {
        avoidContainer.innerHTML = '<div style="font-size:12px;color:var(--muted)">No specific restrictions flagged.</div>';
      }

      // 5. Medical Meal Plan
      const mealsContainer = document.getElementById('meals-container');
      mealsContainer.innerHTML = '';
      
      const slots = ['breakfast', 'lunch', 'dinner', 'snack'];
      let totalCalories = 0;
      let totalProtein = 0;
      let totalCarbs = 0;
      let totalFat = 0;

      slots.forEach(slot => {
        const meal = data.meal_plan[slot];
        if (meal) {
          totalCalories += meal.calories;
          totalProtein += meal.protein;
          totalCarbs += meal.carbs;
          totalFat += meal.fat;

          const card = document.createElement('div');
          card.className = 'medical-meal-card';
          card.innerHTML = `
            <div class="medical-meal-body">
              <div>
                <div class="medical-meal-slot">${slot}</div>
                <div class="medical-meal-name">${meal.name}</div>
              </div>
              <div>
                <div class="medical-meal-macros">
                  <span>🔥 ${meal.calories} kcal</span>
                  <span>🍗 P: ${meal.protein}g</span>
                  <span>🍞 C: ${meal.carbs}g</span>
                  <span>🥑 F: ${meal.fat}g</span>
                </div>
                <div class="medical-meal-inst">${meal.instructions}</div>
              </div>
            </div>
          `;
          mealsContainer.appendChild(card);
        }
      });

      document.getElementById('txt-meal-totals').textContent = 
        `Daily Targets: ${totalCalories} kcal | Protein: ${Math.round(totalProtein)}g | Carbs: ${Math.round(totalCarbs)}g | Fat: ${Math.round(totalFat)}g`;
    }
  </script>
</body>
</html>
