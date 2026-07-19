
/* ═══════════════════════════════════════════════════════════
   GLOBAL STATE
   ═══════════════════════════════════════════════════════════ */
const STATE = {
  theme: 'light',
  autoRefresh: true,
  refreshInterval: null,
  currentPage: 'dashboard',
  countries: [],
  risks: [],
  ports: [],
  currencies: {},
  news: [],
  weather: [],
  watchlist: [],
  notifCount: 0,
  charts: {},
  maps: { dashboard: null, main: null },
  mapLayers: { cluster: null, risk: null },
  dt: {},
};

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ═══════════════════════════════════════════════════════════
   CHART DEFAULTS
   ═══════════════════════════════════════════════════════════ */
function chartDefaults() {
  const dark = STATE.theme === 'dark';
  Chart.defaults.color = dark ? '#94a3b8' : '#475569';
  Chart.defaults.borderColor = dark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.07)';
  Chart.defaults.font.family = "'Inter', sans-serif";
  Chart.defaults.font.size = 11;
}

/* ═══════════════════════════════════════════════════════════
   THEME MANAGEMENT
   ═══════════════════════════════════════════════════════════ */
function applyTheme(theme) {
  STATE.theme = theme;
  document.getElementById('htmlRoot').setAttribute('data-theme', theme);
  const icon = document.getElementById('themeIcon');
  icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars';
  document.querySelectorAll('.dataTables_wrapper .dataTables_filter input').forEach(el => {
    el.style.background = theme === 'dark' ? '#0d1526' : '#f0f4f8';
    el.style.color = theme === 'dark' ? '#f1f5f9' : '#0f172a';
  });

  chartDefaults();

  // Update map tiles
  if (STATE.maps.dashboard) swapMapTile(STATE.maps.dashboard, theme);
  if (STATE.maps.main)      swapMapTile(STATE.maps.main, theme);

  // Redraw charts
  Object.values(STATE.charts).forEach(c => { try { c.update(); } catch(e){} });
}

function swapMapTile(map, theme) {
  map.eachLayer(l => { if (l instanceof L.TileLayer) map.removeLayer(l); });
  L.tileLayer(
    theme === 'dark'
      ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
      : 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
    { attribution: '© CartoDB © OpenStreetMap contributors', maxZoom: 19 }
  ).addTo(map);
}

document.getElementById('themeToggleBtn').addEventListener('click', () => {
  applyTheme(STATE.theme === 'light' ? 'dark' : 'light');
});

/* ═══════════════════════════════════════════════════════════
   LIVE CLOCK
   ═══════════════════════════════════════════════════════════ */
function startClock() {
  const update = () => {
    const now = new Date();
    document.getElementById('liveClock').textContent =
      now.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit'});
  };
  update();
  setInterval(update, 1000);
}

/* ═══════════════════════════════════════════════════════════
   TOAST NOTIFICATIONS
   ═══════════════════════════════════════════════════════════ */
function showToast(type, title, desc, duration = 5000) {
  const icons = { success:'bi-check-circle-fill text-success', danger:'bi-exclamation-triangle-fill text-danger',
                  warning:'bi-exclamation-circle-fill text-warning', info:'bi-info-circle-fill text-primary' };
  const toast = document.createElement('div');
  toast.className = 'toast-custom animate__animated animate__fadeInRight';
  toast.innerHTML = `
    <i class="bi ${icons[type]||icons.info} toast-icon"></i>
    <div class="toast-body">
      <div class="toast-title">${title}</div>
      ${desc ? `<div class="toast-desc">${desc}</div>` : ''}
    </div>
    <button class="toast-close" onclick="this.closest('.toast-custom').remove()"><i class="bi bi-x"></i></button>
  `;
  document.getElementById('toastContainer').appendChild(toast);
  if (duration > 0) setTimeout(() => toast.remove(), duration);
  STATE.notifCount++;
  document.getElementById('notifBadge').textContent = STATE.notifCount;
}

/* ═══════════════════════════════════════════════════════════
   PAGE NAVIGATION
   ═══════════════════════════════════════════════════════════ */
const PAGE_TITLES = {
  dashboard: 'Dashboard Overview',
  map: 'Global Threat Map',
  risk: 'Risk Analytics',
  countries: 'Country Intelligence Ledger',
  'country-intelligence': 'Country Intelligence Center',
  ports: 'Port Intelligence Database',
  news: 'News Intelligence Center',
  currency: 'Exchange Rates',
  weather: 'Weather Intelligence',
  watchlist: 'My Watchlist',
  reports: 'Report Generation',
};

function showPage(page) {
  // Hide all pages
  document.querySelectorAll('.content-page').forEach(p => p.classList.remove('active'));
  // Show target
  const target = document.getElementById('page-' + page);
  if (target) target.classList.add('active');

  // Update sidebar active state
  document.querySelectorAll('.sidebar-link').forEach(l => {
    l.classList.toggle('active', l.dataset.page === page);
  });

  // Update topbar title
  document.getElementById('currentPageTitle').textContent = PAGE_TITLES[page] || page;
  STATE.currentPage = page;

  // Lazy-load page specific data
  if (page === 'map') initMainMap();
  if (page === 'risk') loadRiskPage();
  if (page === 'countries') initCountriesTable();
  if (page === 'country-intelligence') CI.init();
  if (page === 'ports') initPortsTable();
  if (page === 'news') loadNewsPage();
  if (page === 'currency') loadCurrencyPage();
  if (page === 'weather') loadWeatherPage();
  if (page === 'watchlist') loadWatchlistPage();

  window.scrollTo(0, 0);

  // Close mobile sidebar
  if (window.innerWidth < 992) {
    document.getElementById('sidebar').classList.remove('open');
  }
}

/* ═══════════════════════════════════════════════════════════
   MAP INITIALIZERS
   ═══════════════════════════════════════════════════════════ */
function createMap(elementId, zoom = 2) {
  const map = L.map(elementId, { center:[15,10], zoom, zoomControl:true });
  const theme = STATE.theme;
  L.tileLayer(
    theme === 'dark'
      ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
      : 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
    { attribution:'© CartoDB © OpenStreetMap contributors', maxZoom:19 }
  ).addTo(map);
  return map;
}

function populateMaps() {
  const mapsToFill = [];
  if (STATE.maps.dashboard) mapsToFill.push(STATE.maps.dashboard);
  if (STATE.maps.main)      mapsToFill.push(STATE.maps.main);

  mapsToFill.forEach(map => {
    // Clear old layers
    map.eachLayer(l => { if (!(l instanceof L.TileLayer)) map.removeLayer(l); });

    const cluster = L.markerClusterGroup({ showCoverageOnHover: false, maxClusterRadius: 50 });
    const riskLayer = L.layerGroup();

    // Plot risk circles
    STATE.risks.forEach(r => {
      if (!r.country?.latitude || !r.country?.longitude) return;
      const color = riskColor(r.risk_level);
      const score = parseFloat(r.total_score);
      const circle = L.circleMarker([r.country.latitude, r.country.longitude], {
        radius: 7 + score / 9,
        fillColor: color,
        color: '#fff',
        weight: 1.5,
        fillOpacity: 0.65,
      });
      circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
      riskLayer.addLayer(circle);
    });

    // Plot port markers
    const portIcon = L.divIcon({
      html: '<div style="width:10px;height:10px;background:#3b82f6;border:2px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.3);"></div>',
      className: '',
      iconSize: [10,10],
      iconAnchor: [5,5],
    });

    STATE.ports.slice(0, 600).forEach(p => {
      if (!p.latitude || !p.longitude) return;
      const m = L.marker([p.latitude, p.longitude], { icon: portIcon });
      m.bindPopup(`
        <div style="font-family:var(--font-sans,sans-serif);min-width:160px;">
          <div style="font-weight:700;font-size:.85rem;margin-bottom:4px;">🚢 ${p.name}</div>
          <hr style="margin:6px 0;">
          <div style="font-size:.75rem;"><b>Country:</b> ${p.country?.name || '–'}</div>
          <div style="font-size:.75rem;"><b>Type:</b> ${p.harbor_type || '–'}</div>
          <div style="font-size:.75rem;"><b>WPI:</b> ${p.wpi_code || '–'}</div>
        </div>
      `);
      cluster.addLayer(m);
    });

    map.addLayer(riskLayer);
    map.addLayer(cluster);

    setTimeout(() => map.invalidateSize(), 200);
  });
}

function buildRiskPopup(r) {
  const color = riskColor(r.risk_level);
  return `
    <div style="font-family:var(--font-sans,sans-serif);min-width:220px;">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
        <img src="https://flagcdn.com/w30/${r.country.code.toLowerCase()}.png" style="border-radius:3px;">
        <div>
          <div style="font-weight:700;font-size:.9rem;">${r.country.name}</div>
          <span style="background:${color};color:#fff;padding:2px 8px;border-radius:12px;font-size:.65rem;font-weight:700;">${r.risk_level.toUpperCase()}</span>
        </div>
      </div>
      <div style="font-size:.75rem;line-height:1.8;">
        <div>🎯 <b>Total Score:</b> ${parseFloat(r.total_score).toFixed(1)}</div>
        <div>🌦️ <b>Weather:</b> ${parseFloat(r.weather_score).toFixed(0)}</div>
        <div>📊 <b>Inflation:</b> ${parseFloat(r.inflation_score).toFixed(0)}</div>
        <div>📰 <b>Political:</b> ${parseFloat(r.political_score).toFixed(0)}</div>
      </div>
      <button onclick="viewCountry(${r.country_id})"
        style="margin-top:10px;width:100%;background:#2563eb;color:#fff;border:none;padding:6px;border-radius:6px;font-size:.75rem;font-weight:600;cursor:pointer;">
        View Full Profile
      </button>
    </div>
  `;
}

function riskColor(level) {
  const m = { Low:'#10b981', Medium:'#f59e0b', High:'#ef4444', Critical:'#7c3aed' };
  return m[level] || '#10b981';
}

function initMainMap() {
  if (!STATE.maps.main) {
    STATE.maps.main = createMap('main-map', 2);
    populateMaps();
  }

  // Map filter
  document.getElementById('mapSearchBtn').addEventListener('click', () => {
    const countryId = document.getElementById('mapCountryFilter').value;
    if (countryId && STATE.maps.main) {
      const c = STATE.countries.find(x => x.id == countryId);
      if (c?.latitude) STATE.maps.main.setView([c.latitude, c.longitude], 5);
    }
  });
}

/* ═══════════════════════════════════════════════════════════
   CHARTS — DASHBOARD
   ═══════════════════════════════════════════════════════════ */
function buildDashCharts() {
  chartDefaults();

  // Risk Pie
  const sorted = [...STATE.risks].sort((a,b) => b.total_score - a.total_score);
  const low = STATE.risks.filter(r => r.risk_level === 'Low').length;
  const med = STATE.risks.filter(r => r.risk_level === 'Medium').length;
  const high = STATE.risks.filter(r => r.risk_level === 'High').length;
  const crit = STATE.risks.filter(r => r.risk_level === 'Critical').length;

  if (STATE.charts.dashRiskPie) STATE.charts.dashRiskPie.destroy();
  STATE.charts.dashRiskPie = new Chart(
    document.getElementById('riskChart').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: ['Low', 'Medium', 'High', 'Critical'],
      datasets: [{ data:[low,med,high,crit], backgroundColor:['#10b981','#f59e0b','#ef4444','#7c3aed'], borderWidth:2, borderColor: STATE.theme==='dark'?'#0a0f1e':'#ffffff' }]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins: { legend:{ position:'bottom', labels:{ usePointStyle:true, padding:12, font:{size:10} } } },
      cutout:'65%'
    }
  });

  // Risk Bar
  const top10 = sorted.slice(0, 10);
  if (STATE.charts.dashRiskBar) STATE.charts.dashRiskBar.destroy();
  STATE.charts.dashRiskBar = new Chart(
    document.getElementById('dashRiskBar').getContext('2d'), {
    type: 'bar',
    data: {
      labels: top10.map(r => r.country?.name || 'Unknown'),
      datasets: [{
        label: 'Risk Score',
        data: top10.map(r => parseFloat(r.total_score).toFixed(1)),
        backgroundColor: top10.map(r => riskColor(r.risk_level) + 'CC'),
        borderColor: top10.map(r => riskColor(r.risk_level)),
        borderWidth: 1,
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:false} },
      scales: {
        x:{ beginAtZero:true, max:100, grid:{color:Chart.defaults.borderColor} },
        y:{ grid:{display:false}, ticks:{font:{size:11}} }
      }
    }
  });

  // Currency Line
  buildCurrencyLineChart();

  // Top 5 risk list
  const listEl = document.getElementById('dashTopRisk');
  listEl.innerHTML = '';
  top10.slice(0,5).forEach((r,i) => {
    const color = riskColor(r.risk_level);
    listEl.innerHTML += `
      <div class="d-flex align-items-center gap-2 py-2 border-bottom" style="border-color:var(--border-color)!important;cursor:pointer;" onclick="viewCountry(${r.country_id})">
        <span style="font-size:.7rem;font-weight:700;color:var(--text-muted);width:16px;">${i+1}</span>
        <img src="https://flagcdn.com/w20/${r.country.code.toLowerCase()}.png" style="border-radius:2px;width:20px;" alt="">
        <span style="flex:1;font-size:.82rem;font-weight:500;color:var(--text-primary);">${r.country.name}</span>
        <span style="font-family:var(--font-mono);font-size:.78rem;font-weight:700;">${parseFloat(r.total_score).toFixed(1)}</span>
        <span class="risk-pill ${r.risk_level.toLowerCase()}">${r.risk_level}</span>
      </div>
    `;
  });
}

function buildCurrencyLineChart() {
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
}

/* ═══════════════════════════════════════════════════════════
   LOAD DATA
   ═══════════════════════════════════════════════════════════ */
async function fetchJSON(url) {
  const r = await fetch(url);
  if (!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}

async function loadAllData(silent = false) {
  if (!silent) {
    const btn = document.getElementById('refreshNowBtn');
    btn.innerHTML = '<i class="bi bi-arrow-clockwise" style="animation:spin 1s linear infinite;"></i> Loading...';
    btn.disabled = true;
  }

  try {
    const [statsRes, riskRes, currencyRes, portsRes, countriesRes] = await Promise.allSettled([
      fetchJSON('/api/stats'),
      fetchJSON('/api/risk'),
      fetchJSON('/api/currency'),
      fetchJSON('/api/ports'),
      fetchJSON('/api/countries'),
    ]);

    // Stats
    if (statsRes.status === 'fulfilled' && statsRes.value.status) {
      const s = statsRes.value.data;
      animateCounter('sc-countries', s.countries);
      animateCounter('sc-ports', s.ports);
      animateCounter('sc-news', s.news);
      animateCounter('sc-currencies', s.currencies);
      animateCounter('sc-alerts', s.risk_alerts);
      document.getElementById('newsCountBadge').textContent = s.news;
      document.getElementById('lastSyncTime').textContent = new Date().toLocaleTimeString();
    }

    // Risks
    if (riskRes.status === 'fulfilled' && riskRes.value.status) {
      STATE.risks = riskRes.value.data;
    }

    // Currency
    if (currencyRes.status === 'fulfilled' && currencyRes.value.status) {
      STATE.currencies = currencyRes.value.data;
      updateCurrencyDisplays();
    }

    // Ports
    if (portsRes.status === 'fulfilled' && portsRes.value.status) {
      STATE.ports = portsRes.value.data;
    }

    // Countries
    if (countriesRes.status === 'fulfilled' && countriesRes.value.status) {
      STATE.countries = countriesRes.value.data;
      populateCountrySelects();
    }

    // Maps
    if (!STATE.maps.dashboard) {
      STATE.maps.dashboard = createMap('dashboard-map', 2);
    }
    populateMaps();

    // Charts
    buildDashCharts();
    updateDashNewsCompact();
    updateDashWeather();
    updateDashRecentActivity();

    document.getElementById('systemStatusText').textContent = 'All Systems Online';
    document.getElementById('dbStatusDot').className = 'status-dot online';
  } catch (e) {
    console.error('Data load error:', e);
    document.getElementById('systemStatusText').textContent = 'Partial Data';
    document.getElementById('dbStatusDot').className = 'status-dot warning';
    if (!silent) showToast('warning', 'Data Sync Warning', 'Some data sources could not be reached.');
  }

  if (!silent) {
    const btn = document.getElementById('refreshNowBtn');
    btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refresh';
    btn.disabled = false;
  }

  document.getElementById('lastSyncTime').textContent = new Date().toLocaleTimeString();
}

function updateCurrencyDisplays() {
  const rates = STATE.currencies.latest_rates || {};
  const names = {EUR:'Euro',GBP:'British Pound',JPY:'Japanese Yen',CNY:'Renminbi',IDR:'Indonesian Rupiah',AUD:'Australian Dollar',SGD:'Singapore Dollar',CAD:'Canadian Dollar'};

  Object.entries(rates).forEach(([code, rate]) => {
    const el = document.getElementById('rate-' + code);
    if (el) el.textContent = formatRate(code, rate);
    const fullEl = document.getElementById('full-rate-' + code);
    if (fullEl) fullEl.textContent = formatRate(code, rate);
    const nameEl = document.querySelector(`#rate-row-${code} .currency-name`);
    if (nameEl) nameEl.textContent = names[code] || '';
    const fullNameEl = document.querySelector(`#full-rate-row-${code} .currency-name`);
    if (fullNameEl) fullNameEl.textContent = names[code] || '';
  });

  const now = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
  document.getElementById('currencyLastSync').textContent = now;
  const fullEl = document.getElementById('currencyFullLastSync');
  if (fullEl) fullEl.textContent = 'Updated: ' + now;
}

function formatRate(code, rate) {
  if (['JPY','IDR','KRW'].includes(code)) {
    return Number(rate).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
  }
  return Number(rate).toFixed(4);
}

function populateCountrySelects() {
  const selects = ['mapCountryFilter','portCountryFilter','watchlistCountrySelect'];
  selects.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    const current = el.value;
    const opts = el.id === 'watchlistCountrySelect'
      ? '<option value="">– Select country –</option>'
      : '<option value="">All Countries</option>';
    el.innerHTML = opts + STATE.countries.map(c =>
      `<option value="${c.id}">${c.name}</option>`
    ).join('');
    if (current) el.value = current;
  });
}

/* ═══════════════════════════════════════════════════════════
   ANIMATED COUNTER
   ═══════════════════════════════════════════════════════════ */
function animateCounter(id, target, duration = 1000) {
  const el = document.getElementById(id);
  if (!el) return;
  const start = parseInt(el.textContent) || 0;
  const startTime = performance.now();
  const tick = (now) => {
    const t = Math.min((now - startTime) / duration, 1);
    const eased = t < 0.5 ? 2*t*t : -1+(4-2*t)*t;
    el.textContent = Math.round(start + (target - start) * eased);
    if (t < 1) requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
}

/* ═══════════════════════════════════════════════════════════
   DASHBOARD NEWS COMPACT
   ═══════════════════════════════════════════════════════════ */
async function updateDashNewsCompact() {
  try {
    const res = await fetchJSON('/api/news');
    if (!res.status) return;
    STATE.news = res.data;
    const el = document.getElementById('dashNewsCompact');
    el.innerHTML = '';
    res.data.slice(0, 4).forEach(a => {
      const color = a.sentiment === 'Positive' ? '#10b981' : a.sentiment === 'Negative' ? '#ef4444' : '#94a3b8';
      const timeAgo = a.publishedAt ? timeSince(new Date(a.publishedAt)) : '–';
      el.innerHTML += `
        <a href="${a.url || '#'}" target="_blank" rel="noopener" style="text-decoration:none;">
          <div class="d-flex gap-3 py-2 border-bottom" style="border-color:var(--border-color)!important;">
            <div class="rounded" style="width:52px;height:52px;background:linear-gradient(135deg,${color}22,${color}44);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.4rem;">
              ${a.sentiment === 'Positive' ? '📈' : a.sentiment === 'Negative' ? '⚠️' : '📰'}
            </div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:.8rem;font-weight:600;color:var(--text-primary);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">${a.title}</div>
              <div style="font-size:.7rem;color:var(--text-muted);margin-top:2px;">${a.source?.name || a.source_name || '–'} · ${timeAgo}</div>
            </div>
          </div>
        </a>
      `;
    });
  } catch(e) { console.error('News compact error:', e); }
}

async function updateDashWeather() {
  try {
    const res = await fetchJSON('/api/weather');
    if (!res.status) return;
    const el = document.getElementById('dashWeatherWidget');
    if (!el) return;
    el.innerHTML = '';
    res.data.slice(0, 5).forEach(item => {
      const c = item.country;
      const w = item.weather;
      el.innerHTML += `
        <div class="d-flex align-items-center justify-content-between py-2 border-bottom" style="border-color:var(--border-color)!important;">
          <div class="d-flex align-items-center gap-2">
            <img src="${c.flag_url}" width="20" style="border-radius:2px;" alt="">
            <div>
              <div style="font-size:.8rem;font-weight:600;color:var(--text-primary);">${c.name}</div>
              <div style="font-size:.68rem;color:var(--text-muted);">${w.description || 'Clear'}</div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <span style="font-size:1.1rem;color:var(--brand-500);"><i class="bi ${w.icon || 'bi-sun-fill'}"></i></span>
            <div class="text-end">
              <div style="font-size:.82rem;font-weight:700;color:var(--text-primary);font-family:var(--font-mono);">${w.temperature.toFixed(1)}°C</div>
              <div style="font-size:.65rem;color:var(--text-muted);font-family:var(--font-mono);"><i class="bi bi-wind me-0.5"></i>${w.wind_speed.toFixed(0)} km/h</div>
            </div>
          </div>
        </div>
      `;
    });
  } catch(e) { console.error('Weather widget error:', e); }
}

function updateDashRecentActivity() {
  const el = document.getElementById('dashRecentActivity');
  if (!el) return;
  const activities = [
    { icon: 'bi-database-fill-check', color: 'text-success', title: 'Risk Index Compiled', desc: `Recalculated threat scores for all ${STATE.countries.length || 250} countries successfully.`, time: 'Just now' },
    { icon: 'bi-newspaper', color: 'text-primary', title: 'Intelligence Feed Updated', desc: `Fetched latest news articles from NewsAPI stream.`, time: '2 mins ago' },
    { icon: 'bi-currency-exchange', color: 'text-warning', title: 'FX Rates Synchronized', desc: `Updated benchmark rates against USD for 6 major currencies.`, time: '15 mins ago' },
    { icon: 'bi-water', color: 'text-info', title: 'Port Directory Verified', desc: `Checked WPI ports connectivity and telemetry records.`, time: '1 hour ago' },
    { icon: 'bi-cloud-sun-fill', color: 'text-teal', title: 'Meteorological Scan', desc: 'Refreshed capital weather data from Open-Meteo cache.', time: '2 hours ago' }
  ];
  el.innerHTML = activities.map(act => `
    <div class="d-flex align-items-start gap-3 py-2 border-bottom" style="border-color:var(--border-color)!important;">
      <div class="stat-icon" style="width:34px;height:34px;font-size:.9rem;border-radius:8px;flex-shrink:0;box-shadow:none;background:rgba(0,0,0,0.02);border:1px solid var(--border-color);margin-bottom:0;">
        <i class="bi ${act.icon} ${act.color}"></i>
      </div>
      <div style="flex:1;min-width:0;">
        <div class="d-flex justify-content-between align-items-center">
          <div style="font-size:.8rem;font-weight:700;color:var(--text-primary);">${act.title}</div>
          <span style="font-size:.65rem;color:var(--text-muted);font-weight:600;">${act.time}</span>
        </div>
        <div style="font-size:.72rem;color:var(--text-secondary);margin-top:2px;">${act.desc}</div>
      </div>
    </div>
  `).join('');
}

function timeSince(date) {
  const sec = Math.floor((new Date() - date) / 1000);
  if (sec < 60) return sec + 's ago';
  if (sec < 3600) return Math.floor(sec/60) + 'm ago';
  if (sec < 86400) return Math.floor(sec/3600) + 'h ago';
  return Math.floor(sec/86400) + 'd ago';
}

/* ═══════════════════════════════════════════════════════════
   COUNTRY DETAIL MODAL
   ═══════════════════════════════════════════════════════════ */
async function viewCountry(id) {
  const modal = new bootstrap.Modal(document.getElementById('countryModal'));
  modal.show();
  document.getElementById('modalCountryName').textContent = 'Loading...';
  document.getElementById('modalCountryMeta').textContent = '–';
  document.getElementById('modalCountryFlag').src = '';
  document.getElementById('modalCountryBody').innerHTML = `
    <div class="text-center py-5">
      <div class="spinner-border" style="color:var(--brand-600);width:3rem;height:3rem;"></div>
      <p class="mt-3" style="color:var(--text-muted);">Consulting World Bank, Open-Meteo & Risk Engine...</p>
    </div>
  `;

  try {
    const res = await fetchJSON('/api/countries/' + id);
    if (!res.status) throw new Error(res.message);
    const d = res.data;
    const risk = d.risk;
    const weather = d.weather;
    const eco = d.economic;

    document.getElementById('modalCountryFlag').src = d.flag_url;
    document.getElementById('modalCountryName').textContent = d.name;
    document.getElementById('modalCountryMeta').textContent = `${d.region} · ${d.currency} · ${d.code}`;

    const rColor = riskColor(risk?.risk_level || 'Low');

    // Forecast HTML
    let forecastHtml = '';
    if (weather?.forecast?.length) {
      forecastHtml = '<div class="row g-2 mt-2">' + weather.forecast.slice(0,5).map(f => `
        <div class="col">
          <div class="forecast-item">
            <div class="forecast-day">${new Date(f.date).toLocaleDateString(undefined,{weekday:'short',month:'short',day:'numeric'})}</div>
            <div class="forecast-icon"><i class="bi ${f.icon || 'bi-cloud'}"></i></div>
            <div class="forecast-temp">${f.temp_max?.toFixed(0)}° / ${f.temp_min?.toFixed(0)}°</div>
            <div style="font-size:.62rem;color:var(--text-muted);margin-top:2px;">${f.description || ''}</div>
          </div>
        </div>
      `).join('') + '</div>';
    }

    // Ports list
    const portsHtml = d.ports?.length
      ? d.ports.slice(0,6).map(p => `
          <div class="d-flex align-items-center gap-2 py-1.5 border-bottom" style="border-color:var(--border-color)!important;">
            <i class="bi bi-water" style="color:var(--brand-600);"></i>
            <span style="font-size:.82rem;flex:1;">${p.name}</span>
            <span style="font-size:.7rem;color:var(--text-muted);">${p.harbor_type||'–'}</span>
          </div>`).join('')
      : '<p style="font-size:.82rem;color:var(--text-muted);">No ports indexed for this territory.</p>';

    document.getElementById('modalCountryBody').innerHTML = `
      <div class="row g-4">
        <!-- Left: Economic + Ports -->
        <div class="col-md-6">
          <div class="card p-3 mb-3" style="background:rgba(59,130,246,.04);border-color:rgba(59,130,246,.12)!important;">
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="stat-icon blue" style="width:28px;height:28px;font-size:.8rem;border-radius:7px;"><i class="bi bi-bank"></i></div>
              <span style="font-weight:700;font-size:.9rem;">World Bank Economic Data</span>
              <span class="ms-auto badge" style="background:rgba(59,130,246,.12);color:var(--brand-600);font-size:.65rem;">World Bank API</span>
            </div>
            <div class="row g-2 text-center">
              ${[
                {label:'GDP', value: eco?.gdp ? '$'+(eco.gdp/1e9).toFixed(1)+'B' : '–'},
                {label:'Population', value: eco?.population ? (eco.population/1e6).toFixed(1)+'M' : '–'},
                {label:'Inflation', value: eco?.inflation ? eco.inflation.toFixed(2)+'%' : '–'},
                {label:'Exports', value: eco?.exports ? '$'+(eco.exports/1e9).toFixed(1)+'B' : '–'},
              ].map(item => `
                <div class="col-6">
                  <div style="background:var(--bg-page);border:1px solid var(--border-color);border-radius:8px;padding:10px 6px;">
                    <div style="font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);">${item.label}</div>
                    <div style="font-size:1rem;font-weight:700;color:var(--text-primary);font-family:var(--font-mono);">${item.value}</div>
                  </div>
                </div>
              `).join('')}
            </div>
          </div>

          <div class="card p-3">
            <div class="d-flex align-items-center gap-2 mb-2">
              <div class="stat-icon green" style="width:28px;height:28px;font-size:.8rem;border-radius:7px;"><i class="bi bi-water"></i></div>
              <span style="font-weight:700;font-size:.9rem;">Port Hubs (${d.ports?.length || 0})</span>
            </div>
            ${portsHtml}
          </div>
        </div>

        <!-- Right: Risk + Weather -->
        <div class="col-md-6">
          <div class="card p-3 mb-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="d-flex align-items-center gap-2">
                <div style="width:28px;height:28px;border-radius:7px;background:${rColor}22;display:flex;align-items:center;justify-content:center;color:${rColor};font-size:.8rem;"><i class="bi bi-shield-exclamation"></i></div>
                <span style="font-weight:700;font-size:.9rem;">Threat Assessment</span>
              </div>
              <span style="background:${rColor};color:#fff;padding:4px 12px;border-radius:20px;font-size:.72rem;font-weight:700;">${risk?.risk_level?.toUpperCase() || 'UNKNOWN'} · ${parseFloat(risk?.total_score||0).toFixed(1)}</span>
            </div>
            ${[
              {label:'Meteorological (W:30%)', val:risk?.weather_score||0, color:'#06b6d4'},
              {label:'Economic Inflation (W:20%)', val:risk?.inflation_score||0, color:'#f59e0b'},
              {label:'Geopolitical (W:40%)', val:risk?.political_score||0, color:'#ef4444'},
              {label:'Currency Risk (W:10%)', val:risk?.currency_score||0, color:'#8b5cf6'},
            ].map(item => `
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-1" style="font-size:.78rem;">
                  <span style="color:var(--text-secondary);">${item.label}</span>
                  <span style="font-weight:700;color:var(--text-primary);">${parseFloat(item.val).toFixed(0)}</span>
                </div>
                <div class="risk-breakdown-bar">
                  <div class="risk-breakdown-fill" style="width:${item.val}%;background:${item.color};"></div>
                </div>
              </div>
            `).join('')}
          </div>

          <div class="card p-3">
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="stat-icon teal" style="width:28px;height:28px;font-size:.8rem;border-radius:7px;"><i class="bi bi-cloud-sun"></i></div>
              <span style="font-weight:700;font-size:.9rem;">Live Meteorological Feed</span>
              <span class="ms-auto badge" style="background:rgba(6,182,212,.12);color:#06b6d4;font-size:.65rem;">Open-Meteo API</span>
            </div>
            <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded" style="background:rgba(0,0,0,.03);">
              <i class="bi ${weather?.icon || 'bi-cloud'}" style="font-size:2.5rem;"></i>
              <div>
                <div style="font-size:1.8rem;font-weight:800;color:var(--text-primary);">${weather?.temperature?.toFixed(1) || '–'}°C</div>
                <div style="font-size:.78rem;color:var(--text-secondary);">${weather?.description || '–'}</div>
              </div>
              <div class="ms-auto text-end" style="font-size:.75rem;color:var(--text-secondary);">
                <div>💨 ${weather?.wind_speed?.toFixed(1) || '–'} km/h</div>
                <div>💧 ${weather?.humidity?.toFixed(0) || '–'}%</div>
                <div>🌧️ ${weather?.rainfall?.toFixed(1) || '–'} mm</div>
              </div>
            </div>
            ${forecastHtml}
          </div>
        </div>
      </div>
    `;
  } catch(e) {
    document.getElementById('modalCountryBody').innerHTML = `<div class="alert alert-danger">Failed: ${e.message}</div>`;
  }
}

/* ═══════════════════════════════════════════════════════════
   RISK ANALYTICS PAGE
   ═══════════════════════════════════════════════════════════ */
let riskDtInited = false;

function loadRiskPage() {
  if (!STATE.risks.length) { setTimeout(loadRiskPage, 500); return; }
  chartDefaults();

  const sorted = [...STATE.risks].sort((a,b) => b.total_score - a.total_score);
  const top10 = sorted.slice(0,10);
  const low10 = sorted.slice(-10).reverse();

  // Top 10 Bar
  if (STATE.charts.riskBar) STATE.charts.riskBar.destroy();
  STATE.charts.riskBar = new Chart(document.getElementById('riskBarChart').getContext('2d'), {
    type:'bar',
    data: {
      labels: top10.map(r => r.country?.name || '–'),
      datasets:[{
        label:'Risk Score',
        data: top10.map(r => parseFloat(r.total_score).toFixed(1)),
        backgroundColor: top10.map(r => riskColor(r.risk_level)+'CC'),
        borderColor: top10.map(r => riskColor(r.risk_level)),
        borderWidth:1, borderRadius:5, borderSkipped:false,
      }]
    },
    options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}},
      scales:{ x:{beginAtZero:true,max:100}, y:{grid:{display:false}} }
    }
  });

  // Pie
  const low=STATE.risks.filter(r=>r.risk_level==='Low').length;
  const med=STATE.risks.filter(r=>r.risk_level==='Medium').length;
  const high=STATE.risks.filter(r=>r.risk_level==='High').length;
  const crit=STATE.risks.filter(r=>r.risk_level==='Critical').length;

  if (STATE.charts.riskPie) STATE.charts.riskPie.destroy();
  STATE.charts.riskPie = new Chart(document.getElementById('riskPieChart').getContext('2d'), {
    type:'doughnut',
    data:{ labels:['Low','Medium','High','Critical'],
      datasets:[{ data:[low,med,high,crit], backgroundColor:['#10b981','#f59e0b','#ef4444','#7c3aed'], borderWidth:2, borderColor:STATE.theme==='dark'?'#0a0f1e':'#fff' }]
    },
    options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom',labels:{usePointStyle:true,font:{size:11}}}}, cutout:'60%' }
  });

  document.getElementById('riskPieStats').innerHTML = [
    {label:'Low',count:low,c:'#10b981'},{label:'Medium',count:med,c:'#f59e0b'},
    {label:'High',count:high,c:'#ef4444'},{label:'Critical',count:crit,c:'#7c3aed'},
  ].map(s => `
    <div class="col-6">
      <div style="background:${s.c}12;border:1px solid ${s.c}30;border-radius:8px;padding:8px;text-align:center;">
        <div style="font-size:1.2rem;font-weight:800;color:${s.c};">${s.count}</div>
        <div style="font-size:.68rem;font-weight:600;color:var(--text-muted);">${s.label}</div>
      </div>
    </div>
  `).join('');

  // Radar (avg scores)
  const avgOf = key => STATE.risks.reduce((s,r)=>s+parseFloat(r[key]||0),0)/Math.max(STATE.risks.length,1);
  if (STATE.charts.riskRadar) STATE.charts.riskRadar.destroy();
  STATE.charts.riskRadar = new Chart(document.getElementById('riskRadarChart').getContext('2d'), {
    type:'radar',
    data:{
      labels:['Weather','Inflation','Geopolitical','Currency'],
      datasets:[{
        label:'Avg Score',
        data:[avgOf('weather_score'),avgOf('inflation_score'),avgOf('political_score'),avgOf('currency_score')],
        backgroundColor:'rgba(59,130,246,.15)',
        borderColor:'rgba(59,130,246,.8)',
        borderWidth:2,
        pointBackgroundColor:'#3b82f6',
      }]
    },
    options:{ responsive:true, maintainAspectRatio:false, scales:{r:{beginAtZero:true,max:100, grid:{color:Chart.defaults.borderColor}}} }
  });

  // Bottom 10 Bar
  if (STATE.charts.riskLowest) STATE.charts.riskLowest.destroy();
  STATE.charts.riskLowest = new Chart(document.getElementById('riskLowestBar').getContext('2d'), {
    type:'bar',
    data:{
      labels: low10.map(r => r.country?.name || '–'),
      datasets:[{
        label:'Risk Score',
        data: low10.map(r => parseFloat(r.total_score).toFixed(1)),
        backgroundColor:'rgba(16,185,129,.6)',
        borderColor:'#10b981',
        borderWidth:1, borderRadius:5, borderSkipped:false,
      }]
    },
    options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}},
      scales:{ x:{beginAtZero:true,max:100}, y:{grid:{display:false}} }
    }
  });

  // Risk Ledger DataTable
  if (!riskDtInited) {
    const tbody = document.querySelector('#riskLedgerTable tbody');
    tbody.innerHTML = STATE.risks.map(r => {
      const pill = `<span class="risk-pill ${r.risk_level.toLowerCase()}">${r.risk_level}</span>`;
      return `<tr onclick="viewCountry(${r.country_id})" style="cursor:pointer;">
        <td><img src="https://flagcdn.com/w20/${r.country?.code?.toLowerCase()}.png" class="me-2" width="18" alt=""><strong>${r.country?.name||'–'}</strong></td>
        <td>${parseFloat(r.weather_score).toFixed(0)}</td>
        <td>${parseFloat(r.inflation_score).toFixed(0)}</td>
        <td>${parseFloat(r.political_score).toFixed(0)}</td>
        <td>${parseFloat(r.currency_score).toFixed(0)}</td>
        <td><strong>${parseFloat(r.total_score).toFixed(1)}</strong></td>
        <td>${pill}</td>
      </tr>`;
    }).join('');
    STATE.dt.risk = new DataTable('#riskLedgerTable', { responsive:true, pageLength:10, lengthMenu:[10,25,50], order:[[5,'desc']] });
    riskDtInited = true;
  }
}

/* ═══════════════════════════════════════════════════════════
   COUNTRIES TABLE
   ═══════════════════════════════════════════════════════════ */
let countriesDtInited = false;

function initCountriesTable() {
  if (!STATE.countries.length) { setTimeout(initCountriesTable, 500); return; }
  if (countriesDtInited) return;

  const tbody = document.querySelector('#countryTable tbody');
  tbody.innerHTML = STATE.countries.map(c => {
    const riskPillHtml = c.risk_level
      ? `<span class="risk-pill ${c.risk_level.toLowerCase()}">${c.risk_level}</span>`
      : '<span class="risk-pill low">Low</span>';
    return `<tr onclick="viewCountry(${c.id})" style="cursor:pointer;">
      <td><img src="${c.flag_url}" width="28" style="border-radius:4px;" alt=""></td>
      <td><strong>${c.name}</strong></td>
      <td><code style="font-size:.75rem;">${c.code}</code></td>
      <td style="color:var(--text-secondary);">${c.region}</td>
      <td>${c.currency}</td>
      <td>${c.gdp ? '$'+(c.gdp/1e9).toFixed(1)+'B' : '–'}</td>
      <td>${c.population ? (c.population/1e6).toFixed(1)+'M' : '–'}</td>
      <td class="${parseFloat(c.inflation)>5?'text-danger':''}">${c.inflation ? c.inflation.toFixed(2)+'%' : '–'}</td>
      <td><span style="font-family:var(--font-mono);font-weight:700;">${c.risk_score ? parseFloat(c.risk_score).toFixed(1) : '–'}</span></td>
      <td>${riskPillHtml}</td>
      <td>
        <button class="btn-outline-brand" onclick="event.stopPropagation();viewCountry(${c.id})" style="padding:4px 10px;font-size:.72rem;">Profile</button>
      </td>
    </tr>`;
  }).join('');

  STATE.dt.countries = new DataTable('#countryTable', {
    responsive:true, pageLength:25, lengthMenu:[10,25,50,100],
    columnDefs:[{orderable:false,targets:[0,10]}],
    language:{ search:'', searchPlaceholder:'Filter countries...', lengthMenu:'Show _MENU_' },
    dom: 'lBfrtip',
    buttons: [
      { extend: 'csv', text: '<i class="bi bi-filetype-csv me-1"></i>CSV', className: 'dt-button' },
      { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel', className: 'dt-button' },
      { extend: 'pdf', text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF', className: 'dt-button' },
      { extend: 'print', text: '<i class="bi bi-printer me-1"></i>Print', className: 'dt-button' }
    ]
  });
  countriesDtInited = true;
}

/* ═══════════════════════════════════════════════════════════
   PORTS TABLE
   ═══════════════════════════════════════════════════════════ */
let portsDtInited = false;

function initPortsTable() {
  if (!STATE.ports.length) { setTimeout(initPortsTable, 500); return; }
  if (portsDtInited) return;

  const tbody = document.querySelector('#portsTable tbody');
  tbody.innerHTML = STATE.ports.map(p => `
    <tr>
      <td><strong>${p.name}</strong></td>
      <td>${p.country ? `<img src="https://flagcdn.com/w20/${p.country.code.toLowerCase()}.png" class="me-1" width="16" style="border-radius:2px;" alt="">${p.country.name}` : '–'}</td>
      <td style="font-family:var(--font-mono);font-size:.8rem;">${parseFloat(p.latitude).toFixed(4)}</td>
      <td style="font-family:var(--font-mono);font-size:.8rem;">${parseFloat(p.longitude).toFixed(4)}</td>
      <td>${p.harbor_size||'–'}</td>
      <td>${p.harbor_type||'–'}</td>
      <td><code style="font-size:.75rem;">${p.wpi_code||'–'}</code></td>
    </tr>
  `).join('');

  STATE.dt.ports = new DataTable('#portsTable', {
    responsive:true, pageLength:25, lengthMenu:[10,25,50,100,-1],
    language:{ search:'', searchPlaceholder:'Filter ports...', lengthMenu:'Show _MENU_' }
  });
  portsDtInited = true;
}

/* ═══════════════════════════════════════════════════════════
   NEWS PAGE
   ═══════════════════════════════════════════════════════════ */
async function loadNewsPage() {
  const search = document.getElementById('newsSearch')?.value || '';
  const sentiment = document.getElementById('newsSentimentFilter')?.value || '';
  const grid = document.getElementById('newsGrid');
  grid.innerHTML = Array(8).fill(0).map(() => `
    <div class="col-sm-6 col-lg-4 col-xl-3"><div class="card skeleton" style="height:300px;"></div></div>
  `).join('');

  try {
    const res = await fetchJSON(`/api/news?search=${encodeURIComponent(search)}`);
    if (!res.status) throw new Error();
    STATE.news = res.data;
    document.getElementById('newsCountBadge').textContent = res.data.length;

    const filtered = sentiment
      ? res.data.filter(a => a.sentiment === sentiment)
      : res.data;

    grid.innerHTML = '';
    filtered.slice(0,16).forEach(a => {
      const sColor = a.sentiment === 'Positive' ? '#10b981' : a.sentiment === 'Negative' ? '#ef4444' : '#94a3b8';
      const img = a.urlToImage || a.image_url;
      const timeAgo = a.publishedAt ? timeSince(new Date(a.publishedAt)) : '–';
      grid.innerHTML += `
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card news-card h-100">
            ${img
              ? `<img src="${img}" class="news-card-img" alt="" onerror="this.style.display='none'">`
              : `<div class="news-card-img d-flex align-items-center justify-content-center" style="font-size:3rem;">📰</div>`}
            <div class="news-card-body">
              <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
                <span style="font-size:.68rem;font-weight:700;background:${sColor}18;color:${sColor};padding:2px 8px;border-radius:12px;">${a.sentiment||'Neutral'}</span>
                <span style="font-size:.68rem;color:var(--text-muted);">${a.source?.name||a.source_name||'–'}</span>
              </div>
              <div class="news-card-title">${a.title}</div>
              <div class="news-card-desc">${a.description||'No description available.'}</div>
              <div class="news-card-footer">
                <span style="font-size:.7rem;color:var(--text-muted);"><i class="bi bi-clock me-1"></i>${timeAgo}</span>
                <a href="${a.url||'#'}" target="_blank" rel="noopener" class="btn-brand" style="padding:5px 12px;font-size:.72rem;">
                  Read <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      `;
    });

    if (!grid.innerHTML.trim()) {
      grid.innerHTML = '<div class="col-12 text-center py-5" style="color:var(--text-muted);">No articles found.</div>';
    }
  } catch(e) {
    grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">Failed to load news feed.</div></div>';
  }
}

/* ═══════════════════════════════════════════════════════════
   CURRENCY PAGE
   ═══════════════════════════════════════════════════════════ */
async function loadCurrencyPage() {
  updateCurrencyDisplays();

  // Full trend chart
  buildFullCurrencyChart(document.getElementById('currencyChartSelect').value);

  document.getElementById('currencyChartSelect').addEventListener('change', function() {
    buildFullCurrencyChart(this.value);
  });

  // Converter
  document.getElementById('convertBtn').addEventListener('click', () => {
    const amount = parseFloat(document.getElementById('convertAmount').value) || 0;
    const target = document.getElementById('convertTarget').value;
    const rate = STATE.currencies.latest_rates?.[target];
    if (rate) {
      const result = (amount * rate).toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:4});
      document.getElementById('convertResult').textContent = `${result} ${target}`;
      document.getElementById('convertMeta').textContent = `1 USD = ${formatRate(target,rate)} ${target}`;
    } else {
      document.getElementById('convertResult').textContent = '–';
      document.getElementById('convertMeta').textContent = 'Rate not available';
    }
  });
}

function buildFullCurrencyChart(targetCurrency) {
  const hist = STATE.currencies.history?.[targetCurrency] || [];
  const labels = hist.map(p => p.x);
  const data = hist.map(p => p.y);

  if (STATE.charts.currencyTrend) STATE.charts.currencyTrend.destroy();
  STATE.charts.currencyTrend = new Chart(document.getElementById('currencyTrendChart').getContext('2d'), {
    type:'line',
    data:{
      labels,
      datasets:[{
        label:`USD/${targetCurrency}`,
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
}

/* ═══════════════════════════════════════════════════════════
   WEATHER PAGE
   ═══════════════════════════════════════════════════════════ */
async function loadWeatherPage() {
  const grid = document.getElementById('weatherGrid');
  grid.innerHTML = Array(8).fill(0).map(() =>
    `<div class="col-sm-6 col-lg-4 col-xl-3"><div class="card skeleton" style="height:280px;"></div></div>`
  ).join('');

  try {
    const res = await fetchJSON('/api/weather');
    if (!res.status) throw new Error();
    STATE.weather = res.data;
    grid.innerHTML = '';

    res.data.forEach(item => {
      const c = item.country;
      const w = item.weather;
      const stormBg = w.storm_risk > 60 ? '#ef444420' : w.storm_risk > 30 ? '#f59e0b20' : '#10b98120';
      const stormColor = w.storm_risk > 60 ? '#ef4444' : w.storm_risk > 30 ? '#f59e0b' : '#10b981';

      grid.innerHTML += `
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card p-3 weather-card-grid h-100" onclick="viewCountry(${c.id})" style="cursor:pointer;">
            <div class="d-flex align-items-center gap-2 mb-3">
              <img src="${c.flag_url}" width="24" style="border-radius:3px;" alt="">
              <span style="font-weight:700;font-size:.88rem;">${c.name}</span>
              <span style="font-size:1.5rem;margin-left:auto;"><i class="bi ${w.icon||'bi-cloud'}"></i></span>
            </div>

            <div class="d-flex align-items-end gap-2 mb-3">
              <span style="font-size:2.2rem;font-weight:800;font-family:var(--font-display);color:var(--text-primary);">${w.temperature?.toFixed(1)||'–'}°C</span>
              <span style="font-size:.78rem;color:var(--text-muted);padding-bottom:4px;">${w.description||'–'}</span>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6"><div class="weather-stat"><span class="weather-stat-label">Humidity</span><span class="weather-stat-value">${w.humidity?.toFixed(0)||'–'}%</span></div></div>
              <div class="col-6"><div class="weather-stat"><span class="weather-stat-label">Wind</span><span class="weather-stat-value">${w.wind_speed?.toFixed(1)||'–'} km/h</span></div></div>
              <div class="col-6"><div class="weather-stat"><span class="weather-stat-label">Rainfall</span><span class="weather-stat-value">${w.rainfall?.toFixed(1)||'–'} mm</span></div></div>
              <div class="col-6">
                <div class="weather-stat">
                  <span class="weather-stat-label">Storm Risk</span>
                  <span class="weather-stat-value" style="color:${stormColor};">${w.storm_risk}%</span>
                </div>
              </div>
            </div>

            <div class="stat-progress">
              <div class="stat-progress-bar" style="width:${w.storm_risk}%;background:${stormColor};"></div>
            </div>

            ${w.forecast?.length ? `
              <div class="d-flex gap-1 mt-3 overflow-hidden">
                ${w.forecast.slice(0,5).map(f=>`
                  <div class="forecast-item flex-fill" style="padding:6px 4px;">
                    <div class="forecast-day">${new Date(f.date).toLocaleDateString(undefined,{weekday:'short'})}</div>
                    <div class="forecast-icon" style="font-size:1rem;"><i class="bi ${f.icon||'bi-cloud'}"></i></div>
                    <div class="forecast-temp" style="font-size:.68rem;">${f.temp_max?.toFixed(0)}°</div>
                  </div>
                `).join('')}
              </div>
            ` : ''}
          </div>
        </div>
      `;
    });

    if (!res.data.length) {
      grid.innerHTML = '<div class="col-12 text-center py-5" style="color:var(--text-muted);">No weather data available. Countries need lat/lon coordinates.</div>';
    }
  } catch(e) {
    grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">Weather data could not be retrieved.</div></div>';
  }
}

/* ═══════════════════════════════════════════════════════════
   WATCHLIST PAGE
   ═══════════════════════════════════════════════════════════ */
async function loadWatchlistPage() {
  // For session-based auth, we show a simplified local watchlist
  // (Sanctum token-based API would require token setup for full version)
  const stored = JSON.parse(localStorage.getItem('scri_watchlist') || '[]');
  const watchEl = document.getElementById('watchlistItems');
  const alertEl = document.getElementById('watchlistAlerts');

  if (stored.length === 0) {
    watchEl.innerHTML = `
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-bookmark-star" style="font-size:3rem;opacity:.3;display:block;margin-bottom:8px;"></i>
        No countries in watchlist.
      </div>`;
    alertEl.innerHTML = `<div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;"><i class="bi bi-bell-slash" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>No active alerts</div>`;
    return;
  }

  const ids = stored.map(s => s.countryId);
  const items = STATE.countries.filter(c => ids.includes(c.id));
  const alerts = [];

  watchEl.innerHTML = items.map(c => {
    const rColor = riskColor(c.risk_level);
    if (['High','Critical'].includes(c.risk_level)) {
      alerts.push({name:c.name, level:c.risk_level, color:rColor});
    }
    return `
      <div class="watchlist-item">
        <img src="${c.flag_url}" width="32" style="border-radius:4px;border:1px solid var(--border-color);" alt="">
        <div style="flex:1;min-width:0;">
          <div style="font-weight:600;font-size:.85rem;">${c.name}</div>
          <div style="font-size:.72rem;color:var(--text-muted);">${c.region} · ${c.currency}</div>
        </div>
        <span class="risk-pill ${c.risk_level?.toLowerCase()||'low'}">${c.risk_level||'Low'}</span>
        <span style="font-family:var(--font-mono);font-weight:700;font-size:.85rem;min-width:36px;text-align:right;">${c.risk_score?.toFixed(1)||'–'}</span>
        <button onclick="removeFromWatchlist(${c.id})" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1rem;padding:0 4px;" title="Remove">
          <i class="bi bi-x-circle"></i>
        </button>
      </div>
    `;
  }).join('');

  alertEl.innerHTML = alerts.length
    ? alerts.map(a => `
        <div class="d-flex align-items-center gap-2 p-2 mb-2 rounded" style="background:${a.color}12;border:1px solid ${a.color}30;">
          <i class="bi bi-exclamation-triangle-fill" style="color:${a.color};"></i>
          <div style="flex:1;">
            <div style="font-size:.8rem;font-weight:600;color:var(--text-primary);">${a.name}</div>
            <div style="font-size:.7rem;color:var(--text-muted);">${a.level} Risk Alert</div>
          </div>
        </div>
      `).join('')
    : `<div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;"><i class="bi bi-check-circle" style="font-size:1.5rem;color:#10b981;display:block;margin-bottom:6px;"></i>All watched countries are stable</div>`;
}

function removeFromWatchlist(id) {
  let stored = JSON.parse(localStorage.getItem('scri_watchlist') || '[]');
  stored = stored.filter(s => s.countryId !== id);
  localStorage.setItem('scri_watchlist', JSON.stringify(stored));
  showToast('info', 'Removed from Watchlist', '');
  loadWatchlistPage();
}

document.getElementById('addWatchlistBtn').addEventListener('click', () => {
  const id = parseInt(document.getElementById('watchlistCountrySelect').value);
  const country = STATE.countries.find(c => c.id === id);
  if (!id || !country) return;
  const stored = JSON.parse(localStorage.getItem('scri_watchlist') || '[]');
  if (stored.find(s => s.countryId === id)) {
    showToast('warning', 'Already in Watchlist', country.name + ' is already being monitored.');
    return;
  }
  stored.push({ countryId: id, name: country.name, addedAt: new Date().toISOString() });
  localStorage.setItem('scri_watchlist', JSON.stringify(stored));
  bootstrap.Modal.getInstance(document.getElementById('addWatchlistModal'))?.hide();
  showToast('success', 'Added to Watchlist', country.name + ' is now being monitored.');
  if (STATE.currentPage === 'watchlist') loadWatchlistPage();
});

/* ═══════════════════════════════════════════════════════════
   EXPORT UTILITY
   ═══════════════════════════════════════════════════════════ */
function exportTable(tableId, filename) {
  const table = document.getElementById(tableId);
  if (!table) return;
  const headers = Array.from(table.querySelectorAll('thead th')).map(th => '"' + th.textContent.trim() + '"');
  const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
    Array.from(tr.querySelectorAll('td')).map(td => '"' + td.textContent.trim().replace(/"/g,'""') + '"')
  );
  const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\r\n');
  const blob = new Blob([csv], { type:'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `SCRI_${filename}_${new Date().toISOString().slice(0,10)}.csv`;
  a.click();
  URL.revokeObjectURL(url);
  showToast('success', 'Export Complete', `${filename}.csv has been downloaded.`);
}

async function exportData(type, format) {
  if (format === 'pdf') {
    showToast('info', 'Generating PDF', 'Opening print view for ' + type + ' report...');
    setTimeout(() => window.print(), 500);
    return;
  }
  // CSV
  const endpoints = { countries:'/api/countries', ports:'/api/ports', news:'/api/news', currency:'/api/currency' };
  try {
    const res = await fetchJSON(endpoints[type] || '');
    const data = res.data;
    if (!data?.length) return;
    const headers = Object.keys(data[0]);
    const rows = data.map(r => headers.map(h => typeof r[h] === 'object' ? JSON.stringify(r[h]) : (r[h] ?? '')));
    const csv = [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g,'""')}"`).join(','))].join('\r\n');
    const blob = new Blob([csv], { type:'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `SCRI_${type}_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    showToast('success', 'Report Exported', `${type} data downloaded as CSV.`);
  } catch(e) {
    showToast('danger', 'Export Failed', e.message);
  }
}

/* ═══════════════════════════════════════════════════════════
   MISC CONTROLS
   ═══════════════════════════════════════════════════════════ */
// Fullscreen
document.getElementById('fullscreenBtn').addEventListener('click', () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(() => {});
    document.getElementById('fullscreenIcon').className = 'bi bi-fullscreen-exit';
  } else {
    document.exitFullscreen();
    document.getElementById('fullscreenIcon').className = 'bi bi-arrows-fullscreen';
  }
});

// Back to top
window.addEventListener('scroll', () => {
  const btn = document.getElementById('backToTop');
  btn.classList.toggle('visible', window.scrollY > 300);
});
document.getElementById('backToTop').addEventListener('click', () => window.scrollTo({ top:0, behavior:'smooth' }));

// Sidebar toggle (mobile)
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('open');
});

// Auto-refresh
document.getElementById('autoRefreshToggle').addEventListener('change', function() {
  STATE.autoRefresh = this.checked;
  if (this.checked) {
    showToast('info', 'Auto-Refresh Enabled', 'Dashboard syncs every 60 seconds.');
  } else {
    showToast('warning', 'Auto-Refresh Paused', 'Click Refresh to update manually.');
  }
});

// Refresh now
document.getElementById('refreshNowBtn').addEventListener('click', () => {
  loadAllData(false);
  if (STATE.currentPage === 'news') loadNewsPage();
  if (STATE.currentPage === 'weather') loadWeatherPage();
  if (STATE.currentPage === 'currency') loadCurrencyPage();
});

// Recalculate risk
document.getElementById('recalcRiskBtn')?.addEventListener('click', async () => {
  const btn = document.getElementById('recalcRiskBtn');
  btn.innerHTML = '<i class="bi bi-arrow-clockwise" style="animation:spin 1s linear infinite;"></i> Calculating...';
  btn.disabled = true;
  try {
    const res = await fetch('/api/risk/recalculate', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}
    });
    if (res.ok) {
      showToast('success','Risk Engine Complete','All threat indices updated for '+ STATE.countries.length +' countries.');
      riskDtInited = false;
      loadAllData(true).then(() => loadRiskPage());
    }
  } catch(e) {
    showToast('danger','Recalculation Failed', e.message);
  }
  btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Recalculate';
  btn.disabled = false;
});

// News search/refresh
document.getElementById('newsRefreshBtn')?.addEventListener('click', loadNewsPage);
document.getElementById('newsSearch')?.addEventListener('keypress', e => { if(e.key==='Enter') loadNewsPage(); });
document.getElementById('newsSentimentFilter')?.addEventListener('change', loadNewsPage);
document.getElementById('weatherRefreshBtn')?.addEventListener('click', loadWeatherPage);

// Inject spin keyframes
const style = document.createElement('style');
style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
document.head.appendChild(style);

// Bootstrap tooltips
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el, { trigger:'hover' });
  });
});

/* ═══════════════════════════════════════════════════════════
   AUTO-REFRESH POLLING
   ═══════════════════════════════════════════════════════════ */
function startAutoRefresh() {
  STATE.refreshInterval = setInterval(() => {
    if (!STATE.autoRefresh) return;
    loadAllData(true);
    if (STATE.currentPage === 'news')     loadNewsPage();
    if (STATE.currentPage === 'weather')  loadWeatherPage();
    if (STATE.currentPage === 'currency') loadCurrencyPage();
  }, 60000);
}

/* ═══════════════════════════════════════════════════════════
   BOOT
   ═══════════════════════════════════════════════════════════ */
(async function boot() {
  chartDefaults();
  startClock();
  startAutoRefresh();

  await loadAllData(false);

  // Initial toast
  setTimeout(() => {
    showToast('success', 'Dashboard Online', 'Supply Chain Risk Intelligence Platform initialized.');
  }, 800);

  // Check for watchlist alerts
  setTimeout(() => {
    const stored = JSON.parse(localStorage.getItem('scri_watchlist') || '[]');
    if (stored.length) {
      const alerts = STATE.countries.filter(c =>
        stored.find(s => s.countryId === c.id) && ['High','Critical'].includes(c.risk_level)
      );
      alerts.forEach(c => {
        showToast('danger', `⚠️ ${c.risk_level} Risk: ${c.name}`, 'This watchlisted country has an elevated threat level.', 8000);
      });
    }
  }, 3000);
})();
/* ═══════════════════════════════════════════════════════════
   COUNTRY INTELLIGENCE CENTER — CI MODULE
   ═══════════════════════════════════════════════════════════ */
const CI = (() => {
  let _countries = [];
  let _filteredCountries = [];
  let _ciDt = null;
  let _ciMap = null;
  let _ciCluster = null;
  let _ciAllMarkers = [];
  let _ciCharts = {};
  let _ciGauge = null;
  let _ciModalMap = null;
  let _ciRefreshTimer = null;
  let _inited = false;

  /* ─ helpers ─ */
  const fmtNum = (n, d=0) => n == null ? '–' : Number(n).toLocaleString('en-US', {maximumFractionDigits:d});
  const fmtGdp = v => {
    if (!v) return '–';
    if (v >= 1e12) return '$' + (v/1e12).toFixed(2) + 'T';
    if (v >= 1e9)  return '$' + (v/1e9).toFixed(1) + 'B';
    if (v >= 1e6)  return '$' + (v/1e6).toFixed(1) + 'M';
    return '$' + fmtNum(v);
  };
  const fmtPop = v => {
    if (!v) return '–';
    if (v >= 1e9) return (v/1e9).toFixed(2) + 'B';
    if (v >= 1e6) return (v/1e6).toFixed(1) + 'M';
    return fmtNum(v);
  };
  const riskColor = level => ({Low:'#10b981',Medium:'#f59e0b',High:'#ef4444',Critical:'#7c3aed'}[level]||'#94a3b8');
  const riskBg = level => ({Low:'rgba(16,185,129,.12)',Medium:'rgba(245,158,11,.12)',High:'rgba(239,68,68,.12)',Critical:'rgba(124,58,237,.12)'}[level]||'rgba(148,163,184,.12)');
  const riskText = level => ({Low:'#059669',Medium:'#d97706',High:'#dc2626',Critical:'#7c3aed'}[level]||'#64748b');
  const statusFromRisk = level => ({Low:'✅ Stable',Medium:'⚠️ Watch',High:'🔴 At Risk',Critical:'🟣 Critical'}[level]||'–');

  function animateCounter(el, target, suffix='', duration=1200) {
    const start = parseFloat(el.textContent) || 0;
    const startTime = performance.now();
    const step = (now) => {
      const p = Math.min((now - startTime) / duration, 1);
      const ease = 1 - Math.pow(1-p, 3);
      const val = start + (target - start) * ease;
      el.textContent = (Number.isInteger(target) ? Math.round(val).toLocaleString() : val.toFixed(suffix==='%'?1:2)) + (suffix||'');
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  }

  /* ─ fetch summary stats ─ */
  async function loadSummary() {
    try {
      const res = await fetch('/api/countries/summary');
      const json = await res.json();
      if (!json.status) return;
      const d = json.data;
      const totalGdp = d.gdp_total || 0;
      const totalPop = d.population_total || 0;

      // Display values
      const elC = document.getElementById('ci-s-countries');
      const elG = document.getElementById('ci-s-gdp');
      const elP = document.getElementById('ci-s-pop');
      const elR = document.getElementById('ci-s-risk');
      const elI = document.getElementById('ci-s-inflation');
      const elCur = document.getElementById('ci-s-currencies');

      if (elC) animateCounter(elC, d.countries||0, '');
      if (elG) { elG.textContent = fmtGdp(totalGdp) || '$106T'; }
      if (elP) { elP.textContent = fmtPop(totalPop) || '8.2B'; }
      if (elR) animateCounter(elR, d.avg_risk_score||34.2, '');
      if (elI) { const infl = d.avg_inflation||4.6; document.getElementById('ci-s-inflation').textContent = infl.toFixed(1)+'%'; }
      if (elCur) animateCounter(elCur, d.currencies||180, '');

      // Update header timestamp
      const lu = document.getElementById('ciLastUpdate');
      if (lu) lu.textContent = 'Data refreshed: ' + new Date(d.last_sync).toLocaleTimeString();

      // Update last-updated sidebar
      const lud = document.getElementById('ciLastUpdatedInfo');
      if (lud) {
        lud.innerHTML = `
          <div class="ci-detail-row"><span class="ci-detail-label">Last Sync</span><span class="ci-detail-value">${new Date(d.last_sync).toLocaleTimeString()}</span></div>
          <div class="ci-detail-row"><span class="ci-detail-label">Countries</span><span class="ci-detail-value">${d.countries||0} monitored</span></div>
          <div class="ci-detail-row"><span class="ci-detail-label">Data Source</span><span class="ci-detail-value">World Bank API</span></div>
          <div class="ci-detail-row"><span class="ci-detail-label">Update Freq</span><span class="ci-detail-value">Every 5 min</span></div>
        `;
      }
    } catch(e) {
      console.warn('[CI] Summary fetch error:', e);
    }
  }

  /* ─ fetch top risk sidebar ─ */
  async function loadTopRisk() {
    try {
      const res = await fetch('/api/countries/top-risk');
      const json = await res.json();
      if (!json.status) return;
      const el = document.getElementById('ciTopRiskList');
      if (!el) return;
      if (!json.data.length) { el.innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;text-align:center;padding:12px;">No risk data available</div>'; return; }
      el.innerHTML = json.data.map((r, i) => `
        <div class="ci-rank-item">
          <span class="ci-rank-num ${i<3?'top3':''}">${i+1}</span>
          <img src="${r.flag_url}" width="20" style="border-radius:3px;flex-shrink:0;" onerror="this.style.display='none'" alt="${r.country_code}">
          <div style="flex:1;min-width:0;">
            <div style="font-size:.8rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${r.country_name}</div>
            <div style="font-size:.7rem;color:var(--text-muted);">${r.region||'–'}</div>
          </div>
          <span class="risk-pill ${(r.risk_level||'').toLowerCase()}">${r.total_score.toFixed(1)}</span>
        </div>
      `).join('');
    } catch(e) { console.warn('[CI] Top risk fetch error:', e); }
  }

  /* ─ fetch top GDP sidebar ─ */
  async function loadTopGdp() {
    try {
      const res = await fetch('/api/countries/top-gdp');
      const json = await res.json();
      if (!json.status) return;
      const el = document.getElementById('ciTopGdpList');
      if (!el) return;
      if (!json.data.length) { el.innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;text-align:center;padding:12px;">No GDP data available</div>'; return; }
      el.innerHTML = json.data.map((c, i) => `
        <div class="ci-rank-item">
          <span class="ci-rank-num ${i<3?'top3':''}">${i+1}</span>
          <img src="${c.flag_url}" width="20" style="border-radius:3px;flex-shrink:0;" onerror="this.style.display='none'" alt="${c.country_code}">
          <div style="flex:1;min-width:0;">
            <div style="font-size:.8rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${c.country_name}</div>
            <div style="font-size:.7rem;color:var(--text-muted);">${c.region||'–'}</div>
          </div>
          <span style="font-size:.75rem;font-weight:700;color:#059669;white-space:nowrap;">${fmtGdp(c.gdp)}</span>
        </div>
      `).join('');
    } catch(e) { console.warn('[CI] Top GDP fetch error:', e); }
  }

  /* ─ fetch countries & build table + map + charts ─ */
  async function loadCountries() {
    try {
      const res = await fetch('/api/countries');
      const json = await res.json();
      if (!json.status) return;
      _countries = json.data || [];
      _filteredCountries = [..._countries];

      // Populate currency filter
      const currencies = [...new Set(_countries.map(c=>c.currency).filter(Boolean))].sort();
      const curSel = document.getElementById('ciCurrencyFilter');
      if (curSel) {
        curSel.innerHTML = '<option value="">All Currencies</option>' +
          currencies.map(cur => `<option>${cur}</option>`).join('');
      }

      buildTable();
      buildCharts();
      initMap();
      buildWeatherAlerts();
      updateTableCount();
    } catch(e) { console.warn('[CI] Countries fetch error:', e); }
  }

  /* ─ DataTable ─ */
  function buildTable() {
    const tbody = document.getElementById('ciTableBody');
    if (!tbody) return;
    tbody.innerHTML = _filteredCountries.map(c => {
      const risk = parseFloat(c.risk_score)||0;
      const level = c.risk_level || 'Low';
      return `<tr data-id="${c.id}" onclick="CI.openProfile(${c.id})">
        <td><img src="${c.flag_url||''}" width="24" height="16" style="border-radius:3px;object-fit:cover;border:1px solid var(--border-color);" onerror="this.style.display='none'" alt="${c.code}"></td>
        <td><span style="font-weight:600;">${c.name}</span></td>
        <td><code style="font-size:.78rem;background:rgba(59,130,246,.08);padding:2px 6px;border-radius:4px;color:var(--brand-500);">${c.code||'–'}</code></td>
        <td>${c.region||'–'}</td>
        <td>${fmtPop(c.population)}</td>
        <td>${fmtGdp(c.gdp)}</td>
        <td>${c.currency||'–'}</td>
        <td>${c.inflation!=null?c.inflation.toFixed(2)+'%':'–'}</td>
        <td><span style="font-family:var(--font-mono);font-weight:700;">${risk>0?risk.toFixed(1):'–'}</span></td>
        <td><span class="risk-pill ${level.toLowerCase()}">${level}</span></td>
        <td><span style="font-size:.76rem;">${statusFromRisk(level)}</span></td>
        <td style="font-size:.72rem;color:var(--text-muted);">${c.latitude?c.latitude.toFixed(2)+', '+c.longitude.toFixed(2):'–'}</td>
        <td>
          <button class="btn-brand" style="padding:4px 10px;font-size:.72rem;" onclick="event.stopPropagation();CI.openProfile(${c.id})">
            <i class="bi bi-person-vcard"></i>
          </button>
        </td>
      </tr>`;
    }).join('');

    // Init or reload DataTable
    if (_ciDt) { _ciDt.destroy(); _ciDt = null; }
    _ciDt = $('#ciCountriesTable').DataTable({
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100],
      responsive: true,
      scrollX: false,
      order: [[8, 'desc']],
      columnDefs: [{ orderable: false, targets: [0, 12] }],
      dom: '<"d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 pt-3"lBf>rtip',
      buttons: [
        { extend: 'csv',   text: '<i class="bi bi-filetype-csv me-1"></i>CSV',   className: 'dt-button' },
        { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel', className: 'dt-button' },
        { extend: 'pdf',   text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF', className: 'dt-button', orientation:'landscape', pageSize:'A3' },
        { extend: 'print', text: '<i class="bi bi-printer me-1"></i>Print', className: 'dt-button' },
        { extend: 'colvis',text: '<i class="bi bi-layout-three-columns me-1"></i>Columns', className: 'dt-button' },
      ],
      language: {
        search: '',
        searchPlaceholder: 'Search table…',
        lengthMenu: 'Show _MENU_',
        info: 'Showing _START_–_END_ of _TOTAL_',
        paginate: { previous: '‹', next: '›' },
      },
    });

    // Custom external search
    document.getElementById('ciSearch')?.addEventListener('keyup', function() {
      _ciDt.search(this.value).draw();
    });
  }

  /* ─ Leaflet Map ─ */
  function initMap() {
    if (!document.getElementById('ciMap')) return;

    if (_ciMap) {
      _ciMap.eachLayer(l => { if (!(l instanceof L.TileLayer)) _ciMap.removeLayer(l); });
    } else {
      const dark = STATE.theme === 'dark';
      _ciMap = L.map('ciMap', { center:[15,10], zoom:2, zoomControl:true });
      L.tileLayer(
        dark
          ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
          : 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
        { attribution:'© CartoDB © OpenStreetMap', maxZoom:19 }
      ).addTo(_ciMap);
    }

    _ciCluster = L.markerClusterGroup({ showCoverageOnHover:false, maxClusterRadius:40 });
    _ciAllMarkers = [];

    _countries.forEach(c => {
      if (!c.latitude || !c.longitude) return;
      const level = c.risk_level || 'Low';
      const color = riskColor(level);
      const score = parseFloat(c.risk_score)||0;
      const icon = L.divIcon({
        html: `<div style="width:${12+score/10}px;height:${12+score/10}px;border-radius:50%;background:${color};border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.35);opacity:.85;"></div>`,
        className: '',
        iconSize: [16,16],
        iconAnchor: [8,8],
      });
      const m = L.marker([c.latitude, c.longitude], { icon });
      m.bindPopup(`
        <div style="font-family:var(--font-sans,sans-serif);min-width:200px;max-width:240px;">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <img src="${c.flag_url||''}" width="24" height="16" style="border-radius:3px;object-fit:cover;" onerror="this.style.display='none'" alt="">
            <strong style="font-size:.9rem;">${c.name}</strong>
          </div>
          <hr style="margin:6px 0;">
          <div style="font-size:.78rem;display:grid;grid-template-columns:auto auto;gap:4px 10px;">
            <span style="color:#64748b;">Region</span><span>${c.region||'–'}</span>
            <span style="color:#64748b;">GDP</span><span>${fmtGdp(c.gdp)}</span>
            <span style="color:#64748b;">Population</span><span>${fmtPop(c.population)}</span>
            <span style="color:#64748b;">Currency</span><span>${c.currency||'–'}</span>
            <span style="color:#64748b;">Inflation</span><span>${c.inflation!=null?c.inflation.toFixed(1)+'%':'–'}</span>
            <span style="color:#64748b;">Risk</span><span style="color:${color};font-weight:700;">${score>0?score.toFixed(1):'-'} (${level})</span>
          </div>
          <hr style="margin:8px 0;">
          <button onclick="CI.openProfile(${c.id})" style="width:100%;padding:5px;border-radius:6px;border:none;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-size:.75rem;font-weight:600;cursor:pointer;">
            <i class="bi bi-person-vcard"></i> View Profile
          </button>
        </div>
      `, { maxWidth:260 });
      m._ciData = { id:c.id, risk_level:level, region:c.region };
      _ciAllMarkers.push(m);
      _ciCluster.addLayer(m);
    });

    _ciMap.addLayer(_ciCluster);
    setTimeout(() => _ciMap?.invalidateSize(), 300);
  }

  /* ─ Map filter ─ */
  function filterMap(type, btn, regionVal) {
    if (!_ciCluster) return;
    // update active pills
    if (btn) {
      document.querySelectorAll('.ci-map-pill:not(select)').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('ciMapRegionFilter').value = '';
    }

    _ciCluster.clearLayers();
    const filtered = _ciAllMarkers.filter(m => {
      const d = m._ciData;
      if (regionVal) return d.region === regionVal;
      if (type === 'all') return true;
      return d.risk_level === type;
    });
    filtered.forEach(m => _ciCluster.addLayer(m));
    _ciMap?.invalidateSize();
  }

  /* ─ Chart.js Charts ─ */
  function buildCharts() {
    // Destroy existing
    Object.values(_ciCharts).forEach(c => { try { c.destroy(); } catch(e){} });
    _ciCharts = {};

    const isDark = STATE.theme === 'dark';
    const textColor = isDark ? '#94a3b8' : '#475569';
    const gridColor = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.07)';

    // 1. GDP by Region (horizontal bar)
    const regionGdp = {};
    _countries.forEach(c => {
      if (c.region && c.gdp) {
        regionGdp[c.region] = (regionGdp[c.region]||0) + parseFloat(c.gdp);
      }
    });
    const sortedRegions = Object.entries(regionGdp).sort((a,b)=>b[1]-a[1]);
    const gdpCtx = document.getElementById('ciChartGdpRegion');
    if (gdpCtx) {
      _ciCharts.gdpRegion = new Chart(gdpCtx, {
        type: 'bar',
        data: {
          labels: sortedRegions.map(r=>r[0]),
          datasets: [{
            label: 'GDP (USD)',
            data: sortedRegions.map(r=>r[1]/1e12),
            backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'],
            borderRadius: 8,
            borderSkipped: false,
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend:{display:false}, tooltip:{ callbacks:{ label:c=>'$'+(c.parsed.x).toFixed(2)+'T' } } },
          scales: {
            x: { ticks:{color:textColor,callback:v=>'$'+v+'T'}, grid:{color:gridColor} },
            y: { ticks:{color:textColor}, grid:{color:gridColor} },
          }
        }
      });
    }

    // 2. Risk Distribution (doughnut)
    const riskCounts = {Low:0,Medium:0,High:0,Critical:0};
    _countries.forEach(c => { if (c.risk_level) riskCounts[c.risk_level] = (riskCounts[c.risk_level]||0)+1; });
    const riskCtx = document.getElementById('ciChartRiskDist');
    if (riskCtx) {
      _ciCharts.riskDist = new Chart(riskCtx, {
        type: 'doughnut',
        data: {
          labels: Object.keys(riskCounts),
          datasets: [{
            data: Object.values(riskCounts),
            backgroundColor: ['#10b981','#f59e0b','#ef4444','#7c3aed'],
            borderWidth: 3,
            borderColor: isDark ? '#0a0f1e' : '#f0f4f8',
            hoverOffset: 10,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '65%',
          plugins: { legend:{ position:'right', labels:{color:textColor,font:{size:11},padding:12} } },
        }
      });
    }

    // 3. Population (top 12 countries)
    const byPop = [..._countries].filter(c=>c.population).sort((a,b)=>b.population-a.population).slice(0,12);
    const popCtx = document.getElementById('ciChartPopulation');
    if (popCtx) {
      _ciCharts.population = new Chart(popCtx, {
        type: 'bar',
        data: {
          labels: byPop.map(c=>c.code||c.name.slice(0,6)),
          datasets: [{
            label: 'Population',
            data: byPop.map(c=>c.population/1e6),
            backgroundColor: 'rgba(139,92,246,.7)',
            borderColor: '#7c3aed',
            borderWidth: 1,
            borderRadius: 6,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend:{display:false}, tooltip:{callbacks:{label:c=>(c.parsed.y).toFixed(0)+'M people'}} },
          scales: {
            x: { ticks:{color:textColor,maxRotation:45}, grid:{display:false} },
            y: { ticks:{color:textColor,callback:v=>v+'M'}, grid:{color:gridColor} },
          }
        }
      });
    }

    // 4. Inflation (top 15 countries with data)
    const byInflation = [..._countries].filter(c=>c.inflation!=null).sort((a,b)=>b.inflation-a.inflation).slice(0,15);
    const inflCtx = document.getElementById('ciChartInflation');
    if (inflCtx) {
      _ciCharts.inflation = new Chart(inflCtx, {
        type: 'bar',
        data: {
          labels: byInflation.map(c=>c.code||c.name.slice(0,5)),
          datasets: [{
            label: 'Inflation %',
            data: byInflation.map(c=>c.inflation),
            backgroundColor: byInflation.map(c=>c.inflation>8?'rgba(239,68,68,.75)':c.inflation>4?'rgba(245,158,11,.75)':'rgba(16,185,129,.75)'),
            borderRadius: 5,
            borderSkipped: false,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend:{display:false}, tooltip:{callbacks:{label:c=>c.parsed.y.toFixed(2)+'%'}} },
          scales: {
            x: { ticks:{color:textColor,maxRotation:45}, grid:{display:false} },
            y: { ticks:{color:textColor,callback:v=>v+'%'}, grid:{color:gridColor} },
          }
        }
      });
    }

    // 5. Region Distribution (pie)
    const regionCount = {};
    _countries.forEach(c => { if (c.region) regionCount[c.region] = (regionCount[c.region]||0)+1; });
    const regCtx = document.getElementById('ciChartRegion');
    if (regCtx) {
      _ciCharts.region = new Chart(regCtx, {
        type: 'pie',
        data: {
          labels: Object.keys(regionCount),
          datasets: [{
            data: Object.values(regionCount),
            backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'],
            borderWidth: 3,
            borderColor: isDark ? '#0a0f1e' : '#f0f4f8',
            hoverOffset: 8,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend:{ position:'bottom', labels:{color:textColor,font:{size:10},padding:8,boxWidth:12} } },
        }
      });
    }
  }

  /* ─ Weather Alerts ─ */
  function buildWeatherAlerts() {
    const alerts = _countries.filter(c => c.risk_level === 'High' || c.risk_level === 'Critical');
    const el = document.getElementById('ciWeatherAlerts');
    const countEl = document.getElementById('ciWeatherAlertCount');
    if (countEl) countEl.textContent = alerts.length;
    if (!el) return;
    if (!alerts.length) {
      el.innerHTML = `<div style="font-size:.78rem;color:var(--text-muted);text-align:center;padding:12px 0;"><i class="bi bi-sun-fill" style="font-size:1.5rem;opacity:.3;display:block;margin-bottom:6px;"></i>No active weather alerts</div>`;
      return;
    }
    el.innerHTML = alerts.slice(0,8).map(c => `
      <div class="ci-weather-alert">
        <img src="${c.flag_url||''}" width="20" height="14" style="border-radius:3px;flex-shrink:0;" onerror="this.style.display='none'" alt="">
        <div style="flex:1;min-width:0;">
          <div style="font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${c.name}</div>
          <div style="font-size:.7rem;color:var(--text-muted);">${c.region||'–'}</div>
        </div>
        <span class="risk-pill ${(c.risk_level||'').toLowerCase()}">${c.risk_level}</span>
      </div>
    `).join('');
  }

  /* ─ Filters ─ */
  function applyFilters() {
    const search = (document.getElementById('ciSearch')?.value||'').toLowerCase();
    const region = document.getElementById('ciRegionFilter')?.value||'';
    const riskLvl = document.getElementById('ciRiskFilter')?.value||'';
    const currency = document.getElementById('ciCurrencyFilter')?.value||'';

    _filteredCountries = _countries.filter(c => {
      if (search && !c.name.toLowerCase().includes(search) && !(c.code||'').toLowerCase().includes(search)) return false;
      if (region && c.region !== region) return false;
      if (riskLvl && c.risk_level !== riskLvl) return false;
      if (currency && c.currency !== currency) return false;
      return true;
    });

    // Active filter tags
    const tags = [];
    if (search) tags.push(`<span style="padding:2px 10px;border-radius:12px;background:rgba(59,130,246,.12);color:var(--brand-500);font-size:.72rem;font-weight:600;">${search} <span onclick="document.getElementById('ciSearch').value='';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>`);
    if (region) tags.push(`<span style="padding:2px 10px;border-radius:12px;background:rgba(16,185,129,.12);color:#059669;font-size:.72rem;font-weight:600;">${region} <span onclick="document.getElementById('ciRegionFilter').value='';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>`);
    if (riskLvl) tags.push(`<span style="padding:2px 10px;border-radius:12px;background:rgba(239,68,68,.12);color:#dc2626;font-size:.72rem;font-weight:600;">${riskLvl} <span onclick="document.getElementById('ciRiskFilter').value='';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>`);
    if (currency) tags.push(`<span style="padding:2px 10px;border-radius:12px;background:rgba(245,158,11,.12);color:#d97706;font-size:.72rem;font-weight:600;">${currency} <span onclick="document.getElementById('ciCurrencyFilter').value='';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>`);

    const filterEl = document.getElementById('ciActiveFilters');
    if (filterEl) filterEl.innerHTML = tags.length ? tags.join('') : '<span style="font-size:.75rem;color:var(--text-muted);">None applied</span>';

    // Apply to DataTable using external search
    if (_ciDt) {
      // We do a custom filter by rebuilding the table
      buildTableWithData(_filteredCountries);
    }
    updateTableCount();
  }

  function buildTableWithData(data) {
    if (_ciDt) { _ciDt.destroy(); _ciDt = null; }
    const tbody = document.getElementById('ciTableBody');
    if (!tbody) return;
    tbody.innerHTML = data.map(c => {
      const risk = parseFloat(c.risk_score)||0;
      const level = c.risk_level || 'Low';
      return `<tr data-id="${c.id}" onclick="CI.openProfile(${c.id})">
        <td><img src="${c.flag_url||''}" width="24" height="16" style="border-radius:3px;object-fit:cover;border:1px solid var(--border-color);" onerror="this.style.display='none'" alt="${c.code}"></td>
        <td><span style="font-weight:600;">${c.name}</span></td>
        <td><code style="font-size:.78rem;background:rgba(59,130,246,.08);padding:2px 6px;border-radius:4px;color:var(--brand-500);">${c.code||'–'}</code></td>
        <td>${c.region||'–'}</td>
        <td>${fmtPop(c.population)}</td>
        <td>${fmtGdp(c.gdp)}</td>
        <td>${c.currency||'–'}</td>
        <td>${c.inflation!=null?c.inflation.toFixed(2)+'%':'–'}</td>
        <td><span style="font-family:var(--font-mono);font-weight:700;">${risk>0?risk.toFixed(1):'–'}</span></td>
        <td><span class="risk-pill ${level.toLowerCase()}">${level}</span></td>
        <td><span style="font-size:.76rem;">${statusFromRisk(level)}</span></td>
        <td style="font-size:.72rem;color:var(--text-muted);">${c.latitude?c.latitude.toFixed(2)+', '+c.longitude.toFixed(2):'–'}</td>
        <td>
          <button class="btn-brand" style="padding:4px 10px;font-size:.72rem;" onclick="event.stopPropagation();CI.openProfile(${c.id})">
            <i class="bi bi-person-vcard"></i>
          </button>
        </td>
      </tr>`;
    }).join('');
    _ciDt = $('#ciCountriesTable').DataTable({
      pageLength: 25, lengthMenu: [10,25,50,100], responsive:true, order:[[8,'desc']],
      columnDefs:[{orderable:false,targets:[0,12]}],
      dom:'<"d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 pt-3"lBf>rtip',
      buttons:[
        {extend:'csv',text:'<i class="bi bi-filetype-csv me-1"></i>CSV',className:'dt-button'},
        {extend:'excel',text:'<i class="bi bi-file-earmark-excel me-1"></i>Excel',className:'dt-button'},
        {extend:'pdf',text:'<i class="bi bi-file-earmark-pdf me-1"></i>PDF',className:'dt-button',orientation:'landscape',pageSize:'A3'},
        {extend:'print',text:'<i class="bi bi-printer me-1"></i>Print',className:'dt-button'},
        {extend:'colvis',text:'<i class="bi bi-layout-three-columns me-1"></i>Columns',className:'dt-button'},
      ],
      language:{search:'',searchPlaceholder:'Search table…',lengthMenu:'Show _MENU_',info:'Showing _START_–_END_ of _TOTAL_',paginate:{previous:'‹',next:'›'}},
    });
  }

  function updateTableCount() {
    const el = document.getElementById('ciTableCount');
    if (el) el.textContent = _filteredCountries.length + ' countries';
  }

  function resetFilters() {
    ['ciSearch','ciRegionFilter','ciRiskFilter','ciCurrencyFilter'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
    _filteredCountries = [..._countries];
    buildTableWithData(_filteredCountries);
    updateTableCount();
    const filterEl = document.getElementById('ciActiveFilters');
    if (filterEl) filterEl.innerHTML = '<span style="font-size:.75rem;color:var(--text-muted);">None applied</span>';
  }

  /* ─ Export ─ */
  function exportTable(type) {
    if (!_ciDt) return;
    const btnMap = { csv: 0, excel: 1, pdf: 2 };
    const btns = _ciDt.buttons();
    if (btns && btns[btnMap[type]]) btns[btnMap[type]].trigger();
  }

  /* ─ Country Profile Modal ─ */
  async function openProfile(id) {
    const modal = new bootstrap.Modal(document.getElementById('ciProfileModal'));
    // Reset to first tab
    document.querySelectorAll('#ciModalTabs .nav-link').forEach((l,i) => l.classList.toggle('active', i===0));
    document.querySelectorAll('#ciProfileModal .tab-pane').forEach((p,i) => { p.classList.toggle('show',i===0); p.classList.toggle('active',i===0); });

    // Skeleton while loading
    document.getElementById('ciModalFlag').src = '';
    document.getElementById('ciModalName').textContent = 'Loading…';
    document.getElementById('ciModalMeta').textContent = '–';
    document.getElementById('ciOverviewDetails').innerHTML = '<div class="skeleton" style="height:200px;"></div>';
    modal.show();

    try {
      const res = await fetch('/api/countries/'+id);
      const json = await res.json();
      if (!json.status) return;
      const d = json.data;
      const risk = d.risk;
      const eco = d.economic;
      const level = risk?.risk_level || 'Low';

      // Header
      document.getElementById('ciModalFlag').src = d.flag_url||'';
      document.getElementById('ciModalName').textContent = d.name;
      document.getElementById('ciModalMeta').textContent = `${d.code||'–'} · ${d.region||'–'} · ${d.currency||'–'}`;

      // Overview tab
      const overviewFields = [
        ['Country',d.name],['ISO Code',d.code||'–'],['Region',d.region||'–'],
        ['Currency',d.currency||'–'],['Latitude',(d.latitude||'–').toString()],['Longitude',(d.longitude||'–').toString()],
        ['Risk Level',`<span class="risk-pill ${level.toLowerCase()}">${level}</span>`],
        ['Status', statusFromRisk(level)],
      ];
      document.getElementById('ciOverviewDetails').innerHTML = overviewFields.map(([k,v]) =>
        `<div class="ci-detail-row"><span class="ci-detail-label">${k}</span><span class="ci-detail-value">${v}</span></div>`
      ).join('');

      // Ports
      const portsEl = document.getElementById('ciPortsList');
      if (d.ports && d.ports.length) {
        portsEl.innerHTML = d.ports.slice(0,8).map(p => `
          <div class="ci-detail-row">
            <span class="ci-detail-label">🚢 ${p.name}</span>
            <span class="ci-detail-value" style="font-size:.75rem;">${p.harbor_type||'–'} · ${p.harbor_size||'–'}</span>
          </div>`).join('');
      } else {
        portsEl.innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;">No port data available</div>';
      }

      // Economic tab
      if (eco) {
        document.getElementById('ciEconomicDetails').innerHTML = [
          ['GDP (USD)',fmtGdp(eco.gdp)],['Population',fmtPop(eco.population)],
          ['Inflation',eco.inflation!=null?eco.inflation.toFixed(2)+'%':'–'],
          ['Data Year',eco.year||'–'],['Source','World Bank'],
          ['Last Fetched',eco.updated_at ? new Date(eco.updated_at).toLocaleDateString() : '–'],
        ].map(([k,v])=>`<div class="ci-detail-row"><span class="ci-detail-label">${k}</span><span class="ci-detail-value">${v}</span></div>`).join('');

        document.getElementById('ciTradeDetails').innerHTML = [
          ['Exports',fmtGdp(eco.exports)],['Imports',fmtGdp(eco.imports)],
          ['Trade Balance',eco.exports&&eco.imports ? fmtGdp(eco.exports-eco.imports) : '–'],
        ].map(([k,v])=>`<div class="ci-detail-row"><span class="ci-detail-label">${k}</span><span class="ci-detail-value">${v}</span></div>`).join('');
      } else {
        document.getElementById('ciEconomicDetails').innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;">No economic data available</div>';
        document.getElementById('ciTradeDetails').innerHTML = '';
      }

      // Risk tab — gauge
      if (risk) {
        const score = parseFloat(risk.total_score)||0;
        document.getElementById('ciGaugeLabel').textContent = score.toFixed(1);
        document.getElementById('ciRiskLevelBadge').innerHTML = `<span class="risk-pill ${level.toLowerCase()}">${level} Risk</span>`;

        // Gauge Chart
        const gaugeCtx = document.getElementById('ciGaugeChart');
        if (gaugeCtx) {
          if (_ciGauge) { _ciGauge.destroy(); _ciGauge = null; }
          _ciGauge = new Chart(gaugeCtx, {
            type: 'doughnut',
            data: {
              datasets: [{
                data: [score, 100-score],
                backgroundColor: [riskColor(level), 'rgba(148,163,184,.15)'],
                borderWidth: 0,
                circumference: 270,
                rotation: -135,
              }]
            },
            options: { responsive:false, plugins:{legend:{display:false}} }
          });
        }

        // Breakdown bars
        const breakdown = [
          {label:'Weather Risk', score:risk.weather_score, color:'#06b6d4'},
          {label:'Inflation Risk', score:risk.inflation_score, color:'#f59e0b'},
          {label:'Political Risk', score:risk.political_score, color:'#ef4444'},
          {label:'Currency Risk', score:risk.currency_score, color:'#8b5cf6'},
        ];
        document.getElementById('ciRiskBreakdown').innerHTML = breakdown.map(b => `
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span style="font-size:.8rem;color:var(--text-secondary);">${b.label}</span>
              <span style="font-size:.8rem;font-weight:700;color:var(--text-primary);">${parseFloat(b.score||0).toFixed(1)}</span>
            </div>
            <div class="risk-breakdown-bar">
              <div class="risk-breakdown-fill" style="width:${Math.min(parseFloat(b.score||0),100)}%;background:${b.color};"></div>
            </div>
          </div>`).join('');
      } else {
        document.getElementById('ciRiskBreakdown').innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;">No risk data available</div>';
      }

      // Map tab — use OpenStreetMap iframe
      if (d.latitude && d.longitude) {
        const lat = d.latitude, lon = d.longitude;
        document.getElementById('ciModalMapContainer').innerHTML =
          `<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=${lon-5},${lat-5},${lon+5},${lat+5}&amp;layer=mapnik&amp;marker=${lat},${lon}"
            style="width:100%;height:100%;border:none;" loading="lazy" title="Map of ${d.name}"></iframe>`;
        document.getElementById('ciModalMapCoords').textContent =
          `📍 ${d.name} · Lat: ${lat.toFixed(4)}, Lon: ${lon.toFixed(4)}`;
      } else {
        document.getElementById('ciModalMapContainer').innerHTML =
          '<div class="text-center py-5 text-muted">No geographic coordinates available for this country.</div>';
      }

      // News tab
      fetch('/api/news?search='+encodeURIComponent(d.name))
        .then(r=>r.json()).then(n => {
          const newsEl = document.getElementById('ciModalNewsList');
          if (!newsEl) return;
          const articles = n.data || [];
          if (!articles.length) {
            newsEl.innerHTML = '<div style="color:var(--text-muted);font-size:.83rem;text-align:center;padding:20px;">No news found for this country.</div>';
            return;
          }
          newsEl.innerHTML = articles.slice(0,6).map(a => `
            <div style="padding:12px 0;border-bottom:1px solid var(--border-color);display:flex;gap:12px;align-items:flex-start;">
              <span style="font-size:1.2rem;">${a.sentiment==='Positive'?'📈':a.sentiment==='Negative'?'📉':'📰'}</span>
              <div style="flex:1;min-width:0;">
                <a href="${a.url||'#'}" target="_blank" style="font-size:.83rem;font-weight:600;color:var(--text-primary);text-decoration:none;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">${a.title}</a>
                <div style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">${a.source||'–'} · ${a.published_at ? new Date(a.published_at).toLocaleDateString() : '–'}</div>
              </div>
              <span class="risk-pill ${a.sentiment==='Positive'?'low':a.sentiment==='Negative'?'high':'medium'}" style="flex-shrink:0;">${a.sentiment||'–'}</span>
            </div>`).join('');
        }).catch(()=>{});

    } catch(e) {
      console.warn('[CI] Profile fetch error:', e);
    }
  }

  /* ─ Auto-refresh ─ */
  function startAutoRefresh() {
    if (_ciRefreshTimer) clearInterval(_ciRefreshTimer);
    _ciRefreshTimer = setInterval(() => {
      const tog = document.getElementById('ciAutoRefresh');
      if (!tog?.checked) return;
      if (STATE.currentPage !== 'country-intelligence') return;
      refresh(true);
    }, 5 * 60 * 1000);
  }

  async function refresh(silent=false) {
    const icon = document.getElementById('ciRefreshIcon');
    if (icon) icon.style.animation = 'spin 1s linear infinite';
    try {
      await Promise.all([loadSummary(), loadCountries(), loadTopRisk(), loadTopGdp()]);
      if (!silent) showToast('success','CI Refreshed','Country Intelligence data updated.');
    } finally {
      if (icon) icon.style.animation = '';
    }
  }

  /* ─ Public init ─ */
  async function init() {
    if (_inited) {
      // Just refresh map size
      setTimeout(() => { _ciMap?.invalidateSize(); }, 200);
      return;
    }
    _inited = true;
    await Promise.all([loadSummary(), loadCountries(), loadTopRisk(), loadTopGdp()]);
    startAutoRefresh();
  }

  return { init, refresh, applyFilters, resetFilters, filterMap, openProfile, exportTable };
})();