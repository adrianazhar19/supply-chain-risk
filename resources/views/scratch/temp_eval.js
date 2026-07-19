<script>
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
  const statusFromRisk = level => ({Low:'✅ Stable',Medium:'⚠️ Watch',High:'🔴 At Risk',Critical:'🟣 Critical'}[level]||'–');

  function animateCounter(el, target, suffix, duration) {
    suffix = suffix||''; duration = duration||1200;
    const startTime = performance.now();
    const step = (now) => {
      const p = Math.min((now - startTime) / duration, 1);
      const ease = 1 - Math.pow(1-p, 3);
      const val = target * ease;
      el.textContent = (Number.isInteger(target) ? Math.round(val).toLocaleString() : val.toFixed(1)) + suffix;
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  }

  async function loadSummary() {
    try {
      const res = await fetch('/api/countries/summary');
      const json = await res.json();
      if (!json.status) return;
      const d = json.data;
      const elC = document.getElementById('ci-s-countries');
      const elG = document.getElementById('ci-s-gdp');
      const elP = document.getElementById('ci-s-pop');
      const elR = document.getElementById('ci-s-risk');
      const elI = document.getElementById('ci-s-inflation');
      const elCur = document.getElementById('ci-s-currencies');
      if (elC) animateCounter(elC, d.countries||0);
      if (elG) elG.textContent = fmtGdp(d.gdp_total) || '$106T';
      if (elP) elP.textContent = fmtPop(d.population_total) || '8.2B';
      if (elR) animateCounter(elR, d.avg_risk_score||34.2);
      if (elI) elI.textContent = (d.avg_inflation||4.6).toFixed(1) + '%';
      if (elCur) animateCounter(elCur, d.currencies||180);
      const lu = document.getElementById('ciLastUpdate');
      if (lu) lu.textContent = 'Data refreshed: ' + new Date(d.last_sync).toLocaleTimeString();
      const lud = document.getElementById('ciLastUpdatedInfo');
      if (lud) {
        lud.innerHTML = '<div class="ci-detail-row"><span class="ci-detail-label">Last Sync</span><span class="ci-detail-value">' + new Date(d.last_sync).toLocaleTimeString() + '</span></div>'
          + '<div class="ci-detail-row"><span class="ci-detail-label">Countries</span><span class="ci-detail-value">' + (d.countries||0) + ' monitored</span></div>'
          + '<div class="ci-detail-row"><span class="ci-detail-label">Data Source</span><span class="ci-detail-value">World Bank API</span></div>'
          + '<div class="ci-detail-row"><span class="ci-detail-label">Update Freq</span><span class="ci-detail-value">Every 5 min</span></div>';
      }
    } catch(e) { console.warn('[CI] Summary fetch error:', e); }
  }

  async function loadTopRisk() {
    try {
      const res = await fetch('/api/countries/top-risk');
      const json = await res.json();
      if (!json.status) return;
      const el = document.getElementById('ciTopRiskList');
      if (!el) return;
      if (!json.data.length) { el.innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;text-align:center;padding:12px;">No risk data available</div>'; return; }
      el.innerHTML = json.data.map(function(r, i) {
        return '<div class="ci-rank-item">'
          + '<span class="ci-rank-num ' + (i<3?'top3':'') + '">' + (i+1) + '</span>'
          + '<img src="' + r.flag_url + '" width="20" style="border-radius:3px;flex-shrink:0;" onerror="this.style.display=\'none\'" alt="' + r.country_code + '">'
          + '<div style="flex:1;min-width:0;"><div style="font-size:.8rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + r.country_name + '</div><div style="font-size:.7rem;color:var(--text-muted);">' + (r.region||'–') + '</div></div>'
          + '<span class="risk-pill ' + (r.risk_level||'').toLowerCase() + '">' + r.total_score.toFixed(1) + '</span>'
          + '</div>';
      }).join('');
    } catch(e) { console.warn('[CI] Top risk error:', e); }
  }

  async function loadTopGdp() {
    try {
      const res = await fetch('/api/countries/top-gdp');
      const json = await res.json();
      if (!json.status) return;
      const el = document.getElementById('ciTopGdpList');
      if (!el) return;
      if (!json.data.length) { el.innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;text-align:center;padding:12px;">No GDP data available</div>'; return; }
      el.innerHTML = json.data.map(function(c, i) {
        return '<div class="ci-rank-item">'
          + '<span class="ci-rank-num ' + (i<3?'top3':'') + '">' + (i+1) + '</span>'
          + '<img src="' + c.flag_url + '" width="20" style="border-radius:3px;flex-shrink:0;" onerror="this.style.display=\'none\'" alt="' + c.country_code + '">'
          + '<div style="flex:1;min-width:0;"><div style="font-size:.8rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + c.country_name + '</div><div style="font-size:.7rem;color:var(--text-muted);">' + (c.region||'–') + '</div></div>'
          + '<span style="font-size:.75rem;font-weight:700;color:#059669;white-space:nowrap;">' + fmtGdp(c.gdp) + '</span>'
          + '</div>';
      }).join('');
    } catch(e) { console.warn('[CI] Top GDP error:', e); }
  }

  function buildDt(data) {
    if (_ciDt) { try { _ciDt.destroy(); } catch(e){} _ciDt = null; }
    var tbody = document.getElementById('ciTableBody');
    if (!tbody) return;
    tbody.innerHTML = data.map(function(c) {
      var risk = parseFloat(c.risk_score)||0;
      var level = c.risk_level || 'Low';
      return '<tr data-id="' + c.id + '" onclick="CI.openProfile(' + c.id + ')" style="cursor:pointer;">'
        + '<td><img src="' + (c.flag_url||'') + '" width="24" height="16" style="border-radius:3px;object-fit:cover;border:1px solid var(--border-color);" onerror="this.style.display=\'none\'" alt="' + c.code + '"></td>'
        + '<td><span style="font-weight:600;">' + c.name + '</span></td>'
        + '<td><code style="font-size:.78rem;background:rgba(59,130,246,.08);padding:2px 6px;border-radius:4px;color:var(--brand-500);">' + (c.code||'–') + '</code></td>'
        + '<td>' + (c.region||'–') + '</td>'
        + '<td>' + fmtPop(c.population) + '</td>'
        + '<td>' + fmtGdp(c.gdp) + '</td>'
        + '<td>' + (c.currency||'–') + '</td>'
        + '<td>' + (c.inflation!=null ? parseFloat(c.inflation).toFixed(2)+'%' : '–') + '</td>'
        + '<td><span style="font-family:var(--font-mono);font-weight:700;">' + (risk>0?risk.toFixed(1):'–') + '</span></td>'
        + '<td><span class="risk-pill ' + level.toLowerCase() + '">' + level + '</span></td>'
        + '<td><span style="font-size:.76rem;">' + statusFromRisk(level) + '</span></td>'
        + '<td style="font-size:.72rem;color:var(--text-muted);">' + (c.latitude ? parseFloat(c.latitude).toFixed(2)+', '+parseFloat(c.longitude).toFixed(2) : '–') + '</td>'
        + '<td><button class="btn-brand" style="padding:4px 10px;font-size:.72rem;" onclick="event.stopPropagation();CI.openProfile(' + c.id + ')"><i class="bi bi-person-vcard"></i></button></td>'
        + '</tr>';
    }).join('');

    _ciDt = $('#ciCountriesTable').DataTable({
      pageLength: 25, lengthMenu: [10,25,50,100], responsive: true, order: [[8,'desc']],
      columnDefs: [{orderable:false, targets:[0,12]}],
      dom: '<"d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 pt-3"lBf>rtip',
      buttons: [
        {extend:'csv',   text:'<i class="bi bi-filetype-csv me-1"></i>CSV',   className:'dt-button'},
        {extend:'excel', text:'<i class="bi bi-file-earmark-excel me-1"></i>Excel', className:'dt-button'},
        {extend:'pdf',   text:'<i class="bi bi-file-earmark-pdf me-1"></i>PDF', className:'dt-button', orientation:'landscape', pageSize:'A3'},
        {extend:'print', text:'<i class="bi bi-printer me-1"></i>Print', className:'dt-button'},
        {extend:'colvis',text:'<i class="bi bi-layout-three-columns me-1"></i>Columns', className:'dt-button'},
      ],
      language: {search:'', searchPlaceholder:'Search table…', lengthMenu:'Show _MENU_', info:'Showing _START_–_END_ of _TOTAL_', paginate:{previous:'‹',next:'›'}},
    });
  }

  async function loadCountries() {
    try {
      const res = await fetch('/api/countries');
      const json = await res.json();
      if (!json.status) return;
      _countries = json.data || [];
      _filteredCountries = _countries.slice();
      var currencies = [];
      _countries.forEach(function(c){ if(c.currency && currencies.indexOf(c.currency)<0) currencies.push(c.currency); });
      currencies.sort();
      var curSel = document.getElementById('ciCurrencyFilter');
      if (curSel) curSel.innerHTML = '<option value="">All Currencies</option>' + currencies.map(function(cur){ return '<option>' + cur + '</option>'; }).join('');
      buildDt(_filteredCountries);
      buildCharts();
      buildMap();
      buildWeatherAlerts();
      updateCount();
    } catch(e) { console.warn('[CI] Countries error:', e); }
  }

  function buildMap() {
    var mapEl = document.getElementById('ciMap');
    if (!mapEl) return;
    if (_ciMap) {
      _ciMap.eachLayer(function(l){ if (!(l instanceof L.TileLayer)) _ciMap.removeLayer(l); });
    } else {
      var dark = STATE.theme === 'dark';
      _ciMap = L.map('ciMap', {center:[15,10], zoom:2});
      L.tileLayer(dark
        ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
        : 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
        {attribution:'© CartoDB © OSM', maxZoom:19}).addTo(_ciMap);
    }
    _ciCluster = L.markerClusterGroup({showCoverageOnHover:false, maxClusterRadius:40});
    _ciAllMarkers = [];
    _countries.forEach(function(c) {
      if (!c.latitude || !c.longitude) return;
      var level = c.risk_level || 'Low';
      var color = riskColor(level);
      var score = parseFloat(c.risk_score)||0;
      var sz = 12 + Math.min(score/10, 10);
      var icon = L.divIcon({
        html: '<div style="width:' + sz + 'px;height:' + sz + 'px;border-radius:50%;background:' + color + ';border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.35);opacity:.85;"></div>',
        className: '', iconSize:[sz,sz], iconAnchor:[sz/2,sz/2],
      });
      var m = L.marker([parseFloat(c.latitude), parseFloat(c.longitude)], {icon:icon});
      m.bindPopup(
        '<div style="font-family:sans-serif;min-width:200px;">'
        + '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">'
        + '<img src="' + (c.flag_url||'') + '" width="24" height="16" style="border-radius:3px;" onerror="this.style.display=\'none\'" alt="">'
        + '<strong style="font-size:.9rem;">' + c.name + '</strong></div>'
        + '<hr style="margin:6px 0;">'
        + '<div style="font-size:.78rem;"><b>Region:</b> ' + (c.region||'–') + '</div>'
        + '<div style="font-size:.78rem;"><b>GDP:</b> ' + fmtGdp(c.gdp) + '</div>'
        + '<div style="font-size:.78rem;"><b>Population:</b> ' + fmtPop(c.population) + '</div>'
        + '<div style="font-size:.78rem;"><b>Currency:</b> ' + (c.currency||'–') + '</div>'
        + '<div style="font-size:.78rem;"><b>Risk:</b> <span style="color:' + color + ';font-weight:700;">' + (score>0?score.toFixed(1):'–') + ' (' + level + ')</span></div>'
        + '<hr style="margin:8px 0;">'
        + '<button onclick="CI.openProfile(' + c.id + ')" style="width:100%;padding:5px;border-radius:6px;border:none;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-size:.75rem;font-weight:600;cursor:pointer;">View Profile</button>'
        + '</div>', {maxWidth:260}
      );
      m._ciData = {id:c.id, risk_level:level, region:c.region};
      _ciAllMarkers.push(m);
      _ciCluster.addLayer(m);
    });
    _ciMap.addLayer(_ciCluster);
    setTimeout(function(){ if(_ciMap) _ciMap.invalidateSize(); }, 300);
  }

  function filterMap(type, btn, regionVal) {
    if (!_ciCluster) return;
    if (btn) {
      document.querySelectorAll('.ci-map-pill:not(select)').forEach(function(p){ p.classList.remove('active'); });
      btn.classList.add('active');
      var rs = document.getElementById('ciMapRegionFilter');
      if (rs) rs.value = '';
    }
    _ciCluster.clearLayers();
    _ciAllMarkers.filter(function(m) {
      var d = m._ciData;
      if (regionVal) return d.region === regionVal;
      if (type === 'all') return true;
      return d.risk_level === type;
    }).forEach(function(m){ _ciCluster.addLayer(m); });
    if (_ciMap) _ciMap.invalidateSize();
  }

  function buildCharts() {
    Object.values(_ciCharts).forEach(function(c){ try{c.destroy();}catch(e){} });
    _ciCharts = {};
    var isDark = STATE.theme === 'dark';
    var textColor = isDark ? '#94a3b8' : '#475569';
    var gridColor = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.07)';

    // 1. GDP by Region
    var regionGdp = {};
    _countries.forEach(function(c){ if(c.region && c.gdp){ regionGdp[c.region] = (regionGdp[c.region]||0) + parseFloat(c.gdp); } });
    var sortedR = Object.entries(regionGdp).sort(function(a,b){return b[1]-a[1];});
    var gdpCtx = document.getElementById('ciChartGdpRegion');
    if (gdpCtx) {
      _ciCharts.gdpRegion = new Chart(gdpCtx, {
        type:'bar', data:{labels:sortedR.map(function(r){return r[0];}),datasets:[{label:'GDP (T)',data:sortedR.map(function(r){return r[1]/1e12;}),backgroundColor:['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'],borderRadius:8,borderSkipped:false}]},
        options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return '$'+c.parsed.x.toFixed(2)+'T';}}}},scales:{x:{ticks:{color:textColor,callback:function(v){return '$'+v+'T';}},grid:{color:gridColor}},y:{ticks:{color:textColor},grid:{color:gridColor}}}}
      });
    }

    // 2. Risk Distribution doughnut
    var riskCounts = {Low:0,Medium:0,High:0,Critical:0};
    _countries.forEach(function(c){ if(c.risk_level) riskCounts[c.risk_level]=(riskCounts[c.risk_level]||0)+1; });
    var riskCtx = document.getElementById('ciChartRiskDist');
    if (riskCtx) {
      _ciCharts.riskDist = new Chart(riskCtx, {
        type:'doughnut', data:{labels:Object.keys(riskCounts),datasets:[{data:Object.values(riskCounts),backgroundColor:['#10b981','#f59e0b','#ef4444','#7c3aed'],borderWidth:3,borderColor:isDark?'#0a0f1e':'#f0f4f8',hoverOffset:10}]},
        options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'right',labels:{color:textColor,font:{size:11},padding:12}}}}
      });
    }

    // 3. Population
    var byPop = _countries.filter(function(c){return c.population;}).sort(function(a,b){return b.population-a.population;}).slice(0,12);
    var popCtx = document.getElementById('ciChartPopulation');
    if (popCtx) {
      _ciCharts.population = new Chart(popCtx, {
        type:'bar', data:{labels:byPop.map(function(c){return c.code||c.name.slice(0,5);}),datasets:[{label:'Population',data:byPop.map(function(c){return c.population/1e6;}),backgroundColor:'rgba(139,92,246,.7)',borderColor:'#7c3aed',borderWidth:1,borderRadius:6}]},
        options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return c.parsed.y.toFixed(0)+'M people';}}}},scales:{x:{ticks:{color:textColor,maxRotation:45},grid:{display:false}},y:{ticks:{color:textColor,callback:function(v){return v+'M';}},grid:{color:gridColor}}}}
      });
    }

    // 4. Inflation
    var byInflation = _countries.filter(function(c){return c.inflation!=null;}).sort(function(a,b){return b.inflation-a.inflation;}).slice(0,15);
    var inflCtx = document.getElementById('ciChartInflation');
    if (inflCtx) {
      _ciCharts.inflation = new Chart(inflCtx, {
        type:'bar', data:{labels:byInflation.map(function(c){return c.code||c.name.slice(0,5);}),datasets:[{label:'Inflation %',data:byInflation.map(function(c){return parseFloat(c.inflation);}),backgroundColor:byInflation.map(function(c){return parseFloat(c.inflation)>8?'rgba(239,68,68,.75)':parseFloat(c.inflation)>4?'rgba(245,158,11,.75)':'rgba(16,185,129,.75)';}),borderRadius:5,borderSkipped:false}]},
        options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return c.parsed.y.toFixed(2)+'%';}}}},scales:{x:{ticks:{color:textColor,maxRotation:45},grid:{display:false}},y:{ticks:{color:textColor,callback:function(v){return v+'%';}},grid:{color:gridColor}}}}
      });
    }

    // 5. Region pie
    var regionCount = {};
    _countries.forEach(function(c){ if(c.region) regionCount[c.region]=(regionCount[c.region]||0)+1; });
    var regCtx = document.getElementById('ciChartRegion');
    if (regCtx) {
      _ciCharts.region = new Chart(regCtx, {
        type:'pie', data:{labels:Object.keys(regionCount),datasets:[{data:Object.values(regionCount),backgroundColor:['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'],borderWidth:3,borderColor:isDark?'#0a0f1e':'#f0f4f8',hoverOffset:8}]},
        options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{color:textColor,font:{size:10},padding:8,boxWidth:12}}}}
      });
    }
  }

  function buildWeatherAlerts() {
    var alerts = _countries.filter(function(c){ return c.risk_level==='High'||c.risk_level==='Critical'; });
    var countEl = document.getElementById('ciWeatherAlertCount');
    if (countEl) countEl.textContent = alerts.length;
    var el = document.getElementById('ciWeatherAlerts');
    if (!el) return;
    if (!alerts.length) {
      el.innerHTML = '<div style="font-size:.78rem;color:var(--text-muted);text-align:center;padding:12px 0;"><i class="bi bi-sun-fill" style="font-size:1.5rem;opacity:.3;display:block;margin-bottom:6px;"></i>No active alerts</div>';
      return;
    }
    el.innerHTML = alerts.slice(0,8).map(function(c) {
      return '<div class="ci-weather-alert">'
        + '<img src="' + (c.flag_url||'') + '" width="20" height="14" style="border-radius:3px;flex-shrink:0;" onerror="this.style.display=\'none\'" alt="">'
        + '<div style="flex:1;min-width:0;"><div style="font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + c.name + '</div><div style="font-size:.7rem;color:var(--text-muted);">' + (c.region||'–') + '</div></div>'
        + '<span class="risk-pill ' + (c.risk_level||'').toLowerCase() + '">' + (c.risk_level||'–') + '</span>'
        + '</div>';
    }).join('');
  }

  function applyFilters() {
    var search = (document.getElementById('ciSearch')?.value||'').toLowerCase();
    var region = document.getElementById('ciRegionFilter')?.value||'';
    var riskLvl = document.getElementById('ciRiskFilter')?.value||'';
    var currency = document.getElementById('ciCurrencyFilter')?.value||'';
    _filteredCountries = _countries.filter(function(c) {
      if (search && !c.name.toLowerCase().includes(search) && !(c.code||'').toLowerCase().includes(search)) return false;
      if (region && c.region !== region) return false;
      if (riskLvl && c.risk_level !== riskLvl) return false;
      if (currency && c.currency !== currency) return false;
      return true;
    });
    buildDt(_filteredCountries);
    updateCount();
    var tags = [];
    if (search) tags.push('<span style="padding:2px 10px;border-radius:12px;background:rgba(59,130,246,.12);color:var(--brand-500);font-size:.72rem;font-weight:600;">' + search + ' <span onclick="document.getElementById(\'ciSearch\').value=\'\';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>');
    if (region) tags.push('<span style="padding:2px 10px;border-radius:12px;background:rgba(16,185,129,.12);color:#059669;font-size:.72rem;font-weight:600;">' + region + ' <span onclick="document.getElementById(\'ciRegionFilter\').value=\'\';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>');
    if (riskLvl) tags.push('<span style="padding:2px 10px;border-radius:12px;background:rgba(239,68,68,.12);color:#dc2626;font-size:.72rem;font-weight:600;">' + riskLvl + ' <span onclick="document.getElementById(\'ciRiskFilter\').value=\'\';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>');
    if (currency) tags.push('<span style="padding:2px 10px;border-radius:12px;background:rgba(245,158,11,.12);color:#d97706;font-size:.72rem;font-weight:600;">' + currency + ' <span onclick="document.getElementById(\'ciCurrencyFilter\').value=\'\';CI.applyFilters();" style="cursor:pointer;opacity:.6;">×</span></span>');
    var filterEl = document.getElementById('ciActiveFilters');
    if (filterEl) filterEl.innerHTML = tags.length ? tags.join('') : '<span style="font-size:.75rem;color:var(--text-muted);">None applied</span>';
  }

  function resetFilters() {
    ['ciSearch','ciRegionFilter','ciRiskFilter','ciCurrencyFilter'].forEach(function(id){ var el=document.getElementById(id); if(el) el.value=''; });
    _filteredCountries = _countries.slice();
    buildDt(_filteredCountries);
    updateCount();
    var filterEl = document.getElementById('ciActiveFilters');
    if (filterEl) filterEl.innerHTML = '<span style="font-size:.75rem;color:var(--text-muted);">None applied</span>';
  }

  function updateCount() {
    var el = document.getElementById('ciTableCount');
    if (el) el.textContent = _filteredCountries.length + ' countries';
  }

  function exportTable(type) {
    if (!_ciDt) return;
    var btns = _ciDt.buttons();
    var map = {csv:0, excel:1, pdf:2};
    if (btns && map[type] !== undefined) _ciDt.button(map[type]).trigger();
  }

  async function openProfile(id) {
    var modal = new bootstrap.Modal(document.getElementById('ciProfileModal'));
    document.querySelectorAll('#ciModalTabs .nav-link').forEach(function(l,i){ l.classList.toggle('active',i===0); });
    document.querySelectorAll('#ciProfileModal .tab-pane').forEach(function(p,i){ p.classList.toggle('show',i===0); p.classList.toggle('active',i===0); });
    document.getElementById('ciModalFlag').src = '';
    document.getElementById('ciModalName').textContent = 'Loading…';
    document.getElementById('ciModalMeta').textContent = '–';
    document.getElementById('ciOverviewDetails').innerHTML = '<div class="skeleton" style="height:200px;border-radius:8px;"></div>';
    modal.show();
    try {
      var res = await fetch('/api/countries/' + id);
      var json = await res.json();
      if (!json.status) return;
      var d = json.data, risk = d.risk, eco = d.economic;
      var level = risk ? risk.risk_level : 'Low';
      document.getElementById('ciModalFlag').src = d.flag_url||'';
      document.getElementById('ciModalName').textContent = d.name;
      document.getElementById('ciModalMeta').textContent = (d.code||'–') + ' · ' + (d.region||'–') + ' · ' + (d.currency||'–');
      // Overview
      var overviewFields = [['Country',d.name],['ISO Code',d.code||'–'],['Region',d.region||'–'],['Currency',d.currency||'–'],['Latitude',(d.latitude||'–').toString()],['Longitude',(d.longitude||'–').toString()],['Risk Level','<span class="risk-pill ' + level.toLowerCase() + '">' + level + '</span>'],['Status',statusFromRisk(level)]];
      document.getElementById('ciOverviewDetails').innerHTML = overviewFields.map(function(f){ return '<div class="ci-detail-row"><span class="ci-detail-label">' + f[0] + '</span><span class="ci-detail-value">' + f[1] + '</span></div>'; }).join('');
      // Ports
      var portsEl = document.getElementById('ciPortsList');
      if (d.ports && d.ports.length) {
        portsEl.innerHTML = d.ports.slice(0,8).map(function(p){ return '<div class="ci-detail-row"><span class="ci-detail-label">🚢 ' + p.name + '</span><span class="ci-detail-value" style="font-size:.75rem;">' + (p.harbor_type||'–') + ' · ' + (p.harbor_size||'–') + '</span></div>'; }).join('');
      } else { portsEl.innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;">No port data available</div>'; }
      // Economic
      if (eco) {
        document.getElementById('ciEconomicDetails').innerHTML = [['GDP',fmtGdp(eco.gdp)],['Population',fmtPop(eco.population)],['Inflation',eco.inflation!=null?parseFloat(eco.inflation).toFixed(2)+'%':'–'],['Data Year',eco.year||'–'],['Source','World Bank'],['Last Fetched',eco.updated_at?new Date(eco.updated_at).toLocaleDateString():'–']].map(function(f){return '<div class="ci-detail-row"><span class="ci-detail-label">'+f[0]+'</span><span class="ci-detail-value">'+f[1]+'</span></div>';}).join('');
        document.getElementById('ciTradeDetails').innerHTML = [['Exports',fmtGdp(eco.exports)],['Imports',fmtGdp(eco.imports)],['Trade Balance',eco.exports&&eco.imports?fmtGdp(eco.exports-eco.imports):'–']].map(function(f){return '<div class="ci-detail-row"><span class="ci-detail-label">'+f[0]+'</span><span class="ci-detail-value">'+f[1]+'</span></div>';}).join('');
      } else {
        document.getElementById('ciEconomicDetails').innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;">No economic data available</div>';
        document.getElementById('ciTradeDetails').innerHTML = '';
      }
      // Risk gauge
      if (risk) {
        var score = parseFloat(risk.total_score)||0;
        document.getElementById('ciGaugeLabel').textContent = score.toFixed(1);
        document.getElementById('ciRiskLevelBadge').innerHTML = '<span class="risk-pill ' + level.toLowerCase() + '">' + level + ' Risk</span>';
        var gaugeCtx = document.getElementById('ciGaugeChart');
        if (gaugeCtx) {
          if (_ciGauge) { try{_ciGauge.destroy();}catch(e){} _ciGauge=null; }
          _ciGauge = new Chart(gaugeCtx, {type:'doughnut',data:{datasets:[{data:[score,100-score],backgroundColor:[riskColor(level),'rgba(148,163,184,.15)'],borderWidth:0,circumference:270,rotation:-135}]},options:{responsive:false,plugins:{legend:{display:false}}}});
        }
        var breakdown = [{label:'Weather Risk',score:risk.weather_score,color:'#06b6d4'},{label:'Inflation Risk',score:risk.inflation_score,color:'#f59e0b'},{label:'Political Risk',score:risk.political_score,color:'#ef4444'},{label:'Currency Risk',score:risk.currency_score,color:'#8b5cf6'}];
        document.getElementById('ciRiskBreakdown').innerHTML = breakdown.map(function(b){
          var s = parseFloat(b.score||0);
          return '<div class="mb-3"><div class="d-flex justify-content-between mb-1"><span style="font-size:.8rem;color:var(--text-secondary);">'+b.label+'</span><span style="font-size:.8rem;font-weight:700;color:var(--text-primary);">'+s.toFixed(1)+'</span></div><div class="risk-breakdown-bar"><div class="risk-breakdown-fill" style="width:'+Math.min(s,100)+'%;background:'+b.color+';"></div></div></div>';
        }).join('');
      } else { document.getElementById('ciRiskBreakdown').innerHTML = '<div style="color:var(--text-muted);font-size:.78rem;">No risk data available</div>'; }
      // Map
      if (d.latitude && d.longitude) {
        var lat = parseFloat(d.latitude), lon = parseFloat(d.longitude);
        document.getElementById('ciModalMapContainer').innerHTML = '<iframe src="https://www.openstreetmap.org/export/embed.html?bbox='+(lon-5)+','+(lat-5)+','+(lon+5)+','+(lat+5)+'&layer=mapnik&marker='+lat+','+lon+'" style="width:100%;height:100%;border:none;" loading="lazy" title="Map of '+d.name+'"></iframe>';
        document.getElementById('ciModalMapCoords').textContent = '📍 ' + d.name + ' · Lat: ' + lat.toFixed(4) + ', Lon: ' + lon.toFixed(4);
      } else {
        document.getElementById('ciModalMapContainer').innerHTML = '<div class="text-center py-5 text-muted">No geographic coordinates available.</div>';
      }
      // News
      fetch('/api/news?search=' + encodeURIComponent(d.name)).then(function(r){return r.json();}).then(function(n){
        var newsEl = document.getElementById('ciModalNewsList');
        if (!newsEl) return;
        var articles = n.data||[];
        if (!articles.length) { newsEl.innerHTML = '<div style="color:var(--text-muted);font-size:.83rem;text-align:center;padding:20px;">No news found for this country.</div>'; return; }
        newsEl.innerHTML = articles.slice(0,6).map(function(a){
          var sent = a.sentiment||'Neutral', icon = sent==='Positive'?'📈':sent==='Negative'?'📉':'📰', cls = sent==='Positive'?'low':sent==='Negative'?'high':'medium';
          return '<div style="padding:12px 0;border-bottom:1px solid var(--border-color);display:flex;gap:12px;align-items:flex-start;"><span style="font-size:1.2rem;">'+icon+'</span><div style="flex:1;min-width:0;"><a href="'+(a.url||'#')+'" target="_blank" style="font-size:.83rem;font-weight:600;color:var(--text-primary);text-decoration:none;">'+a.title+'</a><div style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">'+(a.source||'–')+' · '+(a.published_at?new Date(a.published_at).toLocaleDateString():'–')+'</div></div><span class="risk-pill '+cls+'" style="flex-shrink:0;">'+sent+'</span></div>';
        }).join('');
      }).catch(function(){});
    } catch(e) { console.warn('[CI] Profile error:', e); }
  }

  function startAutoRefresh() {
    if (_ciRefreshTimer) clearInterval(_ciRefreshTimer);
    _ciRefreshTimer = setInterval(function() {
      var tog = document.getElementById('ciAutoRefresh');
      if (!tog || !tog.checked) return;
      if (STATE.currentPage !== 'country-intelligence') return;
      refresh(true);
    }, 5 * 60 * 1000);
  }

  async function refresh(silent) {
    var icon = document.getElementById('ciRefreshIcon');
    if (icon) icon.style.animation = 'spin 1s linear infinite';
    try {
      await Promise.all([loadSummary(), loadCountries(), loadTopRisk(), loadTopGdp()]);
      if (!silent) showToast('success','CI Refreshed','Country Intelligence data updated.');
    } finally {
      if (icon) icon.style.animation = '';
    }
  }

  async function init() {
    if (_inited) { setTimeout(function(){ if(_ciMap) _ciMap.invalidateSize(); }, 200); return; }
    _inited = true;
    await Promise.all([loadSummary(), loadCountries(), loadTopRisk(), loadTopGdp()]);
    startAutoRefresh();
  }

  return { init:init, refresh:refresh, applyFilters:applyFilters, resetFilters:resetFilters, filterMap:filterMap, openProfile:openProfile, exportTable:exportTable };
})();