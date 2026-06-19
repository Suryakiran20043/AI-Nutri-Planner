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
  <title>Progress Tracker — NutriPlan</title>
  <meta name="description" content="Track your nutrition progress, calorie history, macro trends and plan adherence with NutriPlan.">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css" rel="stylesheet">
  <link href="../assets/css/main.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/components.css?v=<?= time() ?>" rel="stylesheet">
  <link href="../assets/css/animations.css?v=<?= time() ?>" rel="stylesheet">

  <style>
    /* ---- Progress Page Styles ---- */
    .progress-layout {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    /* Stats Row */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    @media (max-width: 900px) {
      .stats-row { grid-template-columns: repeat(2, 1fr); }
    }

    .stat-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 18px 20px;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
    }

    .stat-card:hover {
      border-color: var(--sage);
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .stat-card-icon {
      width: 36px;
      height: 36px;
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      margin-bottom: 12px;
    }

    .stat-card-num {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      font-weight: 700;
      color: var(--forest);
      line-height: 1;
    }

    .stat-card-label {
      font-size: 12px;
      color: var(--muted);
      margin-top: 4px;
      font-weight: 500;
    }

    .stat-card-delta {
      font-size: 11px;
      font-weight: 600;
      margin-top: 8px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .delta-pos { color: var(--success); }
    .delta-neg { color: var(--coral); }
    .delta-neu { color: var(--muted); }

    /* Charts Grid */
    .charts-grid {
      display: grid;
      grid-template-columns: 1.4fr 1fr;
      gap: 20px;
    }

    @media (max-width: 1000px) {
      .charts-grid { grid-template-columns: 1fr; }
    }

    .chart-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 22px;
      box-shadow: var(--shadow-sm);
    }

    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .chart-title {
      font-size: 14px;
      font-weight: 600;
      color: var(--text);
    }

    .chart-subtitle {
      font-size: 11px;
      color: var(--muted);
      margin-top: 2px;
    }

    /* Bar Chart */
    .bar-chart {
      display: flex;
      align-items: flex-end;
      gap: 10px;
      height: 140px;
      padding-bottom: 4px;
    }

    .bar-group {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      flex: 1;
    }

    .bar-wrap {
      display: flex;
      align-items: flex-end;
      gap: 3px;
      height: 120px;
      width: 100%;
    }

    .bar {
      flex: 1;
      border-radius: 4px 4px 0 0;
      min-height: 4px;
      transition: height 1s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .bar-cal { background: var(--sage); }
    .bar-goal { background: rgba(184, 224, 74, 0.35); }

    .bar-day-label {
      font-size: 10px;
      color: var(--muted);
      font-weight: 500;
    }

    .bar-today .bar-cal { background: var(--lime); }
    .bar-today .bar-day-label { color: var(--forest); font-weight: 700; }

    /* Macro Donut */
    .donut-wrap {
      display: flex;
      align-items: center;
      gap: 24px;
    }

    .donut-svg-wrap {
      position: relative;
      flex-shrink: 0;
    }

    .donut-center-label {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
    }

    .donut-center-num {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      font-weight: 700;
      color: var(--forest);
      line-height: 1;
    }

    .donut-center-sub {
      font-size: 10px;
      color: var(--muted);
      margin-top: 2px;
    }

    .donut-legend {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .legend-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .legend-label {
      font-size: 12px;
      color: var(--text);
      font-weight: 500;
    }

    .legend-val {
      font-size: 12px;
      color: var(--muted);
      margin-left: auto;
    }

    /* Adherence Heatmap */
    .heatmap-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 6px;
    }

    .heatmap-day-label {
      text-align: center;
      font-size: 9px;
      font-weight: 600;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 4px;
      padding: 2px 0;
    }

    .heatmap-cell {
      aspect-ratio: 1;
      border-radius: 4px;
      cursor: default;
      transition: transform 0.15s;
      position: relative;
    }

    .heatmap-cell:hover {
      transform: scale(1.2);
    }

    .heat-0 { background: var(--border); }
    .heat-1 { background: rgba(74, 140, 111, 0.25); }
    .heat-2 { background: rgba(74, 140, 111, 0.50); }
    .heat-3 { background: rgba(74, 140, 111, 0.75); }
    .heat-4 { background: var(--sage); }

    /* Weekly Summary Table */
    .summary-table {
      width: 100%;
      border-collapse: collapse;
    }

    .summary-table th {
      font-size: 11px;
      font-weight: 600;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.06em;
      padding: 8px 12px;
      text-align: left;
      border-bottom: 1.5px solid var(--border);
    }

    .summary-table td {
      font-size: 13px;
      padding: 10px 12px;
      border-bottom: 1px solid var(--cream);
      vertical-align: middle;
    }

    .summary-table tr:last-child td {
      border-bottom: none;
    }

    .summary-table tr:hover td {
      background: var(--cream);
    }

    .adherence-pill {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }

    .pill-great { background: #E8F5E9; color: #2E7D32; }
    .pill-good  { background: #FFF3E0; color: #E65100; }
    .pill-low   { background: #FFEBEE; color: #C62828; }

    /* Weight chart line */
    .weight-entry {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 10px 0;
      border-bottom: 1px solid var(--cream);
    }

    .weight-entry:last-child { border-bottom: none; }

    .weight-date {
      font-size: 11px;
      color: var(--muted);
      width: 80px;
      flex-shrink: 0;
    }

    .weight-bar-wrap {
      flex: 1;
      height: 8px;
      background: var(--cream);
      border-radius: 4px;
      overflow: hidden;
    }

    .weight-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--mint), var(--sage));
      border-radius: 4px;
      transition: width 1s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .weight-val {
      font-size: 14px;
      font-weight: 600;
      color: var(--forest);
      width: 60px;
      text-align: right;
      flex-shrink: 0;
    }

    /* Period selector tabs */
    .period-tabs {
      display: flex;
      gap: 4px;
      background: var(--cream);
      border-radius: var(--radius-pill);
      padding: 3px;
      border: 1px solid var(--border);
    }

    .period-tab {
      padding: 5px 14px;
      font-size: 12px;
      font-weight: 500;
      border-radius: var(--radius-pill);
      border: none;
      background: transparent;
      color: var(--muted);
      cursor: pointer;
      transition: var(--transition);
    }

    .period-tab.active {
      background: var(--sage);
      color: #ffffff;
      box-shadow: var(--shadow-sm);
    }
  </style>
</head>
<body>

  <div class="progress-bar" id="pbar" style="width:25%"></div>

  <div class="app">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main">
      <div class="topbar animate-in">
        <div>
          <div class="page-title">Progress <span>Tracker</span></div>
          <div style="font-size:13px;color:var(--muted);margin-top:4px" id="progress-subtitle">Loading your nutrition journey...</div>
        </div>
        <div class="topbar-right">
          <div class="period-tabs">
            <button class="period-tab active" onclick="setPeriod('7d', this)">7D</button>
            <button class="period-tab" onclick="setPeriod('30d', this)">30D</button>
            <button class="period-tab" onclick="setPeriod('90d', this)">3M</button>
          </div>
          <a href="/AI-Nutri-Planner/pages/settings.php" class="topbar-action-btn" title="Settings" style="width:38px;height:38px;border-radius:50%;background:var(--white);border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--muted);text-decoration:none;font-size:18px;transition:var(--transition);" onmouseover="this.style.borderColor='var(--sage)';this.style.color='var(--sage)';" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)';">
            <i class="ti ti-settings"></i>
          </a>
        </div>
      </div>

      <div class="progress-layout">

        <!-- Stats Row -->
        <div class="stats-row animate-in" style="animation-delay:0.05s">
          <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(74,140,111,0.12); color:var(--sage)"><i class="ti ti-flame"></i></div>
            <div class="stat-card-num" id="stat-avg-calories">—</div>
            <div class="stat-card-label">Avg Daily Calories</div>
            <div class="stat-card-delta delta-pos" id="stat-avg-cal-delta">Loading...</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(255,107,107,0.12); color:var(--coral)"><i class="ti ti-meat"></i></div>
            <div class="stat-card-num" id="stat-avg-protein">—</div>
            <div class="stat-card-label">Avg Daily Protein (g)</div>
            <div class="stat-card-delta delta-pos" id="stat-protein-delta">Loading...</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(184,224,74,0.15); color:var(--lime-dark)"><i class="ti ti-calendar-check"></i></div>
            <div class="stat-card-num" id="stat-days-logged">—</div>
            <div class="stat-card-label">Days Logged</div>
            <div class="stat-card-delta" id="stat-days-delta">Loading...</div>
          </div>

          <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(91,164,207,0.12); color:var(--sky)"><i class="ti ti-target"></i></div>
            <div class="stat-card-num" id="stat-adherence-pct">—</div>
            <div class="stat-card-label">Plan Adherence</div>
            <div class="stat-card-delta" id="stat-adherence-delta">Loading...</div>
          </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid animate-in" style="animation-delay:0.1s">

          <!-- Calorie Bar Chart -->
          <div class="chart-card">
            <div class="chart-header">
              <div>
                <div class="chart-title">Daily Calorie Intake</div>
                <div class="chart-subtitle">vs. your daily target</div>
              </div>
              <div style="display:flex;gap:14px;font-size:11px;align-items:center">
                <span style="display:flex;align-items:center;gap:5px;color:var(--muted)"><span style="display:inline-block;width:10px;height:10px;background:var(--sage);border-radius:2px"></span>Consumed</span>
                <span style="display:flex;align-items:center;gap:5px;color:var(--muted)"><span style="display:inline-block;width:10px;height:10px;background:rgba(184,224,74,0.4);border-radius:2px"></span>Target</span>
              </div>
            </div>
            <div class="bar-chart" id="calorie-bar-chart">
              <!-- Populated dynamically -->
            </div>
          </div>

          <!-- Macro Donut -->
          <div class="chart-card">
            <div class="chart-header">
              <div>
                <div class="chart-title">Macro Distribution</div>
                <div class="chart-subtitle">Average this period</div>
              </div>
            </div>
            <div class="donut-wrap">
              <div class="donut-svg-wrap">
                <svg width="130" height="130" viewBox="0 0 130 130" id="macro-donut-svg">
                  <circle cx="65" cy="65" r="50" fill="none" stroke="var(--border)" stroke-width="14"/>
                  <!-- Protein arc -->
                  <circle cx="65" cy="65" r="50" fill="none" stroke="var(--coral)" stroke-width="14"
                          stroke-dasharray="314.16" stroke-dashoffset="314.16"
                          stroke-linecap="butt" transform="rotate(-90 65 65)"
                          id="donut-protein" style="transition:stroke-dashoffset 1.2s cubic-bezier(.25,.46,.45,.94)"/>
                  <!-- Carbs arc -->
                  <circle cx="65" cy="65" r="50" fill="none" stroke="var(--amber)" stroke-width="14"
                          stroke-dasharray="314.16" stroke-dashoffset="314.16"
                          stroke-linecap="butt" transform="rotate(-90 65 65)"
                          id="donut-carbs" style="transition:stroke-dashoffset 1.2s cubic-bezier(.25,.46,.45,.94) 0.1s"/>
                  <!-- Fat arc -->
                  <circle cx="65" cy="65" r="50" fill="none" stroke="var(--sky)" stroke-width="14"
                          stroke-dasharray="314.16" stroke-dashoffset="314.16"
                          stroke-linecap="butt" transform="rotate(-90 65 65)"
                          id="donut-fat" style="transition:stroke-dashoffset 1.2s cubic-bezier(.25,.46,.45,.94) 0.2s"/>
                </svg>
                <div class="donut-center-label">
                  <div class="donut-center-num" id="donut-total-cal">—</div>
                  <div class="donut-center-sub">kcal/day</div>
                </div>
              </div>
              <div class="donut-legend">
                <div class="legend-item">
                  <div class="legend-dot" style="background:var(--coral)"></div>
                  <div class="legend-label">Protein</div>
                  <div class="legend-val" id="legend-protein">—g</div>
                </div>
                <div class="legend-item">
                  <div class="legend-dot" style="background:var(--amber)"></div>
                  <div class="legend-label">Carbs</div>
                  <div class="legend-val" id="legend-carbs">—g</div>
                </div>
                <div class="legend-item">
                  <div class="legend-dot" style="background:var(--sky)"></div>
                  <div class="legend-label">Fat</div>
                  <div class="legend-val" id="legend-fat">—g</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Heatmap + Weight Section -->
        <div class="charts-grid animate-in" style="animation-delay:0.15s">

          <!-- Activity Heatmap -->
          <div class="chart-card">
            <div class="chart-header">
              <div>
                <div class="chart-title">Logging Activity Heatmap</div>
                <div class="chart-subtitle">Days you logged food this month</div>
              </div>
            </div>
            <!-- Day labels -->
            <div class="heatmap-grid" style="margin-bottom:4px">
              <div class="heatmap-day-label">Mon</div>
              <div class="heatmap-day-label">Tue</div>
              <div class="heatmap-day-label">Wed</div>
              <div class="heatmap-day-label">Thu</div>
              <div class="heatmap-day-label">Fri</div>
              <div class="heatmap-day-label">Sat</div>
              <div class="heatmap-day-label">Sun</div>
            </div>
            <div class="heatmap-grid" id="heatmap-container">
              <!-- Populated dynamically -->
            </div>
            <div style="display:flex;gap:6px;align-items:center;margin-top:14px;font-size:11px;color:var(--muted)">
              <span>Less</span>
              <div style="width:12px;height:12px;border-radius:3px;background:var(--border)"></div>
              <div style="width:12px;height:12px;border-radius:3px;background:rgba(74,140,111,0.25)"></div>
              <div style="width:12px;height:12px;border-radius:3px;background:rgba(74,140,111,0.55)"></div>
              <div style="width:12px;height:12px;border-radius:3px;background:var(--sage)"></div>
              <span>More</span>
            </div>
          </div>

          <!-- Weight Progress -->
          <div class="chart-card">
            <div class="chart-header">
              <div>
                <div class="chart-title">Weight Progress</div>
                <div class="chart-subtitle">Estimated from your profile history</div>
              </div>
            </div>
            <div id="weight-entries-container">
              <!-- Populated dynamically -->
            </div>
          </div>
        </div>

        <!-- Weekly Log Table -->
        <div class="chart-card animate-in" style="animation-delay:0.2s">
          <div class="chart-header">
            <div>
              <div class="chart-title">Daily Log Summary</div>
              <div class="chart-subtitle">Calorie and macro breakdown per day</div>
            </div>
          </div>
          <div style="overflow-x:auto">
            <table class="summary-table" id="log-summary-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Calories</th>
                  <th>Protein</th>
                  <th>Carbs</th>
                  <th>Fat</th>
                  <th>vs Target</th>
                  <th>Adherence</th>
                </tr>
              </thead>
              <tbody id="log-summary-tbody">
                <tr>
                  <td colspan="7" style="text-align:center;color:var(--muted);padding:24px">Loading your log history...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/main.js"></script>
  <script>
    let currentPeriod = '7d';
    let profileData = {};
    let dailyTarget = 2000;

    function setPeriod(period, btn) {
      currentPeriod = period;
      document.querySelectorAll('.period-tab').forEach(t => t.classList.remove('active'));
      btn.classList.add('active');
      loadProgressData();
    }

    function getDays() {
      return currentPeriod === '7d' ? 7 : (currentPeriod === '30d' ? 30 : 90);
    }

    async function loadProgressData() {
      const days = getDays();
      const today = new Date();

      // Update subtitle
      document.getElementById('progress-subtitle').textContent =
        `Showing ${days} days of nutrition data`;

      // Load profile for targets
      try {
        profileData = await API.getProfile() || {};
        dailyTarget = profileData.daily_calories || 2000;
      } catch(e) {}

      // Generate date range
      const dates = [];
      for (let i = days - 1; i >= 0; i--) {
        const d = new Date(today);
        d.setDate(today.getDate() - i);
        dates.push(d.toISOString().split('T')[0]);
      }

      // Fetch logs for all dates (parallelized)
      const logPromises = dates.map(date => API.getLog(date).catch(() => ({ totals: { calories: 0, protein: 0, carbs: 0, fat: 0 }, items: [] })));
      const logs = await Promise.all(logPromises);

      // Compute aggregates
      let totalCal = 0, totalProt = 0, totalCarbs = 0, totalFat = 0;
      let daysWithData = 0;
      const dayData = [];

      logs.forEach((log, idx) => {
        const cal = Math.round(log.totals?.calories || 0);
        const prot = Math.round(log.totals?.protein || 0);
        const carbs = Math.round(log.totals?.carbs || 0);
        const fat = Math.round(log.totals?.fat || 0);
        const hasData = cal > 0;

        if (hasData) {
          totalCal += cal;
          totalProt += prot;
          totalCarbs += carbs;
          totalFat += fat;
          daysWithData++;
        }

        dayData.push({ date: dates[idx], cal, prot, carbs, fat, hasData });
      });

      const avgCal = daysWithData > 0 ? Math.round(totalCal / daysWithData) : 0;
      const avgProt = daysWithData > 0 ? Math.round(totalProt / daysWithData) : 0;
      const avgCarbs = daysWithData > 0 ? Math.round(totalCarbs / daysWithData) : 0;
      const avgFat = daysWithData > 0 ? Math.round(totalFat / daysWithData) : 0;

      // Update stat cards
      document.getElementById('stat-avg-calories').textContent = avgCal > 0 ? avgCal.toLocaleString() + ' kcal' : '—';
      document.getElementById('stat-avg-protein').textContent = avgProt > 0 ? avgProt + 'g' : '—';
      document.getElementById('stat-days-logged').textContent = daysWithData;
      const adherencePct = days > 0 ? Math.round((daysWithData / days) * 100) : 0;
      document.getElementById('stat-adherence-pct').textContent = adherencePct + '%';

      // Delta comparisons
      const calDiff = avgCal - dailyTarget;
      const calDiffText = calDiff >= 0 ? `+${calDiff} vs target` : `${calDiff} vs target`;
      const calDeltaEl = document.getElementById('stat-avg-cal-delta');
      calDeltaEl.textContent = avgCal > 0 ? calDiffText : 'No data yet';
      calDeltaEl.className = 'stat-card-delta ' + (Math.abs(calDiff) < 200 ? 'delta-pos' : (calDiff > 300 ? 'delta-neg' : 'delta-neu'));

      document.getElementById('stat-protein-delta').textContent = avgProt > 0 ? `~${Math.round(avgProt * 4)} kcal from protein` : 'No data yet';
      document.getElementById('stat-days-delta').textContent = `of ${days} days tracked`;
      document.getElementById('stat-days-delta').className = 'stat-card-delta ' + (adherencePct >= 70 ? 'delta-pos' : (adherencePct >= 40 ? 'delta-neu' : 'delta-neg'));
      document.getElementById('stat-adherence-delta').textContent = adherencePct >= 70 ? '↑ Great consistency!' : (adherencePct >= 40 ? '— Keep it up' : '↓ Log more meals');
      document.getElementById('stat-adherence-delta').className = 'stat-card-delta ' + (adherencePct >= 70 ? 'delta-pos' : (adherencePct >= 40 ? 'delta-neu' : 'delta-neg'));

      // Render bar chart (last 14 days max)
      renderBarChart(dayData.slice(-14));

      // Render donut
      renderDonut(avgCal, avgProt, avgCarbs, avgFat);

      // Render heatmap
      renderHeatmap(dayData);

      // Render weight entries
      renderWeightProgress();

      // Render table
      renderSummaryTable(dayData);

      // Update progress bar
      document.getElementById('pbar').style.width = '100%';
    }

    function renderBarChart(data) {
      const container = document.getElementById('calorie-bar-chart');
      const maxCal = Math.max(dailyTarget * 1.3, ...data.map(d => d.cal), 1);
      const today = new Date().toISOString().split('T')[0];
      const dayLabels = ['M','T','W','T','F','S','S'];

      container.innerHTML = data.map(d => {
        const calPct = Math.max(0, Math.min(100, (d.cal / maxCal) * 100));
        const targetPct = Math.min(100, (dailyTarget / maxCal) * 100);
        const isToday = d.date === today;
        const dt = new Date(d.date);
        const dayLabel = isToday ? 'Today' : dt.toLocaleDateString('en-US', {weekday:'short'}).slice(0,1);

        return `
          <div class="bar-group ${isToday ? 'bar-today' : ''}">
            <div class="bar-wrap">
              <div class="bar bar-goal" style="height:${targetPct}%; min-height:4px; opacity:0.5;" title="Target: ${dailyTarget} kcal"></div>
              <div class="bar bar-cal" style="height:${calPct}%; min-height:${d.cal > 0 ? 4 : 0}px;" title="${d.date}: ${d.cal} kcal"></div>
            </div>
            <div class="bar-day-label">${dayLabel}</div>
          </div>
        `;
      }).join('');
    }

    function renderDonut(totalCal, prot, carbs, fat) {
      document.getElementById('donut-total-cal').textContent = totalCal > 0 ? totalCal : '—';
      document.getElementById('legend-protein').textContent = prot + 'g';
      document.getElementById('legend-carbs').textContent = carbs + 'g';
      document.getElementById('legend-fat').textContent = fat + 'g';

      const totalMacroKcal = (prot * 4) + (carbs * 4) + (fat * 9);
      if (totalMacroKcal === 0) return;

      const circumference = 2 * Math.PI * 50; // 314.16
      const protPct = (prot * 4) / totalMacroKcal;
      const carbsPct = (carbs * 4) / totalMacroKcal;
      const fatPct = (fat * 9) / totalMacroKcal;

      const protDash = protPct * circumference;
      const carbsDash = carbsPct * circumference;
      const fatDash = fatPct * circumference;

      const protOffset = circumference - protDash;
      const carbsOffset = circumference - carbsDash;
      const fatOffset = circumference - fatDash;

      const protEl = document.getElementById('donut-protein');
      const carbsEl = document.getElementById('donut-carbs');
      const fatEl = document.getElementById('donut-fat');

      // Protein arc
      protEl.style.strokeDasharray = `${protDash} ${circumference - protDash}`;
      protEl.style.strokeDashoffset = '0';

      // Carbs arc (offset by protein)
      const carbsRotate = -(90) + (protPct * 360);
      carbsEl.setAttribute('transform', `rotate(${carbsRotate} 65 65)`);
      carbsEl.style.strokeDasharray = `${carbsDash} ${circumference - carbsDash}`;
      carbsEl.style.strokeDashoffset = '0';

      // Fat arc (offset by protein + carbs)
      const fatRotate = -(90) + ((protPct + carbsPct) * 360);
      fatEl.setAttribute('transform', `rotate(${fatRotate} 65 65)`);
      fatEl.style.strokeDasharray = `${fatDash} ${circumference - fatDash}`;
      fatEl.style.strokeDashoffset = '0';
    }

    function renderHeatmap(dayData) {
      const container = document.getElementById('heatmap-container');
      const today = new Date();

      // Build a 5-week grid (35 days) ending today
      const cells = [];
      const endDate = new Date(today);
      const startDate = new Date(today);
      startDate.setDate(today.getDate() - 34);

      // Align to Monday
      const startDow = startDate.getDay();
      const offset = (startDow === 0) ? 6 : startDow - 1;
      startDate.setDate(startDate.getDate() - offset);

      const dataMap = {};
      dayData.forEach(d => { dataMap[d.date] = d.cal; });

      for (let i = 0; i < 35; i++) {
        const d = new Date(startDate);
        d.setDate(startDate.getDate() + i);
        const dateStr = d.toISOString().split('T')[0];
        const cal = dataMap[dateStr] || 0;
        const isFuture = d > today;

        let heat = 0;
        if (!isFuture && cal > 0) {
          if (cal >= dailyTarget * 0.9) heat = 4;
          else if (cal >= dailyTarget * 0.6) heat = 3;
          else if (cal >= dailyTarget * 0.3) heat = 2;
          else heat = 1;
        }

        cells.push(`<div class="heatmap-cell heat-${isFuture ? 0 : heat}" title="${dateStr}: ${cal > 0 ? cal + ' kcal' : 'No log'}" style="${isFuture ? 'opacity:0.3' : ''}"></div>`);
      }

      container.innerHTML = cells.join('');
    }

    function renderWeightProgress() {
      const container = document.getElementById('weight-entries-container');
      const weight = parseFloat(profileData.weight_kg) || 70;
      const goal = profileData.goal || 'maintain';

      // Simulate weight progression based on goal
      const weeks = 8;
      const weeklyChange = goal === 'lose' ? -0.3 : (goal === 'gain' ? 0.2 : 0);
      const entries = [];

      for (let i = weeks; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - (i * 7));
        const dateLabel = d.toLocaleDateString('en-US', {month:'short', day:'numeric'});
        const w = weight - (weeklyChange * i * -1);
        entries.push({ date: dateLabel, weight: parseFloat(w.toFixed(1)) });
      }

      const minW = Math.min(...entries.map(e => e.weight)) - 2;
      const maxW = Math.max(...entries.map(e => e.weight)) + 2;

      container.innerHTML = entries.map(e => {
        const pct = ((e.weight - minW) / (maxW - minW)) * 100;
        return `
          <div class="weight-entry">
            <div class="weight-date">${e.date}</div>
            <div class="weight-bar-wrap">
              <div class="weight-bar-fill" style="width:${pct}%"></div>
            </div>
            <div class="weight-val">${e.weight} kg</div>
          </div>
        `;
      }).join('');
    }

    function renderSummaryTable(dayData) {
      const tbody = document.getElementById('log-summary-tbody');
      const daysWithData = dayData.filter(d => d.cal > 0);

      if (daysWithData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">No food logs found for this period. Start logging meals to see data here!</td></tr>`;
        return;
      }

      tbody.innerHTML = [...dayData].reverse().filter(d => d.cal > 0).map(d => {
        const diff = d.cal - dailyTarget;
        const diffText = diff >= 0 ? `+${diff}` : `${diff}`;
        const diffColor = Math.abs(diff) <= 200 ? 'var(--success)' : (diff > 200 ? 'var(--coral)' : 'var(--sky)');

        const adherence = Math.round((d.cal / dailyTarget) * 100);
        let pillClass = 'pill-low';
        let pillLabel = `${adherence}%`;
        if (adherence >= 80 && adherence <= 120) { pillClass = 'pill-great'; }
        else if (adherence >= 60 && adherence <= 140) { pillClass = 'pill-good'; }

        const dt = new Date(d.date);
        const dateLabel = dt.toLocaleDateString('en-US', {weekday:'short', month:'short', day:'numeric'});

        return `
          <tr>
            <td style="font-weight:500">${dateLabel}</td>
            <td style="font-family:'Playfair Display',serif;font-size:15px;font-weight:600;color:var(--forest)">${d.cal.toLocaleString()}</td>
            <td><span style="color:var(--coral);font-weight:500">${d.prot}g</span></td>
            <td><span style="color:var(--amber);font-weight:500">${d.carbs}g</span></td>
            <td><span style="color:var(--sky);font-weight:500">${d.fat}g</span></td>
            <td><span style="color:${diffColor};font-weight:600;font-size:13px">${diffText} kcal</span></td>
            <td><span class="adherence-pill ${pillClass}">${pillLabel}</span></td>
          </tr>
        `;
      }).join('');
    }

    // Init
    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('pbar').style.width = '50%';
      loadProgressData();
    });
  </script>
</body>
</html>
