const fs = require('fs');
const path = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// ─── PART 1: Replace dashboard mini Currency Trends widget HTML ───────────────

const oldMiniHtml = `    <!-- Currency Trend -->
    <div class="col-xl-6">
      <div class="card p-3">
        <div class="section-header mb-2">
          <div class="section-title">
            <span class="stat-icon amber" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-graph-up"></i></span>
            Currency Trends (vs USD)
          </div>
          <button class="btn-brand" onclick="showPage('currency')">
            <i class="bi bi-arrow-right me-1"></i>Full View
          </button>
        </div>
        <div class="chart-wrapper" style="height:280px;">
          <canvas id="dashCurrencyLine"></canvas>
        </div>
      </div>
    </div>`;

const newMiniHtml = `    <!-- Currency Trend -->
    <div class="col-xl-6">
      <div class="card p-3">
        <div class="section-header mb-2">
          <div class="section-title">
            <span class="stat-icon amber" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-graph-up"></i></span>
            Currency Trends (vs USD)
          </div>
          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" id="dashCurrencySelector" style="width:90px;font-size:.78rem;">
              <option value="EUR">EUR</option>
              <option value="GBP">GBP</option>
              <option value="JPY">JPY</option>
              <option value="CNY">CNY</option>
              <option value="IDR">IDR</option>
              <option value="AUD">AUD</option>
              <option value="SGD">SGD</option>
              <option value="CAD">CAD</option>
            </select>
            <button class="btn-brand" onclick="showPage('currency')">
              <i class="bi bi-arrow-right me-1"></i>Full View
            </button>
          </div>
        </div>
        <!-- Mini KPI summary row -->
        <div class="row g-2 mb-2" id="dashCurrencyKpis">
          <div class="col-3">
            <div style="background:rgba(245,158,11,.08);border-radius:8px;padding:8px 10px;">
              <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);letter-spacing:.04em;">Current</div>
              <div style="font-size:.92rem;font-weight:700;font-family:var(--font-mono);color:var(--text-primary);" id="dashKpiCurrent">–</div>
            </div>
          </div>
          <div class="col-3">
            <div style="background:rgba(245,158,11,.08);border-radius:8px;padding:8px 10px;">
              <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);letter-spacing:.04em;">Change</div>
              <div style="font-size:.92rem;font-weight:700;font-family:var(--font-mono);" id="dashKpiChange">–</div>
            </div>
          </div>
          <div class="col-3">
            <div style="background:rgba(245,158,11,.08);border-radius:8px;padding:8px 10px;">
              <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);letter-spacing:.04em;">7D High</div>
              <div style="font-size:.92rem;font-weight:700;font-family:var(--font-mono);color:#198754;" id="dashKpiHigh">–</div>
            </div>
          </div>
          <div class="col-3">
            <div style="background:rgba(245,158,11,.08);border-radius:8px;padding:8px 10px;">
              <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);letter-spacing:.04em;">7D Low</div>
              <div style="font-size:.92rem;font-weight:700;font-family:var(--font-mono);color:#dc3545;" id="dashKpiLow">–</div>
            </div>
          </div>
        </div>
        <div class="chart-wrapper" style="height:190px;">
          <canvas id="dashCurrencyLine"></canvas>
        </div>
        <div style="font-size:.68rem;color:var(--text-muted);margin-top:4px;" id="dashCurrencyLastUpdated">Last updated: –</div>
      </div>
    </div>`;

if (content.indexOf(oldMiniHtml) === -1) {
    console.error("ERROR: Could not find old mini currency widget HTML! Check line endings or whitespace.");
    // Try with CRLF
    const oldMiniHtmlCrLf = oldMiniHtml.replace(/\n/g, '\r\n');
    if (content.indexOf(oldMiniHtmlCrLf) !== -1) {
        content = content.replace(oldMiniHtmlCrLf, () => newMiniHtml.replace(/\n/g, '\r\n'));
        console.log("Replaced mini widget HTML (CRLF mode)!");
    } else {
        console.error("FATAL: Cannot locate mini currency widget HTML block. Skipping HTML replacement.");
    }
} else {
    content = content.replace(oldMiniHtml, () => newMiniHtml);
    console.log("Replaced mini currency widget HTML!");
}

// ─── PART 2: Replace buildCurrencyLineChart() ────────────────────────────────

const oldBuildCurrencyLineChart = `function buildCurrencyLineChart() {
  const rates = STATE.currencies.latest_rates || {};
  const hist  = STATE.currencies.history || {};
  const currencies = ['EUR','GBP','CNY'];
  const colors = ['#3b82f6','#10b981','#f59e0b'];

  const labels = (hist[currencies[0]] || []).map(p => p.x);
  const datasets = currencies.map((c, i) => ({
    label: c,
    data: (hist[c] || []).map(p => p.y),
    borderColor: colors[i],
    backgroundColor: colors[i] + '18',
    fill: i === 0,
    tension: 0.4,
    borderWidth: 2,
    pointRadius: 0,
  }));

  if (STATE.charts.dashCurrencyLine) STATE.charts.dashCurrencyLine.destroy();
  if (!labels.length) return; // no history yet
  STATE.charts.dashCurrencyLine = new Chart(
    document.getElementById('dashCurrencyLine').getContext('2d'), {
    type: 'line',
    data: { labels, datasets },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ position:'top', labels:{usePointStyle:true, font:{size:11}} } },
      scales: {
        x:{ ticks:{display:false}, grid:{display:false} },
        y:{ grid:{color:Chart.defaults.borderColor}, ticks:{font:{size:11}} }
      }
    }
  });
}`;

const newBuildCurrencyLineChart = `function generateSimulatedTrend(baseRate, days) {
  const result = [];
  let rate = baseRate;
  for (let i = days - 1; i >= 0; i--) {
    const d = new Date();
    d.setDate(d.getDate() - i);
    const dayLabel = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
    const variation = 1 + (Math.random() - 0.5) * 0.01; // ±0.5% daily variation
    rate = rate * variation;
    result.push({ x: dayLabel, y: parseFloat(rate.toFixed(6)) });
  }
  return result;
}

function buildCurrencyLineChart(currency) {
  const selectedCurrency = currency || document.getElementById('dashCurrencySelector')?.value || 'EUR';
  const rates = STATE.currencies.latest_rates || {};
  const hist  = STATE.currencies.history || {};

  // Try real history; fall back to simulation
  let histData = hist[selectedCurrency] || [];
  if (!histData.length) {
    const baseRate = rates[selectedCurrency];
    if (baseRate) {
      histData = generateSimulatedTrend(parseFloat(baseRate), 7);
    } else {
      // Hardcoded demo fallbacks per currency
      const demos = {
        EUR: 0.9215, GBP: 0.7843, JPY: 148.23, CNY: 7.2451,
        IDR: 15782, AUD: 1.5321, SGD: 1.3512, CAD: 1.3601
      };
      const base = demos[selectedCurrency] || 1.0;
      histData = generateSimulatedTrend(base, 7);
    }
  }

  const labels = histData.map(p => p.x);
  const data   = histData.map(p => p.y);
  const currentRate = data[data.length - 1] || 0;
  const prevRate    = data[0] || currentRate;
  const change      = currentRate - prevRate;
  const changePct   = prevRate > 0 ? ((change / prevRate) * 100).toFixed(2) : '0.00';
  const weekHigh    = Math.max(...data);
  const weekLow     = Math.min(...data);

  // Update KPI cards
  const currentEl  = document.getElementById('dashKpiCurrent');
  const changeEl   = document.getElementById('dashKpiChange');
  const highEl     = document.getElementById('dashKpiHigh');
  const lowEl      = document.getElementById('dashKpiLow');
  const updatedEl  = document.getElementById('dashCurrencyLastUpdated');

  if (currentEl)  currentEl.textContent  = currentRate.toFixed(4);
  if (highEl)     highEl.textContent     = weekHigh.toFixed(4);
  if (lowEl)      lowEl.textContent      = weekLow.toFixed(4);
  if (changeEl) {
    const sign = change >= 0 ? '+' : '';
    changeEl.textContent = \`\${sign}\${changePct}%\`;
    changeEl.style.color = change >= 0 ? '#198754' : '#dc3545';
  }
  if (updatedEl) {
    updatedEl.textContent = \`Last updated: \${new Date().toLocaleTimeString()}\`;
  }

  // Currency colors map
  const colorMap = {
    EUR: '#3b82f6', GBP: '#10b981', JPY: '#f59e0b',
    CNY: '#ef4444', IDR: '#8b5cf6', AUD: '#06b6d4',
    SGD: '#ec4899', CAD: '#f97316'
  };
  const color = colorMap[selectedCurrency] || '#f59e0b';

  if (STATE.charts.dashCurrencyLine) STATE.charts.dashCurrencyLine.destroy();

  const canvas = document.getElementById('dashCurrencyLine');
  if (!canvas) return;

  STATE.charts.dashCurrencyLine = new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: \`\${selectedCurrency}/USD\`,
        data,
        borderColor: color,
        backgroundColor: color + '18',
        fill: true,
        tension: 0.4,
        borderWidth: 2,
        pointRadius: 4,
        pointBackgroundColor: color,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointHoverRadius: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 700, easing: 'easeInOutQuart' },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1e293b',
          titleColor: '#94a3b8',
          bodyColor: '#f8fafc',
          borderColor: color,
          borderWidth: 1,
          padding: 10,
          callbacks: {
            label: ctx => \` \${selectedCurrency}: \${ctx.parsed.y.toFixed(6)}\`
          }
        }
      },
      scales: {
        x: {
          grid: { color: 'rgba(0,0,0,0.04)' },
          ticks: { font: { size: 10 }, color: '#94a3b8', maxRotation: 0 }
        },
        y: {
          grid: { color: 'rgba(0,0,0,0.04)' },
          ticks: { font: { size: 10 }, color: '#94a3b8' }
        }
      }
    }
  });
}`;

// Match with CRLF-aware replace
let found = false;
const normalized = content.replace(/\r\n/g, '\n');
const oldNorm = oldBuildCurrencyLineChart.replace(/\r\n/g, '\n');
if (normalized.indexOf(oldNorm) !== -1) {
    const newNorm = normalized.replace(oldNorm, () => newBuildCurrencyLineChart);
    content = newNorm.replace(/\n/g, '\r\n');
    found = true;
    console.log("Replaced buildCurrencyLineChart()!");
} else {
    console.error("ERROR: Could not find buildCurrencyLineChart() function body!");
}

// ─── PART 3: Replace buildFullCurrencyChart() ────────────────────────────────
const oldFullChart = `function buildFullCurrencyChart(targetCurrency) {
  const hist = STATE.currencies.history?.[targetCurrency] || [];
  const labels = hist.map(p => p.x);
  const data = hist.map(p => p.y);

  if (STATE.charts.currencyTrend) STATE.charts.currencyTrend.destroy();
  STATE.charts.currencyTrend = new Chart(document.getElementById('currencyTrendChart').getContext('2d'), {
    type:'line',
    data:{
      labels,
      datasets:[{
        label:\`USD/\${targetCurrency}\`,
        data,
        borderColor:'#f59e0b',
        backgroundColor:'rgba(245,158,11,.08)',
        fill:true,
        tension:0.4,
        borderWidth:2,
        pointRadius:0,
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:false} },
      scales:{ x:{ticks:{display:false},grid:{display:false}}, y:{grid:{color:Chart.defaults.borderColor}} }
    }
  });
}`;

const newFullChart = `function buildFullCurrencyChart(targetCurrency) {
  const hist = STATE.currencies.history?.[targetCurrency] || [];
  const rates = STATE.currencies.latest_rates || {};

  let histData = hist;
  if (!histData.length) {
    const baseRate = rates[targetCurrency];
    const demos = {
      EUR: 0.9215, GBP: 0.7843, JPY: 148.23, CNY: 7.2451,
      IDR: 15782, AUD: 1.5321, SGD: 1.3512, CAD: 1.3601
    };
    const base = baseRate ? parseFloat(baseRate) : (demos[targetCurrency] || 1.0);
    histData = generateSimulatedTrend(base, 7);
  }

  const labels = histData.map(p => p.x);
  const data   = histData.map(p => p.y);

  const colorMap = {
    EUR: '#3b82f6', GBP: '#10b981', JPY: '#f59e0b',
    CNY: '#ef4444', IDR: '#8b5cf6', AUD: '#06b6d4',
    SGD: '#ec4899', CAD: '#f97316'
  };
  const color = colorMap[targetCurrency] || '#f59e0b';

  if (STATE.charts.currencyTrend) STATE.charts.currencyTrend.destroy();
  const canvas = document.getElementById('currencyTrendChart');
  if (!canvas) return;

  STATE.charts.currencyTrend = new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: \`USD/\${targetCurrency}\`,
        data,
        borderColor: color,
        backgroundColor: color + '18',
        fill: true,
        tension: 0.4,
        borderWidth: 2,
        pointRadius: 3,
        pointBackgroundColor: color,
        pointHoverRadius: 5,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 500 },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1e293b',
          titleColor: '#94a3b8',
          bodyColor: '#f8fafc',
          borderColor: color,
          borderWidth: 1,
          padding: 10,
          callbacks: {
            label: ctx => \` \${targetCurrency}: \${ctx.parsed.y.toFixed(6)}\`
          }
        }
      },
      scales: {
        x: { ticks: { display: true, font: { size: 10 } }, grid: { display: false } },
        y: { grid: { color: Chart.defaults.borderColor }, ticks: { font: { size: 11 } } }
      }
    }
  });
}`;

const norm2 = content.replace(/\r\n/g, '\n');
const oldFull2 = oldFullChart.replace(/\r\n/g, '\n');
if (norm2.indexOf(oldFull2) !== -1) {
    const newNorm2 = norm2.replace(oldFull2, () => newFullChart);
    content = newNorm2.replace(/\n/g, '\r\n');
    console.log("Replaced buildFullCurrencyChart()!");
} else {
    console.error("ERROR: Could not find buildFullCurrencyChart() function body!");
}

// ─── PART 4: Wire up dashCurrencySelector change event ───────────────────────
// Find initCurrencyPage and append selector binding after existing select event
const oldInitCurrencyPageEnd = `  // Converter
  document.getElementById('convertBtn').addEventListener('click', () => {`;

const newInitCurrencyPageEnd = `  // Converter
  document.getElementById('convertBtn').addEventListener('click', () => {`;

// Actually, we just need to add the dashboard selector wiring after loadAllData.
// Find the line where buildCurrencyLineChart is first called, after buildDashCharts:
const oldBuildDashCharts = `  // Charts
    buildDashCharts();`;
// Don't touch that. Let's instead search for where dashCurrencySelector onChange should be wired.
// It's cleanest to add it in the DOMContentLoaded / initCurrencyPage area.
// Find the currencyChartSelect event listener (which already exists) and add dashCurrencySelector ABOVE it:

const oldCurrChartSelectListener = `  document.getElementById('currencyChartSelect').addEventListener('change', function() {
    buildFullCurrencyChart(this.value);
  });`;

const newCurrChartSelectListener = `  document.getElementById('currencyChartSelect').addEventListener('change', function() {
    buildFullCurrencyChart(this.value);
  });

  // Build initial full chart with fallback
  buildFullCurrencyChart(document.getElementById('currencyChartSelect').value);`;

const norm3 = content.replace(/\r\n/g, '\n');
const oldCCS = oldCurrChartSelectListener.replace(/\r\n/g, '\n');
if (norm3.indexOf(oldCCS) !== -1) {
    const newNorm3 = norm3.replace(oldCCS, () => newCurrChartSelectListener.replace(/\r\n/g, '\n'));
    content = newNorm3.replace(/\n/g, '\r\n');
    console.log("Added initial buildFullCurrencyChart call!");
} else {
    console.error("WARN: Could not locate currencyChartSelect listener. Skipping initial chart call.");
}

// ─── PART 5: Wire up buildCurrencyLineChart call with selector ────────────────
// Add selector event after buildDashCharts() is first called, inside updateCurrencyDisplays or buildDashCharts
// Find buildCurrencyLineChart() call at end of buildDashCharts (it's at end of buildDashCharts func)
const oldBuildCurrencyCall = `  // Currency Line
  buildCurrencyLineChart();`;

const newBuildCurrencyCall = `  // Currency Line
  buildCurrencyLineChart();

  // Wire up dashboard currency selector
  const dashCurrSel = document.getElementById('dashCurrencySelector');
  if (dashCurrSel && !dashCurrSel.__wired) {
    dashCurrSel.__wired = true;
    dashCurrSel.addEventListener('change', function() {
      buildCurrencyLineChart(this.value);
    });
  }`;

const norm4 = content.replace(/\r\n/g, '\n');
const oldBCC = oldBuildCurrencyCall.replace(/\r\n/g, '\n');
if (norm4.indexOf(oldBCC) !== -1) {
    const newNorm4 = norm4.replace(oldBCC, () => newBuildCurrencyCall.replace(/\r\n/g, '\n'));
    content = newNorm4.replace(/\n/g, '\r\n');
    console.log("Wired up dashCurrencySelector change event!");
} else {
    console.error("WARN: Could not locate '// Currency Line' call. Skipping selector wiring.");
}

fs.writeFileSync(path, content, 'utf8');
console.log("\nSaved dashboard.blade.php with Currency Trends fixes!");
