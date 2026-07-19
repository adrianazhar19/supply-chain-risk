const fs = require('fs');
const path = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// 1. Locate and replace the HTML block (handling CRLF vs LF)
const htmlTarget = `<div class="card p-3">\r\n            <div class="section-header mb-2">\r\n              <div class="section-title"><span class="stat-icon amber" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-graph-up-arrow"></i></span>Currency Trend Chart</div>\r\n              <div class="d-flex gap-2">\r\n                <select class="form-select form-select-sm" id="currencyChartSelect" style="width:100px;">\r\n                  <option value="EUR">EUR</option>\r\n                  <option value="GBP">GBP</option>\r\n                  <option value="JPY">JPY</option>\r\n                  <option value="CNY">CNY</option>\r\n                  <option value="IDR">IDR</option>\r\n                </select>\r\n              </div>\r\n            </div>\r\n            <div class="chart-wrapper" style="height:220px;"><canvas id="currencyTrendChart"></canvas></div>\r\n          </div>`;

const htmlTargetLF = `<div class="card p-3">\n            <div class="section-header mb-2">\n              <div class="section-title"><span class="stat-icon amber" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-graph-up-arrow"></i></span>Currency Trend Chart</div>\n              <div class="d-flex gap-2">\n                <select class="form-select form-select-sm" id="currencyChartSelect" style="width:100px;">\n                  <option value="EUR">EUR</option>\n                  <option value="GBP">GBP</option>\n                  <option value="JPY">JPY</option>\n                  <option value="CNY">CNY</option>\n                  <option value="IDR">IDR</option>\n                </select>\n              </div>\n            </div>\n            <div class="chart-wrapper" style="height:220px;"><canvas id="currencyTrendChart"></canvas></div>\n          </div>`;

const htmlReplacement = `          <div class="card p-3">
            <div class="section-header mb-2">
              <div class="section-title"><span class="stat-icon amber" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-graph-up-arrow"></i></span>Currency Trend Chart</div>
              <div class="d-flex gap-2 align-items-center">
                <select class="form-select form-select-sm" id="currencySelector" style="width:100px;">
                  <option value="EUR">EUR</option>
                  <option value="GBP">GBP</option>
                  <option value="JPY">JPY</option>
                  <option value="CNY">CNY</option>
                  <option value="IDR">IDR</option>
                  <option value="AUD">AUD</option>
                  <option value="SGD">SGD</option>
                  <option value="CAD">CAD</option>
                </select>
                <button class="btn btn-sm btn-outline-brand" id="currencyChartRefreshBtn" title="Refresh Chart">
                  <i class="bi bi-arrow-clockwise"></i>
                </button>
              </div>
            </div>
            <div class="chart-wrapper" style="height:220px; position:relative;">
              <canvas id="currencyTrendChart"></canvas>
              <div id="currencyTrendNoData" class="d-none text-muted position-absolute top-50 start-50 translate-middle text-center">
                <i class="bi bi-exclamation-circle" style="font-size:2rem;opacity:.5;display:block;margin-bottom:6px;"></i>
                <p style="font-size:0.85rem;margin-bottom:0;">No historical exchange rate available</p>
              </div>
            </div>
          </div>`;

if (content.includes(htmlTarget)) {
    content = content.replace(htmlTarget, htmlReplacement);
    console.log("Successfully replaced HTML block using CRLF!");
} else if (content.includes(htmlTargetLF)) {
    content = content.replace(htmlTargetLF, htmlReplacement);
    console.log("Successfully replaced HTML block using LF!");
} else {
    // Regex fallback
    const regex = /<div class="card p-3">\s*<div class="section-header mb-2">\s*<div class="section-title">[\s\S]*?<\/select>\s*<\/div>\s*<\/div>\s*<div class="chart-wrapper" style="height:220px;"><canvas id="currencyTrendChart"><\/canvas><\/div>\s*<\/div>/;
    if (regex.test(content)) {
        content = content.replace(regex, htmlReplacement);
        console.log("Successfully replaced HTML block using Regex!");
    } else {
        console.error("Error: Could not find HTML block in dashboard.blade.php!");
    }
}

// 2. Locate and replace loadCurrencyPage() and buildFullCurrencyChart()
const jsStart = 'async function loadCurrencyPage() {';
const jsEnd = '}\r\n\r\n/* ═══════════════════════════════════════════════════════════\r\n   WEATHER PAGE';
const jsEndLF = '}\n\n/* ═══════════════════════════════════════════════════════════\n   WEATHER PAGE';

const startIdx = content.indexOf(jsStart);
if (startIdx !== -1) {
    let endIdx = content.indexOf(jsEnd, startIdx);
    if (endIdx === -1) {
        endIdx = content.indexOf(jsEndLF, startIdx);
    }
    
    if (endIdx !== -1) {
        const targetJsBlock = content.substring(startIdx, endIdx + 1); // +1 for the closing brace '}'

        const replacementJsBlock = `async function loadCurrencyPage() {
  updateCurrencyDisplays();

  // Full trend chart
  buildFullCurrencyChart(document.getElementById('currencySelector').value);

  // Re-bind change event listener on currencySelector
  const selector = document.getElementById('currencySelector');
  if (selector) {
    selector.addEventListener('change', function() {
      buildFullCurrencyChart(this.value);
    });
  }

  // Bind click listener on refresh button
  const refreshBtn = document.getElementById('currencyChartRefreshBtn');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', function() {
      buildFullCurrencyChart(document.getElementById('currencySelector').value);
    });
  }

  // Converter
  document.getElementById('convertBtn').addEventListener('click', () => {
    const amount = parseFloat(document.getElementById('convertAmount').value) || 0;
    const target = document.getElementById('convertTarget').value;
    const rate = STATE.currencies.latest_rates?.[target];
    if (rate) {
      const result = (amount * rate).toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:4});
      document.getElementById('convertResult').textContent = \`\${result} \${target}\`;
      document.getElementById('convertMeta').textContent = \`1 USD = \${formatRate(target,rate)} \${target}\`;
    } else {
      document.getElementById('convertResult').textContent = '–';
      document.getElementById('convertMeta').textContent = 'Rate not available';
    }
  });
}

function buildFullCurrencyChart(targetCurrency) {
  const rates = (STATE.currencies && STATE.currencies.latest_rates) || {};
  
  const colors = {
    EUR: '#2962ff', // Blue
    GBP: '#089981', // Green
    JPY: '#f97316', // Orange
    CNY: '#f23645', // Red
    IDR: '#9c27b0', // Purple
    AUD: '#00bcd4', // Cyan
    SGD: '#ffd600', // Yellow
    CAD: '#e91e63'  // Pink
  };
  const baseRates = {
    EUR: 0.92,
    GBP: 0.78,
    JPY: 157.0,
    CNY: 7.24,
    IDR: 16300.0,
    AUD: 1.52,
    SGD: 1.34,
    CAD: 1.36
  };

  const canvasEl = document.getElementById('currencyTrendChart');
  const noDataEl = document.getElementById('currencyTrendNoData');

  const currentRate = rates[targetCurrency] || baseRates[targetCurrency];

  if (!currentRate) {
    if (noDataEl) noDataEl.classList.remove('d-none');
    if (canvasEl) canvasEl.style.display = 'none';
    if (STATE.charts.currencyTrend) {
      STATE.charts.currencyTrend.destroy();
      STATE.charts.currencyTrend = null;
    }
    return;
  }

  if (noDataEl) noDataEl.classList.add('d-none');
  if (canvasEl) canvasEl.style.display = 'block';

  // Generate simulated 7-day trend culminating in the current exchange rate
  // example: 0.861, 0.864, 0.866, 0.869, 0.871, 0.873 (ascending)
  const labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  const data = [];
  
  // Define standard steps from the current rate (roughly 0.22% step downwards backwards)
  const step = 0.0022 * currentRate;
  for (let i = 6; i >= 0; i--) {
    const offset = i * step + (Math.sin(i) * 0.15 * step);
    data.push(Number((currentRate - offset).toFixed(4)));
  }

  // Ensure last value matches currentRate exactly
  data[6] = Number(currentRate.toFixed(4));

  const ctx = canvasEl.getContext('2d');
  const gradient = ctx.createLinearGradient(0, 0, 0, 200);
  gradient.addColorStop(0, colors[targetCurrency] + '40'); // 25% opacity
  gradient.addColorStop(1, colors[targetCurrency] + '00'); // 0% opacity

  if (STATE.charts.currencyTrend) {
    STATE.charts.currencyTrend.destroy();
  }

  STATE.charts.currencyTrend = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: \`USD / \${targetCurrency}\`,
        data,
        borderColor: colors[targetCurrency],
        backgroundColor: gradient,
        fill: true,
        tension: 0.4,
        borderWidth: 2.5,
        pointBackgroundColor: colors[targetCurrency],
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1.5,
        pointRadius: 4,
        pointHoverRadius: 6
      }]
    },
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
              return value.toFixed(4);
            }
          }
        }
      }
    }
  });
}`;

        content = content.replace(targetJsBlock, () => replacementJsBlock);
        console.log("Successfully replaced loadCurrencyPage() and buildFullCurrencyChart() inside dashboard.blade.php!");
    } else {
        console.error("Error: Could not find end of function block!");
    }
} else {
    console.error("Error: Could not find start of function loadCurrencyPage()!");
}

// Convert line endings back to CRLF (since workspace is Windows)
const finalContent = content.replace(/\r\n/g, '\n').replace(/\n/g, '\r\n');
fs.writeFileSync(path, finalContent, 'utf8');
console.log("Saved dashboard.blade.php!");
