const fs = require('fs');
const path = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// 1. Locate and replace the HTML chart wrapper block
const htmlTarget = `<div class="chart-wrapper" style="height:280px;">\r\n          <canvas id="dashCurrencyLine"></canvas>\r\n        </div>`;
const htmlTargetLF = `<div class="chart-wrapper" style="height:280px;">\n          <canvas id="dashCurrencyLine"></canvas>\n        </div>`;

const htmlReplacement = `        <div class="chart-wrapper" style="height:280px; position:relative;">
          <canvas id="dashCurrencyLine"></canvas>
          <div id="currencyNoData" class="d-none text-muted position-absolute top-50 start-50 translate-middle text-center">
            <i class="bi bi-exclamation-circle" style="font-size:2rem;opacity:.5;display:block;margin-bottom:6px;"></i>
            <p style="font-size:0.85rem;margin-bottom:0;">No historical currency data found</p>
          </div>
        </div>`;

// Replace HTML block (handling CRLF vs LF)
if (content.includes(htmlTarget)) {
    content = content.replace(htmlTarget, htmlReplacement);
    console.log("Successfully replaced HTML wrapper using CRLF!");
} else if (content.includes(htmlTargetLF)) {
    content = content.replace(htmlTargetLF, htmlReplacement);
    console.log("Successfully replaced HTML wrapper using LF!");
} else {
    // Try regex-based replacement
    const regex = /<div class="chart-wrapper" style="height:280px;">\s*<canvas id="dashCurrencyLine"><\/canvas>\s*<\/div>/;
    if (regex.test(content)) {
        content = content.replace(regex, htmlReplacement);
        console.log("Successfully replaced HTML wrapper using Regex!");
    } else {
        console.error("Error: Could not find HTML wrapper block!");
    }
}

// 2. Locate and replace function buildCurrencyLineChart()
const jsStart = 'function buildCurrencyLineChart() {';
const jsEnd = '}\n\n/* ═══════════════════════════════════════════════════════════\n   LOAD DATA';
const jsEndCRLF = '}\r\n\r\n/* ═══════════════════════════════════════════════════════════\r\n   LOAD DATA';

const startIdx = content.indexOf(jsStart);
if (startIdx !== -1) {
    let endIdx = content.indexOf(jsEnd, startIdx);
    if (endIdx === -1) {
        endIdx = content.indexOf(jsEndCRLF, startIdx);
    }
    
    if (endIdx !== -1) {
        const targetJsBlock = content.substring(startIdx, endIdx + 1); // +1 for the closing brace '}'

        const replacementJsBlock = `function buildCurrencyLineChart() {
  const rates = (STATE.currencies && STATE.currencies.latest_rates) || {};
  const hist = (STATE.currencies && STATE.currencies.history) || [];

  const currencies = ['EUR', 'GBP', 'JPY', 'CNY', 'SGD'];
  const colors = {
    EUR: '#3b82f6',
    GBP: '#10b981',
    JPY: '#f97316',
    CNY: '#7c3aed',
    SGD: '#ef4444'
  };
  const baseRates = {
    EUR: 0.92,
    GBP: 0.78,
    JPY: 157.0,
    CNY: 7.24,
    SGD: 1.34
  };

  const canvasEl = document.getElementById('dashCurrencyLine');
  const noDataEl = document.getElementById('currencyNoData');

  // Check if we have any currency data (either history or latest rates)
  const hasHistory = Array.isArray(hist) && hist.length > 0;
  const hasLatest = Object.keys(rates).length > 0;

  if (!hasHistory && !hasLatest) {
    // No data exists at all
    if (noDataEl) noDataEl.classList.remove('d-none');
    if (canvasEl) canvasEl.style.display = 'none';
    if (STATE.charts.dashCurrencyLine) {
      STATE.charts.dashCurrencyLine.destroy();
      STATE.charts.dashCurrencyLine = null;
    }
    return;
  }

  // If data exists, hide empty state and show canvas
  if (noDataEl) noDataEl.classList.add('d-none');
  if (canvasEl) canvasEl.style.display = 'block';

  let labels = [];
  let chartData = {
    EUR: [],
    GBP: [],
    JPY: [],
    CNY: [],
    SGD: []
  };

  const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  if (hasHistory && hist.length >= 2) {
    // Show 7 day trend from historical data
    const last7 = hist.slice(-7);
    last7.forEach(entry => {
      const dateObj = new Date(entry.date);
      labels.push(dayNames[dateObj.getDay()]);
      currencies.forEach(c => {
        chartData[c].push(entry[c] !== undefined && entry[c] !== null ? entry[c] : (rates[c] || baseRates[c]));
      });
    });
  } else {
    // Create simulated trend using latest value
    for (let i = 6; i >= 0; i--) {
      const d = new Date();
      d.setDate(d.getDate() - i);
      labels.push(dayNames[d.getDay()]);
    }
    
    // Seed random walk
    currencies.forEach(c => {
      const latestVal = rates[c] || (hasHistory && hist[0] && hist[0][c]) || baseRates[c];
      let currentVal = latestVal;
      for (let i = 0; i < 7; i++) {
        const variation = (Math.sin(i / 1.5) * 0.005) + ((Math.random() - 0.5) * 0.003);
        currentVal = currentVal * (1 + variation);
        chartData[c].push(currentVal);
      }
    });
  }

  const datasets = currencies.map(c => ({
    label: c,
    data: chartData[c],
    borderColor: colors[c],
    backgroundColor: colors[c] + '12', // Fill area transparency
    fill: true,
    tension: 0.4,
    borderWidth: 2.5,
    pointRadius: 3,
    pointHoverRadius: 5
  }));

  if (STATE.charts.dashCurrencyLine) {
    STATE.charts.dashCurrencyLine.destroy();
  }

  STATE.charts.dashCurrencyLine = new Chart(canvasEl.getContext('2d'), {
    type: 'line',
    data: { labels, datasets },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 1000,
        easing: 'easeOutQuart'
      },
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: {
            usePointStyle: true,
            boxWidth: 8,
            font: { size: 11, family: 'var(--font-sans, sans-serif)' }
          }
        },
        tooltip: {
          mode: 'index',
          intersect: false,
          backgroundColor: 'rgba(15, 23, 42, 0.9)',
          titleFont: { size: 12, weight: 'bold' },
          bodyFont: { size: 11 },
          borderColor: 'rgba(255, 255, 255, 0.1)',
          borderWidth: 1
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 10 } }
        },
        y: {
          grid: { color: 'rgba(255, 255, 255, 0.05)' },
          ticks: {
            font: { size: 10 },
            callback: function(value) {
              return value.toFixed(2);
            }
          }
        }
      }
    }
  });
}`;

        content = content.replace(targetJsBlock, () => replacementJsBlock);
        console.log("Successfully replaced function buildCurrencyLineChart() inside dashboard.blade.php!");
    } else {
        console.error("Error: Could not find end of function buildCurrencyLineChart()!");
    }
} else {
    console.error("Error: Could not find start of function buildCurrencyLineChart()!");
}

// Convert line endings back to CRLF (since workspace is Windows)
const finalContent = content.replace(/\r\n/g, '\n').replace(/\n/g, '\r\n');
fs.writeFileSync(path, finalContent, 'utf8');
console.log("Saved dashboard.blade.php!");
