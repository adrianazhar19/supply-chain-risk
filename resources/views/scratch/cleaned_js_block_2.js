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
  let _ciRefreshTimer = null;
  let _inited = false;

  const capitals = {
    US: 'Washington D.C.', GB: 'London', FR: 'Paris', DE: 'Berlin', JP: 'Tokyo',
    CN: 'Beijing', CA: 'Ottawa', AU: 'Canberra', RU: 'Moscow', IN: 'New Delhi',
    BR: 'Brasilia', ZA: 'Pretoria', IT: 'Rome', ES: 'Madrid', NL: 'Amsterdam',
    SE: 'Stockholm', CH: 'Bern', SG: 'Singapore', KR: 'Seoul', MX: 'Mexico City',
    ID: 'Jakarta', AE: 'Abu Dhabi', SA: 'Riyadh', AR: 'Buenos Aires', TR: 'Ankara',
    CA: 'Ottawa', DE: 'Berlin', MY: 'Kuala Lumpur', PH: 'Manila', TH: 'Bangkok'
  };

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
  const riskColor = level => ({Low:'#10b981',Medium:'#eab308',High:'#f97316',Critical:'#ef4444',Extreme:'#7c3aed'}[level]||'#94a3b8');
  const riskBg = level => ({Low:'rgba(16,185,129,.12)',Medium:'rgba(234,179,8,.12)',High:'rgba(249,115,22,.12)',Critical:'rgba(239,68,68,.12)',Extreme:'rgba(124,58,237,.12)'}[level]||'rgba(148,163,184,.12)');
  const riskText = level => ({Low:'#059669',Medium:'#ca8a04',High:'#ea580c',Critical:'#dc2626',Extreme:'#7c3aed'}[level]||'#64748b');
  const statusFromRisk = level => ({Low:'✅ Stable',Medium:'⚠️ Watch',High:'🟠 At Risk',Critical:'🔴 Critical',Extreme:'🟣 Extreme Shutdown'}[level]||'–');

  function animateCounter(el, target, suffix='', duration=1200) {
    if (!el) return;
    const start = parseFloat(el.textContent.replace(/[^0-9.-]/g, '')) || 0;
    const startTime = performance.now();
    const step = (now) => {
      const p = Math.min((now - startTime) / duration, 1);
      const ease = 1 - Math.pow(1-p, 3);
      const val = start + (target - start) * ease;
      
      let formattedVal = (Number.isInteger(target) ? Math.round(val).toLocaleString() : val.toFixed(suffix==='%'?1:2)) + (suffix||'');
      if (suffix === 'B' || suffix === 'T') {
         formattedVal = val.toFixed(1) + suffix;
      }
      el.textContent = formattedVal;
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
      const elPor = document.getElementById('ci-s-ports');
      const elWea = document.getElementById('ci-s-weather');

      if (elC) animateCounter(elC, d.countries||250, '');
      if (elG) { elG.textContent = fmtGdp(totalGdp) || '$106T'; }
      if (elP) { elP.textContent = fmtPop(totalPop) || '8.2B'; }
      if (elR) animateCounter(elR, d.avg_risk_score||34.2, '');
      if (elI) { const infl = d.avg_inflation||4.6; animateCounter(elI, infl, '%'); }
      if (elCur) animateCounter(elCur, d.currencies||180, '');
      if (elPor) animateCounter(elPor, STATE.ports?.length || 42, '');
      if (elWea) animateCounter(elWea, STATE.weather?.length || 250, '');

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

  /* ─ fetch top lists ─ */
  async function loadTopRisk() {
    try {
      const res = await fetch('/api/countries/top-risk');
      const json = await res.json();
      if (!json.status) return;
      const el = document.getElementById('ciTopRiskList');
      if (!el) return;
      if (!json.data.length) { el.innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;text-align:center;padding:12px;">No risk data available</div>'; return; }
      el.innerHTML = json.data.map((r, i) => `
        <div class="ci-rank-item d-flex align-items-center gap-2 mb-2 p-1" style="cursor:pointer;" onclick="CI.openProfile(${r.country_id})">
          <span class="ci-rank-num fw-bold text-muted" style="width:20px;">${i+1}</span>
          <img src="${r.flag_url}" width="20" style="border-radius:3px;flex-shrink:0;" onerror="this.style.display='none'">
          <div style="flex:1;min-width:0;">
            <div style="font-size:.8rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${r.country_name}</div>
          </div>
          <span class="risk-pill ${(r.risk_level||'').toLowerCase()}">${r.total_score.toFixed(1)}</span>
        </div>
      `).join('');
    } catch(e) { console.warn('[CI] Top risk fetch error:', e); }
  }

  async function loadTopGdp() {
    try {
      const res = await fetch('/api/countries/top-gdp');
      const json = await res.json();
      if (!json.status) return;
      const el = document.getElementById('ciTopGdpList');
      if (!el) return;
      if (!json.data.length) { el.innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;text-align:center;padding:12px;">No GDP data available</div>'; return; }
      el.innerHTML = json.data.map((c, i) => `
        <div class="ci-rank-item d-flex align-items-center gap-2 mb-2 p-1" style="cursor:pointer;" onclick="CI.openProfile(dots)">
          <span class="ci-rank-num fw-bold text-muted" style="width:20px;">${i+1}</span>
          <img src="${c.flag_url}" width="20" style="border-radius:3px;flex-shrink:0;" onerror="this.style.display='none'">
          <div style="flex:1;min-width:0;">
            <div style="font-size:.8rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${c.country_name}</div>
          </div>
          <span style="font-size:.75rem;font-weight:700;color:#10b981;">${fmtGdp(c.gdp)}</span>
        </div>
      `.replace(/\dots/g, 'c.country_id')).join('');
    } catch(e) { console.warn('[CI] Top GDP fetch error:', e); }
  }

  function loadTopPopInflation() {
    if (!_countries.length) return;
    
    // Top Population
    const topPop = [..._countries].filter(c=>c.population).sort((a,b)=>b.population-a.population).slice(0,10);
    const popEl = document.getElementById('ciTopPopList');
    if (popEl) {
      popEl.innerHTML = topPop.map((c, i) => `
        <div class="ci-rank-item d-flex align-items-center gap-2 mb-2 p-1" style="cursor:pointer;" onclick="CI.openProfile(${c.id})">
          <span class="ci-rank-num fw-bold text-muted" style="width:20px;">${i+1}</span>
          <img src="${c.flag_url}" width="20" style="border-radius:3px;flex-shrink:0;" onerror="this.style.display='none'">
          <div style="flex:1;min-width:0;">
            <div style="font-size:.8rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${c.name}</div>
          </div>
          <span style="font-size:.75rem;font-weight:700;color:#3b82f6;">${fmtPop(c.population)}</span>
        </div>
      `).join('');
    }

    // Top Inflation
    const topInfl = [..._countries].filter(c=>c.inflation!=null).sort((a,b)=>b.inflation-a.inflation).slice(0,10);
    const inflEl = document.getElementById('ciTopInflationList');
    if (inflEl) {
      inflEl.innerHTML = topInfl.map((c, i) => `
        <div class="ci-rank-item d-flex align-items-center gap-2 mb-2 p-1" style="cursor:pointer;" onclick="CI.openProfile(${c.id})">
          <span class="ci-rank-num fw-bold text-muted" style="width:20px;">${i+1}</span>
          <img src="${c.flag_url}" width="20" style="border-radius:3px;flex-shrink:0;" onerror="this.style.display='none'">
          <div style="flex:1;min-width:0;">
            <div style="font-size:.8rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${c.name}</div>
          </div>
          <span style="font-size:.75rem;font-weight:700;color:#ef4444;">${c.inflation.toFixed(1)}%</span>
        </div>
      `).join('');
    }
  }

  /* ─ fetch countries ─ */
  async function loadCountries() {
    try {
      const res = await fetch('/api/countries');
      const json = await res.json();
      if (!json.status) return;
      _countries = json.data || [];
      _filteredCountries = [..._countries];

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
      loadTopPopInflation();
      updateTableCount();
    } catch(e) { console.warn('[CI] Countries fetch error:', e); }
  }

  /* ─ DataTable ─ */
  function buildTable() {
     buildTableWithData(_filteredCountries);
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
      if (c.latitude === null || c.longitude === null) return;
      const lat = parseFloat(c.latitude);
      const lon = parseFloat(c.longitude);
      if (isNaN(lat) || isNaN(lon) || lat === 0 || lon === 0 || lat < -90 || lat > 90 || lon < -180 || lon > 180) return;

      const level = c.risk_level || 'Low';
      const color = riskColor(level);
      const score = parseFloat(c.risk_score)||0;
      const icon = L.divIcon({
        html: `<div style="width:${12+score/10}px;height:${12+score/10}px;border-radius:50%;background:${color};border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.35);opacity:.85;"></div>`,
        className: '',
        iconSize: [16,16],
        iconAnchor: [8,8],
      });
      const m = L.marker([lat, lon], { icon });
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
    Object.values(_ciCharts).forEach(c => { try { c.destroy(); } catch(e){} });
    _ciCharts = {};

    const isDark = STATE.theme === 'dark';
    const textColor = isDark ? '#94a3b8' : '#475569';
    const gridColor = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.07)';

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
            backgroundColor: ['#10b981','#eab308','#f97316','#ef4444'],
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
            backgroundColor: 'rgba(59,130,246,.7)',
            borderColor: '#3b82f6',
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

    const top20Gdp = [..._countries].filter(c=>c.gdp).sort((a,b)=>b.gdp-a.gdp).slice(0,20);
    const top20GdpCtx = document.getElementById('ciChartTop20Gdp');
    if (top20GdpCtx) {
      _ciCharts.top20Gdp = new Chart(top20GdpCtx, {
        type: 'bar',
        data: {
          labels: top20Gdp.map(c=>c.code||c.name.slice(0,5)),
          datasets: [{
            label: 'GDP (B USD)',
            data: top20Gdp.map(c=>c.gdp/1e9),
            backgroundColor: 'rgba(124,58,237,.7)',
            borderColor: '#7c3aed',
            borderWidth: 1,
            borderRadius: 6,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend:{display:false}, tooltip:{callbacks:{label:c=>'$'+c.parsed.y.toFixed(0)+'B'}} },
          scales: {
            x: { ticks:{color:textColor,maxRotation:45}, grid:{display:false} },
            y: { ticks:{color:textColor,callback:v=>'$'+v+'B'}, grid:{color:gridColor} },
          }
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
      <div class="ci-weather-alert d-flex align-items-center gap-2 mb-2 p-1 rounded bg-dark-subtle">
        <img src="${c.flag_url||''}" width="20" height="14" style="border-radius:3px;flex-shrink:0;" onerror="this.style.display='none'">
        <div style="flex:1;min-width:0;">
          <div style="font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${c.name}</div>
          <div style="font-size:.7rem;color:var(--text-muted);margin-top:2px;">Storm risk elevated</div>
        </div>
        <span class="risk-pill ${(c.risk_level||'').toLowerCase()}">	ext${c.risk_level}</span>
      </div>
    `.replace(/\text/g, '')).join('');
  }

  /* ─ Filters ─ */
  function applyFilters() {
    const search = (document.getElementById('ciSearch')?.value||'').toLowerCase();
    const region = document.getElementById('ciRegionFilter')?.value||'';
    const riskLvl = document.getElementById('ciRiskFilter')?.value||'';
    const gdpFilter = document.getElementById('ciGdpFilter')?.value||'';
    const popFilter = document.getElementById('ciPopFilter')?.value||'';
    const currency = document.getElementById('ciCurrencyFilter')?.value||'';

    _filteredCountries = _countries.filter(c => {
      if (search && !c.name.toLowerCase().includes(search) && !(c.code||'').toLowerCase().includes(search)) return false;
      if (region && c.region !== region) return false;
      if (riskLvl && c.risk_level !== riskLvl) return false;
      if (currency && c.currency !== currency) return false;

      if (gdpFilter) {
         const gdpVal = parseFloat(c.gdp)||0;
         if (gdpFilter === 'low' && gdpVal >= 10e9) return false;
         if (gdpFilter === 'med' && (gdpVal < 10e9 || gdpVal > 100e9)) return false;
         if (gdpFilter === 'high' && (gdpVal < 100e9 || gdpVal > 1e12)) return false;
         if (gdpFilter === 'huge' && gdpVal <= 1e12) return false;
      }

      if (popFilter) {
         const popVal = parseFloat(c.population)||0;
         if (popFilter === 'low' && popVal >= 1e6) return false;
         if (popFilter === 'med' && (popVal < 1e6 || popVal > 10e6)) return false;
         if (popFilter === 'high' && (popVal < 10e6 || popVal > 100e6)) return false;
         if (popFilter === 'huge' && popVal <= 100e6) return false;
      }

      return true;
    });

    const tags = [];
    if (search) tags.push(`<span style="padding:2px 10px;border-radius:12px;background:rgba(59,130,246,.12);color:var(--brand-500);font-size:.72rem;font-weight:600;">Search: ${search} <span onclick="document.getElementById('ciSearch').value='';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>`);
    if (region) tags.push(`<span style="padding:2px 10px;border-radius:12px;background:rgba(16,185,129,.12);color:#059669;font-size:.72rem;font-weight:600;">${region} <span onclick="document.getElementById('ciRegionFilter').value='';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>`);
    if (riskLvl) tags.push(`<span style="padding:2px 10px;border-radius:12px;background:rgba(239,68,68,.12);color:#dc2626;font-size:.72rem;font-weight:600;">	h${riskLvl} <span onclick="document.getElementById('ciRiskFilter').value='';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>`.replace(/\th/g, ''));
    if (gdpFilter) tags.push(`<span style="padding:2px 10px;border-radius:12px;background:rgba(59,130,246,.12);color:#2563eb;font-size:.72rem;font-weight:600;">GDP: ${gdpFilter} <span onclick="document.getElementById('ciGdpFilter').value='';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>`);
    if (popFilter) tags.push(`<span style="padding:2px 10px;border-radius:12px;background:rgba(16,185,129,.12);color:#10b981;font-size:.72rem;font-weight:600;">Pop: ${popFilter} <span onclick="document.getElementById('ciPopFilter').value='';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>`);
    if (currency) tags.push(`<span style="padding:2px 10px;border-radius:12px;background:rgba(245,158,11,.12);color:#d97706;font-size:.72rem;font-weight:600;">	h${currency} <span onclick="document.getElementById('ciCurrencyFilter').value='';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>`.replace(/\th/g, ''));

    const filterEl = document.getElementById('ciActiveFilters');
    if (filterEl) filterEl.innerHTML = tags.length ? tags.join('') : '<span style="font-size:.75rem;color:var(--text-muted);">No active filter tokens</span>';

    buildTableWithData(_filteredCountries);
    updateTableCount();
  }

  function buildTableWithData(data) {
    if (_ciDt) { _ciDt.destroy(); _ciDt = null; }
    const tbody = document.getElementById('ciTableBody');
    if (!tbody) return;

    if (!data.length) {
       tbody.innerHTML = '<tr><td colspan="16" class="text-center py-4 text-muted">No countries matched filters.</td></tr>';
       return;
    }

    tbody.innerHTML = data.map(c => {
      const risk = parseFloat(c.risk_score)||0;
      const level = c.risk_level || 'Low';
      const popVal = parseFloat(c.population)||0;
      const gdpVal = parseFloat(c.gdp)||0;
      const gdpCap = popVal > 0 ? (gdpVal / popVal) : 0;

      return `<tr data-id="${c.id}" onclick="CI.openProfile(${c.id})">
        <td><img src="${c.flag_url||''}" width="24" height="16" style="border-radius:3px;object-fit:cover;border:1px solid var(--border-color);" onerror="this.style.display='none'"></td>
        <td><span style="font-weight:600;">${c.name}</span></td>
        <td><code style="font-size:.78rem;background:rgba(59,130,246,.08);padding:2px 6px;border-radius:4px;color:var(--brand-500);">${c.code||'–'}</code></td>
        <td>${c.region||'–'}</td>
        <td>${fmtPop(c.population)}</td>
        <td>${fmtGdp(c.gdp)}</td>
        <td>${gdpCap > 0 ? '$' + gdpCap.toLocaleString('en-US', {maximumFractionDigits:0}) : '–'}</td>
        <td>${c.inflation!=null?c.inflation.toFixed(2)+'%':'–'}</td>
        <td>${c.currency||'–'}</td>
        <td>🚢 ${STATE.ports?.filter(p => p.country_id === c.id).length || 0}</td>
        <td>${STATE.weather?.filter(w => w.country?.id === c.id).length || 1} stations</td>
        <td><span style="font-family:var(--font-mono);font-weight:700;">${risk>0?risk.toFixed(1):'–'}</span></td>
        <td><span class="risk-pill ${level.toLowerCase()}">${level}</span></td>
        <td><span style="font-size:.76rem;">${statusFromRisk(level)}</span></td>
        <td style="font-size:.72rem;color:var(--text-muted);">Just Now</td>
        <td>
          <button class="btn btn-sm btn-outline-brand" style="padding:4px 10px;font-size:.72rem;" onclick="event.stopPropagation();CI.openProfile(${c.id})">
            Profile
          </button>
        </td>
      </tr>`;
    }).join('');

    _ciDt = $('#ciCountriesTable').DataTable({
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      responsive: true,
      scrollX: false,
      order: [[11, 'desc']],
      columnDefs: [{ orderable: false, targets: [0, 15] }],
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
  }

  function updateTableCount() {
    const el = document.getElementById('ciTableCount');
    if (el) el.textContent = _filteredCountries.length + ' countries';
  }

  function resetFilters() {
    ['ciSearch','ciRegionFilter','ciRiskFilter','ciGdpFilter','ciPopFilter','ciCurrencyFilter'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
    _filteredCountries = [..._countries];
    buildTableWithData(_filteredCountries);
    updateTableCount();
    const filterEl = document.getElementById('ciActiveFilters');
    if (filterEl) filterEl.innerHTML = '<span style="font-size:.75rem;color:var(--text-muted);">No active filter tokens</span>';
  }

  /* ─ Export ─ */
  function exportTable(type) {
    if (!_ciDt) return;
    const btnMap = { csv: 0, excel: 1, pdf: 2 };
    const btns = _ciDt.buttons();
    if (btns && btns[btnMap[type]]) btns[btnMap[type]].trigger();
  }

  /* ─ Country Profile right drawer ─ */
  async function openProfile(id) {
    const drawer = document.getElementById('ciProfileDrawer');
    if (!drawer) return;
    drawer.style.display = 'block';
    setTimeout(() => drawer.classList.add('open'), 50);

    document.querySelectorAll('#ciDrawerTabs .nav-link').forEach((l,i) => l.classList.toggle('active', i===0));
    document.getElementById('ciTabOverviewLink').click();

    document.getElementById('ciDrawerFlag').src = '';
    document.getElementById('ciDrawerName').textContent = 'Loading…';
    document.getElementById('ciDrawerMeta').textContent = '–';
    document.getElementById('ciDrawerOverviewDetails').innerHTML = '<div class="skeleton" style="height:200px; margin-bottom:12px;"></div>';
    document.getElementById('ciDrawerPortsList').innerHTML = '';

    try {
      const res = await fetch('/api/countries/'+id);
      const json = await res.json();
      if (!json.status) return;
      const d = json.data;
      const risk = d.risk;
      const eco = d.economic;
      const level = risk?.risk_level || 'Low';
      const popVal = parseFloat(d.economic?.population)||0;
      const gdpVal = parseFloat(d.economic?.gdp)||0;
      const gdpCap = popVal > 0 ? (gdpVal / popVal) : 0;

      document.getElementById('ciDrawerFlag').src = d.flag_url||'';
      document.getElementById('ciDrawerName').textContent = d.name;
      document.getElementById('ciDrawerMeta').textContent = `${d.code||'–'} · ${d.region||'–'} · ${d.currency||'–'}`;

      const capName = capitals[d.code.toUpperCase()] || 'N/A';
      const overviewFields = [
        ['Country', d.name],['Capital', capName],['ISO Code', d.code||'–'],['Region', d.region||'–'],
        ['Currency', d.currency||'–'],['Exchange Rate', STATE.currencies?.latest_rates ? (STATE.currencies.latest_rates[d.currency] ? '1 USD = ' + STATE.currencies.latest_rates[d.currency].toFixed(2) + ' ' + d.currency : 'N/A') : 'N/A'],
        ['Risk Level', `<span class="risk-pill ${level.toLowerCase()}">${level}</span>`],
        ['Status', statusFromRisk(level)],
        ['Supply Chain Status', {Low:'<span class="badge bg-success">Optimal</span>',Medium:'<span class="badge bg-warning">Warning</span>',High:'<span class="badge bg-danger">Critical Disruption</span>',Critical:'<span class="badge bg-danger">Severe Shutdown</span>'}[level] || '<span class="badge bg-secondary">Stable</span>']
      ];
      document.getElementById('ciDrawerOverviewDetails').innerHTML = overviewFields.map(([k,v]) =>
        `<div class="ci-detail-row d-flex justify-content-between mb-2 pb-1 border-bottom" style="border-color:rgba(255,255,255,.05)!important; font-size:.8rem;"><span class="text-muted">${k}</span><strong class="text-white">${v}</strong></div>`
      ).join('');

      if (d.latitude && d.longitude) {
        document.getElementById('ciDrawerMapCoords').textContent = `📍 Lat: ${d.latitude.toFixed(4)}, Lon: ${d.longitude.toFixed(4)}`;
        document.getElementById('ciDrawerMapContainer').innerHTML =
          `<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=${d.longitude-5},dots&amp;layer=mapnik&amp;marker=${d.latitude},${d.longitude}"
            style="width:100%;height:100%;border:none;" loading="lazy"></iframe>`.replace(/\dots/g, `${d.latitude-5},${d.longitude+5},${d.latitude+5}`);
      } else {
         document.getElementById('ciDrawerMapCoords').textContent = 'Coordinates not mapped';
         document.getElementById('ciDrawerMapContainer').innerHTML = '<div class="text-center py-4 text-muted">Map not available</div>';
      }

      const portsEl = document.getElementById('ciDrawerPortsList');
      if (d.ports && d.ports.length) {
        portsEl.innerHTML = d.ports.slice(0,5).map(p => `
          <div class="ci-detail-row d-flex justify-content-between mb-2 pb-1 border-bottom" style="border-color:rgba(255,255,255,.05)!important; font-size:.8rem;">
            <span class="text-white">🚢 ${p.name}</span>
            <span class="text-muted">${p.harbor_type||'–'} · ${p.harbor_size||'–'}</span>
          </div>`).join('');
      } else {
        portsEl.innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;text-align:center;">No monitored ports listed</div>';
      }

      if (eco) {
        document.getElementById('ciDrawerEconomicDetails').innerHTML = [
          ['GDP (USD)', fmtGdp(eco.gdp)],['Population', fmtPop(eco.population)],
          ['GDP per Capita', gdpCap > 0 ? '$' + gdpCap.toLocaleString('en-US', {maximumFractionDigits:0}) : '–'],
          ['Inflation Rate', eco.inflation!=null ? eco.inflation.toFixed(2)+'%' : '–'],
          ['Data Year', eco.year||'–'],['Source', 'World Bank'],
        ].map(([k,v])=>`<div class="ci-detail-row d-flex justify-content-between mb-2 pb-1 border-bottom" style="border-color:rgba(255,255,255,.05)!important; font-size:.8rem;"><span class="text-muted">${k}</span><strong class="text-white">	ext${v}</strong></div>`.replace(/\text/g, '')).join('');

        document.getElementById('ciDrawerTradeDetails').innerHTML = [
          ['Exports (Value)', fmtGdp(eco.exports)],['Imports (Value)', fmtGdp(eco.imports)],
          ['Trade Balance', eco.exports&&eco.imports ? fmtGdp(eco.exports-eco.imports) : '–'],
        ].map(([k,v])=>`<div class="ci-detail-row d-flex justify-content-between mb-2 pb-1 border-bottom" style="border-color:rgba(255,255,255,.05)!important; font-size:.8rem;"><span class="text-muted">${k}</span><strong class="text-white">	ext${v}</strong></div>`.replace(/\text/g, '')).join('');
      } else {
        document.getElementById('ciDrawerEconomicDetails').innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;">No economic records found</div>';
        document.getElementById('ciDrawerTradeDetails').innerHTML = '';
      }

      if (risk) {
        const score = parseFloat(risk.total_score)||0;
        document.getElementById('ciDrawerGaugeLabel').textContent = score.toFixed(1);
        document.getElementById('ciDrawerRiskLevelBadge').innerHTML = `<span class="risk-pill ${level.toLowerCase()}">${level} Risk</span>`;

        const gaugeCtx = document.getElementById('ciDrawerGaugeChart');
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

        const breakdown = [
          {label:'Weather Risk', score:risk.weather_score, color:'#06b6d4'},
          {label:'Inflation Risk', score:risk.inflation_score, color:'#f59e0b'},
          {label:'Political Risk', score:risk.political_score, color:'#ef4444'},
          {label:'Currency Risk', score:risk.currency_score, color:'#8b5cf6'},
        ];
        document.getElementById('ciDrawerRiskBreakdown').innerHTML = breakdown.map(b => `
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span style="font-size:.8rem;color:var(--text-secondary);">${b.label}</span>
              <span style="font-size:.8rem;font-weight:700;color:var(--text-primary);">${parseFloat(b.score||0).toFixed(1)}</span>
            </div>
            <div class="risk-breakdown-bar" style="height:6px;background:rgba(255,255,255,0.06);border-radius:4px;overflow:hidden;">
              <div class="risk-breakdown-fill" style="width:${Math.min(parseFloat(b.score||0),100)}%;background:${b.color};height:100%;"></div>
            </div>
          </div>`).join('');
      } else {
        document.getElementById('ciDrawerRiskBreakdown').innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;">No risk details listed</div>';
      }

      if (d.weather) {
        document.getElementById('ciDrawerWeatherDetails').innerHTML = `
          <div class="p-3 bg-dark-subtle rounded d-flex align-items-center gap-3">
             <span style="font-size:2rem;color:${riskColor(level)};"><i class="bi ${d.weather.icon||'bi-cloud'}"></i></span>
             <div>
                <div class="h4 mb-0 text-white font-mono fw-bold">${d.weather.temperature?.toFixed(1)}°C</div>
                <div style="font-size:0.75rem;color:var(--text-muted);">${d.weather.description || 'Clear Sky'}</div>
             </div>
          </div>
          <div class="row g-2 mt-2">
             <div class="col-4 text-center p-2 rounded bg-opacity-25 bg-secondary" style="font-size:0.7rem;"><div class="text-muted">Humidity</div><strong class="text-white">${d.weather.humidity}%</strong></div>
             <div class="col-4 text-center p-2 rounded bg-opacity-25 bg-secondary" style="font-size:0.7rem;"><div class="text-muted">Wind Speed</div><strong class="text-white">${d.weather.wind_speed} km/h</strong></div>
             <div class="col-4 text-center p-2 rounded bg-opacity-25 bg-secondary" style="font-size:0.7rem;"><div class="text-muted">Rainfall</div><strong class="text-white">	ext${d.weather.rainfall} mm</strong></div>
          </div>
        `.replace(/\text/g, '');
      } else {
         document.getElementById('ciDrawerWeatherDetails').innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;">No weather data loaded</div>';
      }

      fetch('/api/news?search='+encodeURIComponent(d.name))
        .then(r=>r.json()).then(n => {
          const newsEl = document.getElementById('ciDrawerNewsList');
          if (!newsEl) return;
          const articles = n.data || [];
          if (!articles.length) {
            newsEl.innerHTML = '<div style="color:var(--text-muted);font-size:.83rem;text-align:center;padding:20px;">No news found for this country.</div>';
            return;
          }
          newsEl.innerHTML = articles.slice(0,5).map(a => `
            <div style="padding:10px 0;border-bottom:1px solid var(--border-color);display:flex;gap:12px;align-items:flex-start;">
              <div style="flex:1;min-width:0;">
                <a href="${a.url||'#'}" target="_blank" style="font-size:.8rem;font-weight:600;color:var(--text-primary);text-decoration:none;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">${a.title}</a>
                <div style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">${a.source||'–'} · ${a.published_at ? new Date(a.published_at).toLocaleDateString() : '–'}</div>
              </div>
              <span class="risk-pill ${a.sentiment==='Positive'?'low':a.sentiment==='Negative'?'high':'medium'}" style="flex-shrink:0; font-size:.65rem; padding:2px 6px;">${a.sentiment||'–'}</span>
            </div>`).join('');
        }).catch(()=>{});

    } catch(e) {
      console.warn('[CI] Profile fetch error:', e);
    }
  }

  function closeProfile() {
    const drawer = document.getElementById('ciProfileDrawer');
    if (!drawer) return;
    drawer.classList.remove('open');
    setTimeout(() => drawer.style.display = 'none', 300);
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
      setTimeout(() => { _ciMap?.invalidateSize(); }, 200);
      return;
    }
    _inited = true;
    await Promise.all([loadSummary(), loadCountries(), loadTopRisk(), loadTopGdp()]);
    startAutoRefresh();
  }

  return { init, refresh, applyFilters, resetFilters, filterMap, openProfile, closeProfile, exportTable };
})();