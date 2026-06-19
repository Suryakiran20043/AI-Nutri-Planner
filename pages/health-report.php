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
  
  <!-- Animation Libraries -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  
  <style>
    /* Premium Page Styling */
    .health-container {
      max-width: 1100px;
      margin: 0 auto;
      padding-bottom: 40px;
      position: relative;
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
    }
    
    /* Risk Alert Box */
    .risk-alert-card {
      background: #FFF7F0;
      border: 1px solid #FFE2CC;
      border-radius: var(--radius-md);
      padding: 16px 20px;
      margin-bottom: 24px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    
    /* Medical Meal Plan Grid - NEW ANIMATED UI */
    .medical-meals-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    
    .meal-card-anim {
      background: var(--white);
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      position: relative;
      cursor: pointer;
      transform-style: preserve-3d;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border: 1px solid var(--border);
    }
    
    .meal-card-anim:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
      border-color: var(--sage);
    }
    
    /* Glowing effect on hover */
    .meal-card-anim::before {
      content: "";
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      box-shadow: 0 0 20px rgba(117, 160, 115, 0.4);
      opacity: 0;
      transition: opacity 0.4s ease;
      z-index: -1;
      border-radius: inherit;
    }
    .meal-card-anim:hover::before {
      opacity: 1;
    }

    .meal-card-img-wrapper {
      height: 160px;
      overflow: hidden;
      position: relative;
    }
    
    .meal-card-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s ease;
    }
    
    .meal-card-anim:hover .meal-card-img {
      transform: scale(1.1);
    }

    .meal-card-score {
      position: absolute;
      top: 12px;
      right: 12px;
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(4px);
      padding: 6px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      color: var(--forest);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .meal-card-body {
      padding: 20px;
      background: linear-gradient(to top, rgba(255,255,255,1) 80%, rgba(255,255,255,0.9));
    }
    
    .meal-slot {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--sage);
      letter-spacing: 0.1em;
      margin-bottom: 6px;
    }

    .meal-name {
      font-family: 'Playfair Display', serif;
      font-size: 18px;
      font-weight: 600;
      color: var(--forest);
      margin-bottom: 12px;
      line-height: 1.2;
    }
    
    .meal-macros-pill {
      display: inline-flex;
      background: var(--cream);
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      color: var(--text);
      margin-right: 6px;
      margin-bottom: 6px;
    }

    /* Modal / Glassmorphism Panel */
    .recipe-modal-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(5px);
      z-index: 999;
      display: none;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .recipe-modal {
      background: rgba(255, 255, 255, 0.95);
      border: 1px solid rgba(255,255,255,0.4);
      box-shadow: 0 20px 50px rgba(0,0,0,0.2);
      border-radius: var(--radius-lg);
      width: 90%;
      max-width: 800px;
      max-height: 90vh;
      overflow-y: auto;
      transform: translateY(50px);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      opacity: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
    }
    
    .recipe-modal.active {
      transform: translateY(0);
      opacity: 1;
    }
    
    .modal-hero {
      height: 250px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    
    .modal-close {
      position: absolute;
      top: 16px; right: 16px;
      background: rgba(0,0,0,0.5);
      color: white;
      border: none;
      width: 32px; height: 32px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 16px;
      display: flex; align-items: center; justify-content: center;
      transition: background 0.2s;
    }
    .modal-close:hover { background: rgba(0,0,0,0.8); }

    .modal-content-wrap {
      padding: 30px;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 24px;
      border-bottom: 1px solid var(--border);
      padding-bottom: 20px;
    }
    
    .modal-title {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      color: var(--forest);
      margin-bottom: 8px;
    }

    .health-score-circle {
      width: 60px; height: 60px;
      border-radius: 50%;
      border: 4px solid var(--sage);
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      color: var(--forest); font-weight: 700;
    }

    .modal-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
    }

    @media (max-width: 768px) {
      .modal-grid { grid-template-columns: 1fr; gap: 20px; }
    }

    .ing-item {
      display: flex; justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px dashed var(--border);
      font-size: 14px;
      opacity: 0;
      transform: translateX(-10px);
    }
    
    /* Timeline styling for instructions */
    .timeline {
      position: relative;
      padding-left: 20px;
      border-left: 2px solid var(--sage);
      margin-top: 20px;
    }
    .timeline-item {
      position: relative;
      margin-bottom: 20px;
      padding-left: 15px;
      opacity: 0;
      transform: translateX(20px);
    }
    .timeline-item::before {
      content: "";
      position: absolute;
      left: -26px;
      top: 2px;
      width: 10px; height: 10px;
      border-radius: 50%;
      background: var(--lime);
      border: 2px solid var(--sage);
    }

    .disease-note {
      background: #FFF3E0;
      border-left: 4px solid #FF9800;
      padding: 12px 16px;
      border-radius: 4px;
      font-size: 13px;
      color: #E65100;
      margin-top: 10px;
      margin-bottom: 15px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }
  </style>
</head>
<body>
  <div class="app">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main">
      <div class="topbar">
        <div>
          <div class="page-title">Clinical <span>Health Report</span></div>
          <div style="font-size:13px;color:var(--muted);margin-top:4px">Personalized Medical Meal Planner via RAG</div>
        </div>
        <div class="topbar-right" id="topbar-actions-view">
          <button class="btn btn-outline" id="btn-upload-new" style="display:none;" onclick="showUploadView()">📤 Upload New Report</button>
        </div>
      </div>

      <div class="health-container">
        <!-- Upload Dropzone -->
        <div id="upload-view" class="animate__animated animate__fadeIn">
          <div class="card card-p" style="margin-bottom: 24px;">
            <p style="font-size:14px;color:var(--text);line-height:1.5;margin-bottom:16px;">
              <strong>Upload your latest blood test report</strong> to generate a personalized diet plan based on your exact biomarkers and physical profile.
            </p>
            <div class="upload-card" id="drop-zone" onclick="document.getElementById('report-file').click()">
              <div class="upload-icon">📄</div>
              <div class="upload-title">Drag & drop your lab report here</div>
              <div class="upload-sub">Supports PDF, PNG, JPG</div>
              <button class="btn btn-lime">Browse Files</button>
              <input type="file" id="report-file" style="display:none;" accept=".pdf,.png,.jpg,.jpeg" onchange="handleFileSelect(event)">
            </div>
          </div>
        </div>

        <!-- Pipeline Loader -->
        <div class="pipeline-loader animate__animated animate__zoomIn" id="pipeline-loader">
          <div class="pipeline-title">AI Clinical Pipeline Processing</div>
          <div class="pipeline-steps">
            <div class="step-item" id="step-1"><div class="step-icon">1</div><span>Uploading lab report file securely...</span></div>
            <div class="step-item" id="step-2"><div class="step-icon">2</div><span>Extracting raw report text via OCR...</span></div>
            <div class="step-item" id="step-3"><div class="step-icon">3</div><span>Running Clinical NLP matching...</span></div>
            <div class="step-item" id="step-4"><div class="step-icon">4</div><span>Predicting dietary constraints...</span></div>
            <div class="step-item" id="step-5"><div class="step-icon">5</div><span>RAG Generation & Ingredient Scaling...</span></div>
          </div>
        </div>

        <!-- Dashboard View -->
        <div class="dashboard-view" id="dashboard-view">
          <div class="card card-p" style="margin-bottom: 20px;">
            <div style="font-size:12px; font-weight:600; color:var(--sage); text-transform:uppercase;">Active Report</div>
            <h3 style="font-family:'Playfair Display',serif; color:var(--forest); margin-top:4px;" id="txt-active-report-title">Report</h3>
          </div>

          <div id="risk-alerts-container"></div>
          
          <h4 style="font-family:'Playfair Display',serif; font-size:17px; margin-bottom:16px; margin-top:20px;">Extracted Biomarkers</h4>
          <div class="biomarkers-grid" id="biomarkers-container" data-aos="fade-up"></div>

          <h4 style="font-family:'Playfair Display',serif; font-size:22px; color:var(--forest); margin:30px 0 16px 0;">Medically Tailored Recommendations</h4>
          
          <div class="medical-meals-grid" id="meals-container">
            <!-- Meal Cards injected here -->
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Modal -->
  <div class="modal" id="etm-detail-modal">
    <div class="modal-container" style="max-width:900px; padding: 0;">
      <div class="modal-header" style="padding: 16px 24px; border-bottom: 1px solid var(--border);">
        <h3 id="etm-modal-title" style="font-family:'Playfair Display',serif; font-size:22px; color:var(--forest); margin:0;">Recipe Details</h3>
        <button class="modal-close" onclick="closeModal()" style="position:static; transform:none; background:none; border:none; font-size:24px; color:var(--muted); cursor:pointer;">&times;</button>
      </div>
      <div class="modal-body" id="etm-modal-body" style="padding: 24px; max-height: calc(100vh - 120px); overflow-y: auto;">
      </div>
    </div>
  </div>

  <script src="../assets/js/api.js?v=2"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  
  <script>
    AOS.init({ once: true, duration: 800 });
    let currentData = null;

    document.addEventListener('DOMContentLoaded', () => {
      loadLatestReport();
      setupDragDrop();
    });

    function setupDragDrop() {
      const dropZone = document.getElementById('drop-zone');
      ['dragenter', 'dragover'].forEach(ev => dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.add('dragover'); }));
      ['dragleave', 'drop'].forEach(ev => dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.remove('dragover'); }));
      dropZone.addEventListener('drop', e => {
        if (e.dataTransfer.files.length) uploadReportFile(e.dataTransfer.files[0]);
      });
    }

    async function loadLatestReport() {
      try {
        const data = await API.getLatestReport();
        if (data && data.has_report) {
          currentData = data;
          renderDashboard(data);
        } else {
          showUploadView();
        }
      } catch (err) {
        showUploadView();
      }
    }

    function showUploadView() {
      document.getElementById('upload-view').style.display = 'block';
      document.getElementById('dashboard-view').style.display = 'none';
      document.getElementById('pipeline-loader').style.display = 'none';
    }

    function handleFileSelect(e) {
      if (e.target.files.length) uploadReportFile(e.target.files[0]);
    }

    async function uploadReportFile(file) {
      document.getElementById('upload-view').style.display = 'none';
      document.getElementById('pipeline-loader').style.display = 'block';
      
      let step = 1;
      const t = setInterval(() => {
        if(step < 5) activateStep(++step);
      }, 1500);

      try {
        await API.uploadReport(file);
        clearInterval(t);
        activateStep(5);
        setTimeout(async () => {
          const data = await API.getLatestReport();
          currentData = data;
          renderDashboard(data);
        }, 1000);
      } catch (err) {
        clearInterval(t);
        alert('Failed: ' + err.message);
        showUploadView();
      }
    }

    function activateStep(num) {
      for (let i = 1; i <= 5; i++) {
        const el = document.getElementById('step-' + i);
        if (i < num) el.className = 'step-item completed';
        else if (i === num) el.className = 'step-item active';
        else el.className = 'step-item';
      }
    }

    function renderDashboard(data) {
      document.getElementById('pipeline-loader').style.display = 'none';
      document.getElementById('upload-view').style.display = 'none';
      document.getElementById('dashboard-view').style.display = 'block';
      document.getElementById('btn-upload-new').style.display = 'block';
      
      document.getElementById('txt-active-report-title').textContent = data.report.file_name;

      // Risks
      const rCont = document.getElementById('risk-alerts-container');
      rCont.innerHTML = '';
      if(data.health_risks) {
        data.health_risks.forEach(r => {
          rCont.innerHTML += `
            <div class="risk-alert-card animate__animated animate__fadeInLeft">
              <div style="font-weight:600; color:#8A3B00;">⚠️ ${r.condition} Detected (${r.risk_percentage}%)</div>
            </div>`;
        });
      }

      // Biomarkers
      const bCont = document.getElementById('biomarkers-container');
      bCont.innerHTML = '';
      Object.values(data.biomarkers).forEach(b => {
        bCont.innerHTML += `
          <div class="biomarker-card">
            <div class="biomarker-name">${b.display_name} <span class="biomarker-status status-${b.status.toLowerCase()}">${b.status}</span></div>
            <div class="biomarker-val">${b.value} <span class="biomarker-unit">${b.unit}</span></div>
          </div>`;
      });

      // Meals with GSAP integration
      const mCont = document.getElementById('meals-container');
      mCont.innerHTML = '';
      
      const slots = ['breakfast', 'lunch', 'dinner', 'snack'];
      slots.forEach((slot, idx) => {
        const meal = data.meal_plan[slot];
        if (meal && !meal.error && meal.name && !meal.name.includes("Safe ")) {
          // It's a populated personalized meal
          const card = document.createElement('div');
          card.className = 'meal-card-anim';
          card.setAttribute('data-aos', 'fade-up');
          card.setAttribute('data-aos-delay', (idx * 100).toString());
          
          let scoreHtml = '';
          if(meal.health_compatibility_score) {
             scoreHtml = `<div class="meal-card-score">⚕️ ${meal.health_compatibility_score}% Match</div>`;
          }

          let defaultImg = window.getFallbackImage ? window.getFallbackImage(meal.name) : 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800&q=80';
          
          card.innerHTML = `
            <div class="meal-card-img-wrapper">
              <img src="${meal.image_url || defaultImg}" onerror="this.onerror=null; this.src='${defaultImg}'" class="meal-card-img">
              ${scoreHtml}
              <div class="meal-hover-overlay" style="position:absolute; bottom:0; left:0; right:0; background:rgba(44, 76, 59, 0.85); color:white; padding:12px; font-size:12px; transform:translateY(100%); transition:transform 0.4s ease; text-align:center; backdrop-filter:blur(4px);">
                <strong>View Recipe Details</strong><br>
                <span style="font-size:10px; opacity:0.8;">Includes preparation steps & modified instructions</span>
              </div>
            </div>
            <div class="meal-card-body">
              <div class="meal-slot">${slot}</div>
              <div class="meal-name">${meal.name}</div>
              <div style="margin-bottom:10px;">
                <span class="meal-macros-pill">🔥 ${meal.nutrition?.calories || 0} kcal</span>
                <span class="meal-macros-pill">🍗 ${meal.nutrition?.protein || 0}g</span>
                <span class="meal-macros-pill">🥑 ${meal.nutrition?.fat || 0}g</span>
              </div>
            </div>
          `;
          
          // Hover 3D parallax effect and Overlay
          card.addEventListener('mouseenter', () => {
            const overlay = card.querySelector('.meal-hover-overlay');
            if(overlay) overlay.style.transform = 'translateY(0)';
          });
          card.addEventListener('mousemove', (e) => {
            let inst = meal.instructions || '';
            if (Array.isArray(inst)) inst = inst.join('\\n');
            if(typeof showRecipePopover === 'function') {
                showRecipePopover(e, meal.name, inst.replace(/'/g,"\\'").replace(/"/g, '&quot;'));
            }
            
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const xPct = x / rect.width - 0.5;
            const yPct = y / rect.height - 0.5;
            gsap.to(card, {
              rotationY: xPct * 10,
              rotationX: -yPct * 10,
              transformPerspective: 1000,
              ease: "power1.out",
              duration: 0.5
            });
          });
          card.addEventListener('mouseleave', () => {
            if(typeof hideRecipePopover === 'function') hideRecipePopover();
            gsap.to(card, { rotationY: 0, rotationX: 0, duration: 0.5, ease: "power1.out" });
            const overlay = card.querySelector('.meal-hover-overlay');
            if(overlay) overlay.style.transform = 'translateY(100%)';
          });
          
          card.addEventListener('click', () => openModal(slot, meal));
          mCont.appendChild(card);
        }
      });
      
      // Refresh AOS
      setTimeout(() => AOS.refresh(), 100);
    }

    function openModal(slot, meal) {
      localStorage.setItem('temp_recipe', JSON.stringify(meal));
      window.open('recipe.php?local=1', '_blank');
    }

    function closeModal() {
      // Deprecated
    }
  </script>
</body>
</html>
