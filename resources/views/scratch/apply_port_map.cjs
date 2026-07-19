/**
 * apply_port_map.cjs  (v2 — CRLF-tolerant)
 */
'use strict';
const fs   = require('fs');
const path = require('path');

const FILE = path.join('resources', 'views', 'dashboard.blade.php');
let src = fs.readFileSync(FILE, 'utf8');

/* normalise to LF for matching, track original line ending */
const CRLF = src.includes('\r\n');
const n = s => s.replace(/\r\n/g,'\n');
const raw = n(src);           // working copy (LF only)

let out = raw;

function replaceOnce(old, rep) {
  if (!out.includes(old)) {
    console.error('NOT FOUND (first 120 chars):\n' + old.slice(0, 120));
    process.exit(1);
  }
  out = out.replace(old, () => rep);
}

/* ══════════════════════════════════════════════════════════════
   1. CSS — inject after  .stat-card:hover .stat-icon { ... }
   ══════════════════════════════════════════════════════════════ */
const CSS_ANCHOR = `.stat-card:hover .stat-icon { transform: scale(1.12) rotate(-4deg); }`;

const MAP_CSS = `
/* ─── Map Layer & Controls CSS ──────────────────────────────── */
.map-layer-btn {
  font-size: .68rem; font-weight: 600; padding: 3px 10px; border-radius: 20px;
  border: 1px solid #dee2e6; background: transparent; color: var(--text-secondary,#475569);
  cursor: pointer; transition: all .15s; white-space: nowrap;
}
.map-layer-btn.active, .map-layer-btn:hover { background: #0d6efd; color: #fff; border-color: #0d6efd; }
.map-ctrl-btn {
  width: 32px; height: 32px; border-radius: 8px; border: none;
  background: rgba(255,255,255,.92); color: #475569;
  box-shadow: 0 2px 8px rgba(0,0,0,.15); font-size: .85rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all .15s; backdrop-filter: blur(4px);
}
.map-ctrl-btn:hover { background: #0d6efd; color: #fff; transform: scale(1.08); }
html[data-theme="dark"] .map-ctrl-btn { background: rgba(30,41,59,.92); color: #94a3b8; }
.map-stat-pill { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:10px;font-weight:700; }
#portSidePanel h6 { font-size:.78rem; font-weight:800; margin-bottom:8px; }
#portSidePanel .psp-row { display:flex;justify-content:space-between;font-size:.7rem;padding:3px 0;border-bottom:1px solid #f1f5f9; }
html[data-theme="dark"] #portSidePanel { background:var(--card-bg); }
html[data-theme="dark"] #portMapSuggestions { background:var(--card-bg);border-color:var(--border-color); }
#mapMainCard.map-fullscreen { position:fixed !important;inset:0;z-index:9990;border-radius:0 !important; }
#mapMainCard.map-fullscreen #dashboard-map { height:calc(100vh - 200px) !important; }`;

replaceOnce(CSS_ANCHOR, CSS_ANCHOR + MAP_CSS);
console.log('1. CSS injected');

/* ══════════════════════════════════════════════════════════════
   2. MAP CARD HTML
   ══════════════════════════════════════════════════════════════ */
const OLD_MAP = `    <!-- Map preview -->
    <div class="col-xl-8">
      <div class="card p-3 h-100">
        <div class="section-header">
          <div class="section-title">
            <span class="stat-icon teal" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-pin-map"></i></span>
            Global Supply Chain Threat Map
          </div>
          <div class="d-flex gap-2">
            <button class="btn-outline-brand" onclick="showPage('map')">
              <i class="bi bi-arrows-fullscreen me-1"></i>Expand
            </button>
          </div>
        </div>
        <div id="dashboard-map" style="height:340px;border-radius:12px;border:1px solid var(--border-color);"></div>
        <div class="d-flex gap-3 mt-2 flex-wrap" style="font-size:.72rem;color:var(--text-muted);">
          <span><i class="bi bi-circle-fill me-1" style="color:#10b981;"></i>Low</span>
          <span><i class="bi bi-circle-fill me-1" style="color:#f59e0b;"></i>Medium</span>
          <span><i class="bi bi-circle-fill me-1" style="color:#ef4444;"></i>High</span>
          <span><i class="bi bi-circle-fill me-1" style="color:#7c3aed;"></i>Critical</span>
          <span><i class="bi bi-ship-cargo me-1 text-blue"></i>Ports</span>
        </div>
      </div>
    </div>`;

const NEW_MAP = `    <!-- Map preview — Professional Port Map -->
    <div class="col-xl-8">
      <div class="card p-3 h-100 d-flex flex-column" id="mapMainCard">

        <!-- Header -->
        <div class="section-header mb-2">
          <div class="section-title">
            <span class="stat-icon teal" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-pin-map"></i></span>
            Global Supply Chain Intelligence Map
          </div>
          <div class="d-flex gap-2 flex-wrap align-items-center">
            <span style="font-size:.7rem;background:#3b82f620;color:#3b82f6;padding:2px 10px;border-radius:10px;font-weight:700;"><i class="bi bi-anchor me-1"></i><span id="mapPortCountVal">0</span> Ports</span>
            <button class="btn-outline-brand" onclick="toggleMapFullscreen()" id="mapFullscreenBtn" title="Fullscreen"><i class="bi bi-arrows-fullscreen me-1" id="mapFullscreenIcon"></i>Expand</button>
            <button class="btn-outline-brand" onclick="showPage('map')" title="Full map page"><i class="bi bi-map me-1"></i>Full Map</button>
          </div>
        </div>

        <!-- Stats Bar -->
        <div class="d-flex gap-2 mb-2 flex-wrap" style="font-size:.7rem;">
          <span class="map-stat-pill" style="background:#3b82f610;color:#3b82f6;border:1px solid #3b82f620;"><i class="bi bi-anchor me-1"></i><b id="msp-total">0</b> Ports</span>
          <span class="map-stat-pill" style="background:#10b98110;color:#10b981;border:1px solid #10b98120;"><i class="bi bi-building me-1"></i><b id="msp-major">0</b> Major</span>
          <span class="map-stat-pill" style="background:#8b5cf610;color:#8b5cf6;border:1px solid #8b5cf620;"><i class="bi bi-box-seam me-1"></i><b id="msp-container">0</b> Container</span>
          <span class="map-stat-pill" style="background:#f59e0b10;color:#b45309;border:1px solid #f59e0b20;"><i class="bi bi-droplet me-1"></i><b id="msp-oil">0</b> Oil</span>
          <span class="map-stat-pill" style="background:#ef444410;color:#ef4444;border:1px solid #ef444420;"><i class="bi bi-exclamation-triangle me-1"></i><b id="msp-highrisk">0</b> High Risk</span>
        </div>

        <!-- Layer Switcher -->
        <div class="d-flex gap-2 mb-2 flex-wrap align-items-center" id="mapLayerSwitcher">
          <span style="font-size:.65rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Layers:</span>
          <button class="map-layer-btn active" data-layer="risk"    onclick="toggleMapLayer('risk',this)"><i class="bi bi-globe me-1"></i>Risk</button>
          <button class="map-layer-btn active" data-layer="ports"   onclick="toggleMapLayer('ports',this)"><i class="bi bi-anchor me-1"></i>Ports</button>
          <button class="map-layer-btn"        data-layer="routes"  onclick="toggleMapLayer('routes',this)"><i class="bi bi-arrow-left-right me-1"></i>Routes</button>
          <button class="map-layer-btn"        data-layer="weather" onclick="toggleMapLayer('weather',this)"><i class="bi bi-cloud-sun me-1"></i>Weather</button>
          <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
            <div style="position:relative;">
              <i class="bi bi-search" style="position:absolute;left:8px;top:50%;transform:translateY(-50%);font-size:.7rem;color:#94a3b8;pointer-events:none;"></i>
              <input type="text" id="portMapSearch" class="form-control form-control-sm" style="border-radius:20px;padding-left:26px;font-size:.72rem;width:150px;" placeholder="Search port..." autocomplete="off">
              <div id="portMapSuggestions" style="position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:9999;background:#fff;border:1px solid #e2e8f0;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,.1);display:none;max-height:200px;overflow-y:auto;"></div>
            </div>
            <select id="portTypeFilter" class="form-select form-select-sm" style="width:auto;font-size:.7rem;border-radius:20px;" onchange="applyPortFilters()">
              <option value="">All Types</option>
              <option value="Commercial">Commercial</option>
              <option value="Container">Container</option>
              <option value="Oil Terminal">Oil Terminal</option>
              <option value="Industrial">Industrial</option>
              <option value="Fishing">Fishing</option>
              <option value="Military">Military</option>
            </select>
            <select id="portSizeFilter" class="form-select form-select-sm" style="width:auto;font-size:.7rem;border-radius:20px;" onchange="applyPortFilters()">
              <option value="">All Sizes</option>
              <option value="Small">Small</option>
              <option value="Medium">Medium</option>
              <option value="Large">Large</option>
              <option value="Very Large">Very Large</option>
            </select>
          </div>
        </div>

        <!-- Map + Side Panel -->
        <div style="position:relative;flex:1;">
          <div id="dashboard-map" style="height:340px;border-radius:12px;border:1px solid var(--border-color);"></div>
          <!-- Floating Controls -->
          <div style="position:absolute;top:10px;left:10px;z-index:900;display:flex;flex-direction:column;gap:4px;">
            <button class="map-ctrl-btn" onclick="resetMapView()" title="Reset View"><i class="bi bi-house"></i></button>
            <button class="map-ctrl-btn" onclick="locateMe()" title="My Location"><i class="bi bi-crosshair"></i></button>
            <button class="map-ctrl-btn" onclick="exportPortsCsv()" title="Export CSV"><i class="bi bi-download"></i></button>
          </div>
          <!-- Port Side Panel -->
          <div id="portSidePanel" style="display:none;position:absolute;top:0;right:0;width:230px;height:340px;background:#fff;border-radius:0 12px 12px 0;box-shadow:-4px 0 20px rgba(0,0,0,.12);z-index:800;overflow-y:auto;padding:14px;">
            <button onclick="closePortPanel()" style="position:absolute;top:8px;right:8px;background:none;border:none;cursor:pointer;color:#94a3b8;font-size:.9rem;"><i class="bi bi-x-lg"></i></button>
            <div id="portSidePanelContent"></div>
          </div>
        </div>

        <!-- Legend -->
        <div class="d-flex gap-3 mt-2 flex-wrap align-items-center" style="font-size:.68rem;color:var(--text-muted);">
          <b style="font-size:.65rem;text-transform:uppercase;letter-spacing:.05em;">Risk:</b>
          <span><i class="bi bi-circle-fill me-1" style="color:#10b981;"></i>Low</span>
          <span><i class="bi bi-circle-fill me-1" style="color:#f59e0b;"></i>Med</span>
          <span><i class="bi bi-circle-fill me-1" style="color:#ef4444;"></i>High</span>
          <span><i class="bi bi-circle-fill me-1" style="color:#7c3aed;"></i>Critical</span>
          <b class="ms-2" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.05em;">Ports:</b>
          <span style="color:#10b981;"><i class="bi bi-circle-fill me-1" style="font-size:.45rem;"></i>Small</span>
          <span style="color:#f59e0b;"><i class="bi bi-circle-fill me-1" style="font-size:.45rem;"></i>Med</span>
          <span style="color:#ef4444;"><i class="bi bi-circle-fill me-1" style="font-size:.45rem;"></i>Large</span>
          <b class="ms-2" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.05em;">Routes:</b>
          <span style="color:#10b981;">&#8212; Normal</span>
          <span style="color:#f59e0b;">&#8212; Busy</span>
          <span style="color:#ef4444;">- - High Risk</span>
        </div>

      </div>
    </div>`;

replaceOnce(n(OLD_MAP), NEW_MAP);
console.log('2. Map card HTML replaced');

/* ══════════════════════════════════════════════════════════════
   3. REPLACE populateMaps()
   ══════════════════════════════════════════════════════════════ */
const OLD_POP = `function populateMaps() {
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
      m.bindPopup(\`
        <div style="font-family:var(--font-sans,sans-serif);min-width:160px;">
          <div style="font-weight:700;font-size:.85rem;margin-bottom:4px;">🚢 \${p.name}</div>
          <hr style="margin:6px 0;">
          <div style="font-size:.75rem;"><b>Country:</b> \${p.country?.name || '–'}</div>
          <div style="font-size:.75rem;"><b>Type:</b> \${p.harbor_type || '–'}</div>
          <div style="font-size:.75rem;"><b>WPI:</b> \${p.wpi_code || '–'}</div>
        </div>
      \`);
      cluster.addLayer(m);
    });

    map.addLayer(riskLayer);
    map.addLayer(cluster);

    setTimeout(() => map.invalidateSize(), 200);
  });
}`;

const NEW_POP = `/* ─── Map Layer State ─────────────────────────────────────────────── */
var MAP_LAYERS = {
  risk:    { enabled: true,  group: null },
  ports:   { enabled: true,  group: null },
  routes:  { enabled: false, group: null },
  weather: { enabled: false, group: null },
};
var PORT_CLUSTER = null;
var PORT_ALL_MARKERS = [];
var PORT_FILTER = { type: '', size: '' };

var SHIPPING_ROUTES = [
  { from:[1.29,103.85],  to:[31.23,121.47], name:'Singapore → Shanghai',  status:'normal',    color:'#10b981' },
  { from:[31.23,121.47], to:[35.10,129.04], name:'Shanghai → Busan',      status:'busy',      color:'#f59e0b' },
  { from:[51.95,4.13],   to:[53.54,9.99],   name:'Rotterdam → Hamburg',   status:'normal',    color:'#10b981' },
  { from:[33.73,-118.26],to:[35.67,139.65], name:'LA → Tokyo',            status:'high_risk', color:'#ef4444' },
  { from:[25.20,55.27],  to:[19.07,72.88],  name:'Dubai → Mumbai',        status:'normal',    color:'#10b981' },
  { from:[31.23,121.47], to:[22.31,114.17], name:'Shanghai → Hong Kong',  status:'busy',      color:'#f59e0b' },
  { from:[51.95,4.13],   to:[51.50,-0.12],  name:'Rotterdam → London',    status:'normal',    color:'#10b981' },
  { from:[-6.20,106.82], to:[22.31,114.17], name:'Jakarta → Hong Kong',   status:'busy',      color:'#f59e0b' },
  { from:[1.29,103.85],  to:[13.76,100.50], name:'Singapore → Bangkok',   status:'normal',    color:'#10b981' },
  { from:[22.31,114.17], to:[35.10,129.04], name:'Hong Kong → Busan',     status:'normal',    color:'#10b981' },
];

function portSizeColor(size) {
  var s = (size||'').toLowerCase();
  if (s.includes('very') || s === 'large') return '#ef4444';
  if (s === 'medium') return '#f59e0b';
  return '#10b981';
}
function portIconSize(size) {
  var s = (size||'').toLowerCase();
  if (s.includes('very')) return 16;
  if (s === 'large') return 13;
  if (s === 'medium') return 10;
  return 8;
}
function portTypeEmoji(type) {
  var t = (type||'').toLowerCase();
  if (t.includes('container')) return '📦';
  if (t.includes('oil')||t.includes('terminal')) return '🛢️';
  if (t.includes('military')) return '⚓';
  if (t.includes('fishing')) return '🎣';
  if (t.includes('industrial')) return '🏭';
  return '🚢';
}
function makePortIcon(p) {
  var color = portSizeColor(p.harbor_size);
  var sz    = portIconSize(p.harbor_size);
  return L.divIcon({
    html: '<div style="width:'+sz+'px;height:'+sz+'px;background:'+color+';border:2px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.35);"></div>',
    className: 'port-icon',
    iconSize: [sz,sz], iconAnchor: [sz/2,sz/2],
  });
}
function buildPortPopup(p) {
  var emoji     = portTypeEmoji(p.harbor_type);
  var sizeColor = portSizeColor(p.harbor_size);
  var code      = (p.country_code||p.country&&p.country.code||'').toLowerCase();
  var flag      = code ? '<img src="https://flagcdn.com/w20/'+code+'.png" style="border-radius:2px;vertical-align:middle;margin-right:3px;">' : '';
  var countryName = p.country&&p.country.name ? p.country.name : (p.country_name||'Unknown');
  return '<div style="font-family:var(--font-sans,sans-serif);min-width:220px;max-width:260px;">'
    +'<div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">'
      +'<span style="font-size:1.4rem;">'+emoji+'</span>'
      +'<div><div style="font-weight:700;font-size:.88rem;">'+(p.name||'Unnamed Port')+'</div>'
        +'<div style="font-size:.7rem;color:#64748b;">'+flag+countryName+'</div>'
      +'</div>'
    +'</div>'
    +'<hr style="margin:5px 0;">'
    +'<div style="font-size:.72rem;line-height:1.8;">'
      +'<div><b>Type:</b> '+(p.harbor_type||'N/A')+'</div>'
      +'<div><b>Size:</b> <span style="color:'+sizeColor+';font-weight:700;">'+(p.harbor_size||'N/A')+'</span></div>'
      +'<div><b>WPI:</b> '+(p.wpi_code||'N/A')+'</div>'
      +'<div><b>Coords:</b> '+parseFloat(p.latitude||0).toFixed(3)+', '+parseFloat(p.longitude||0).toFixed(3)+'</div>'
    +'</div>'
    +'<button onclick="openPortSidePanel('+JSON.stringify(p).replace(/"/g,\'&quot;\')+')" '
      +'style="margin-top:8px;width:100%;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;border:none;padding:5px;border-radius:6px;font-size:.72rem;font-weight:600;cursor:pointer;">'
      +'📊 Port Intelligence'
    +'</button>'
  +'</div>';
}
function openPortSidePanel(p) {
  var panel   = document.getElementById('portSidePanel');
  var content = document.getElementById('portSidePanelContent');
  if (!panel || !content) return;
  var emoji     = portTypeEmoji(p.harbor_type);
  var sizeColor = portSizeColor(p.harbor_size);
  var code      = (p.country_code||p.country&&p.country.code||'').toLowerCase();
  var flag      = code ? '<img src="https://flagcdn.com/w40/'+code+'.png" style="border-radius:4px;width:36px;">' : '';
  var countryName = p.country&&p.country.name ? p.country.name : (p.country_name||'Unknown');
  var country   = (STATE.countries||[]).find(function(c){ return c.name === countryName; }) || {};
  var risk      = country.risk_score ? parseFloat(country.risk_score).toFixed(1) : 'N/A';
  var riskLevel = country.risk_level || 'N/A';
  var rColors   = {Low:'#10b981',Medium:'#f59e0b',High:'#ef4444',Critical:'#7c3aed'};
  var rc        = rColors[riskLevel] || '#94a3b8';
  var gdp       = country.gdp ? '$'+(parseFloat(country.gdp)/1e12).toFixed(2)+'T' : 'N/A';
  content.innerHTML =
    '<div style="text-align:center;margin-bottom:10px;">'
      +'<div style="font-size:2rem;">'+emoji+'</div>'
      +flag
      +'<h6 style="margin-top:6px;font-size:.83rem;font-weight:800;">'+(p.name||'Unnamed Port')+'</h6>'
      +'<div style="font-size:.7rem;color:#64748b;">'+countryName+'</div>'
    +'</div>'
    +'<div class="psp-row"><span>Type</span><b>'+(p.harbor_type||'N/A')+'</b></div>'
    +'<div class="psp-row"><span>Size</span><b style="color:'+sizeColor+';">'+(p.harbor_size||'N/A')+'</b></div>'
    +'<div class="psp-row"><span>WPI Code</span><b>'+(p.wpi_code||'N/A')+'</b></div>'
    +'<div class="psp-row"><span>Latitude</span><b>'+parseFloat(p.latitude||0).toFixed(4)+'</b></div>'
    +'<div class="psp-row"><span>Longitude</span><b>'+parseFloat(p.longitude||0).toFixed(4)+'</b></div>'
    +'<div class="psp-row"><span>Country Risk</span><b style="color:'+rc+';">'+riskLevel+' ('+risk+')</b></div>'
    +'<div class="psp-row"><span>Country GDP</span><b>'+gdp+'</b></div>'
    +'<div style="margin-top:10px;">'
      +'<button onclick="showPage(\'ports\')" style="width:100%;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;border:none;padding:6px;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;margin-bottom:5px;">📍 Open Port Intelligence</button>'
      +'<button onclick="closePortPanel()" style="width:100%;background:#f1f5f9;color:#475569;border:none;padding:5px;border-radius:8px;font-size:.7rem;cursor:pointer;">Close</button>'
    +'</div>';
  panel.style.display = 'block';
}
function closePortPanel() {
  var panel = document.getElementById('portSidePanel');
  if (panel) panel.style.display = 'none';
}
function buildPortCluster(ports) {
  if (PORT_CLUSTER) {
    [STATE.maps.dashboard, STATE.maps.main].filter(Boolean).forEach(function(m){ try { m.removeLayer(PORT_CLUSTER); } catch(e){} });
  }
  PORT_ALL_MARKERS = [];
  var cluster = L.markerClusterGroup({
    showCoverageOnHover: false, maxClusterRadius: 60, chunkedLoading: true, spiderfyOnMaxZoom: true,
    iconCreateFunction: function(c) {
      var cnt = c.getChildCount();
      var bg  = cnt > 50 ? '#ef4444' : cnt > 20 ? '#f59e0b' : '#3b82f6';
      return L.divIcon({
        html: '<div style="width:34px;height:34px;border-radius:50%;background:'+bg+';color:#fff;display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.25);">'+cnt+'</div>',
        className:'', iconSize:[34,34], iconAnchor:[17,17],
      });
    },
  });
  ports.forEach(function(p) {
    if (!p.latitude || !p.longitude) return;
    var marker = L.marker([parseFloat(p.latitude), parseFloat(p.longitude)], { icon: makePortIcon(p), portData: p });
    marker.bindPopup(buildPortPopup(p), { maxWidth: 280 });
    marker.bindTooltip('<b>'+(p.name||'Port')+'</b><br><small>'+(p.harbor_type||'')+' · '+(p.harbor_size||'')+'</small>', { direction:'top', offset:[0,-6] });
    cluster.addLayer(marker);
    PORT_ALL_MARKERS.push({ marker: marker, port: p });
  });
  PORT_CLUSTER = cluster;
  return cluster;
}
function applyPortFilters() {
  PORT_FILTER.type = (document.getElementById('portTypeFilter')||{}).value || '';
  PORT_FILTER.size = (document.getElementById('portSizeFilter')||{}).value || '';
  var filtered = (STATE.ports||[]).filter(function(p) {
    if (PORT_FILTER.type && (p.harbor_type||'') !== PORT_FILTER.type) return false;
    if (PORT_FILTER.size && (p.harbor_size||'') !== PORT_FILTER.size) return false;
    return true;
  });
  var nc = buildPortCluster(filtered);
  MAP_LAYERS.ports.group = nc;
  [STATE.maps.dashboard, STATE.maps.main].filter(Boolean).forEach(function(m){
    if (MAP_LAYERS.ports.enabled) m.addLayer(nc);
  });
  updateMapStats(filtered);
}
function updateMapStats(ports) {
  var all = ports || STATE.ports || [];
  var major = all.filter(function(p){ var s=(p.harbor_size||'').toLowerCase(); return s==='large'||s.includes('very'); });
  var cont  = all.filter(function(p){ return (p.harbor_type||'').toLowerCase().includes('container'); });
  var oil   = all.filter(function(p){ var t=(p.harbor_type||'').toLowerCase(); return t.includes('oil')||t.includes('terminal'); });
  var hiR   = all.filter(function(p){
    var name = p.country&&p.country.name ? p.country.name : (p.country_name||'');
    var c = (STATE.countries||[]).find(function(co){ return co.name===name; });
    return c && (c.risk_level==='High'||c.risk_level==='Critical');
  });
  function st(id,v){ var el=document.getElementById(id); if(el) el.textContent=v; }
  st('msp-total',all.length); st('msp-major',major.length);
  st('msp-container',cont.length); st('msp-oil',oil.length);
  st('msp-highrisk',hiR.length); st('mapPortCountVal',all.length);
}
function buildRoutesLayer() {
  var layer = L.layerGroup();
  SHIPPING_ROUTES.forEach(function(r) {
    var line = L.polyline([r.from, r.to], { color:r.color, weight:2.5, opacity:.7, dashArray: r.status==='high_risk'?'6 4':null });
    line.bindTooltip('<b>'+r.name+'</b><br>Status: '+r.status, { sticky:true });
    layer.addLayer(line);
    var mid = [(r.from[0]+r.to[0])/2, (r.from[1]+r.to[1])/2];
    layer.addLayer(L.circleMarker(mid, { radius:3, color:r.color, fillColor:r.color, fillOpacity:1, weight:1 }));
  });
  return layer;
}
function toggleMapLayer(name, btn) {
  MAP_LAYERS[name].enabled = !MAP_LAYERS[name].enabled;
  if (btn) btn.classList.toggle('active', MAP_LAYERS[name].enabled);
  [STATE.maps.dashboard, STATE.maps.main].filter(Boolean).forEach(function(m) {
    var grp = MAP_LAYERS[name].group;
    if (!grp) return;
    try { if (MAP_LAYERS[name].enabled) m.addLayer(grp); else m.removeLayer(grp); } catch(e){}
  });
}
function resetMapView() {
  [STATE.maps.dashboard, STATE.maps.main].filter(Boolean).forEach(function(m){ m.setView([15,10],2); });
}
function locateMe() {
  if (!navigator.geolocation) { showToast('warning','Geolocation','Not supported.'); return; }
  navigator.geolocation.getCurrentPosition(function(pos) {
    var ll = [pos.coords.latitude, pos.coords.longitude];
    [STATE.maps.dashboard, STATE.maps.main].filter(Boolean).forEach(function(m){ m.setView(ll,8); });
    showToast('success','Located','Map centered to your location.');
  }, function(){ showToast('warning','Geolocation','Permission denied.'); });
}
function toggleMapFullscreen() {
  var card = document.getElementById('mapMainCard');
  var icon = document.getElementById('mapFullscreenIcon');
  var btn  = document.getElementById('mapFullscreenBtn');
  if (!card) return;
  var full = card.classList.toggle('map-fullscreen');
  if (icon) icon.className = full ? 'bi bi-fullscreen-exit me-1' : 'bi bi-arrows-fullscreen me-1';
  if (btn)  btn.innerHTML  = full ? '<i class="bi bi-fullscreen-exit me-1"></i>Exit' : '<i class="bi bi-arrows-fullscreen me-1"></i>Expand';
  setTimeout(function(){ [STATE.maps.dashboard, STATE.maps.main].filter(Boolean).forEach(function(m){ m.invalidateSize(); }); }, 300);
}
function exportPortsCsv() {
  var ports = STATE.ports||[];
  if (!ports.length) { showToast('warning','No Data','Port data not loaded yet.'); return; }
  var rows = [['Name','Country','WPI Code','Harbor Type','Harbor Size','Latitude','Longitude']];
  ports.forEach(function(p){
    var cn = p.country&&p.country.name ? p.country.name : (p.country_name||'');
    rows.push([p.name||'',cn,p.wpi_code||'',p.harbor_type||'',p.harbor_size||'',
               parseFloat(p.latitude||0).toFixed(4), parseFloat(p.longitude||0).toFixed(4)]);
  });
  var csv = rows.map(function(r){ return r.map(function(c){ return '"'+String(c).replace(/"/g,'""')+'"'; }).join(','); }).join('\n');
  var blob = new Blob([csv],{type:'text/csv'});
  var url  = URL.createObjectURL(blob);
  var a    = document.createElement('a');
  a.href=url; a.download='ports_export.csv'; a.click();
  URL.revokeObjectURL(url);
  showToast('success','Exported','Port data downloaded as CSV.');
}
function initPortSearch() {
  var input   = document.getElementById('portMapSearch');
  var suggest = document.getElementById('portMapSuggestions');
  if (!input || !suggest || input.__wiredPort) return;
  input.__wiredPort = true;
  input.addEventListener('input', function() {
    var q = this.value.trim().toLowerCase();
    if (!q || q.length < 2) { suggest.style.display='none'; return; }
    var matches = (STATE.ports||[]).filter(function(p){
      return (p.name||'').toLowerCase().includes(q)
          || (p.wpi_code||'').toLowerCase().includes(q)
          || (p.harbor_type||'').toLowerCase().includes(q)
          || (p.country&&p.country.name?p.country.name:'').toLowerCase().includes(q)
          || (p.country_name||'').toLowerCase().includes(q);
    }).slice(0,8);
    if (!matches.length) { suggest.innerHTML='<div style="padding:8px 12px;font-size:.75rem;color:#94a3b8;">No ports found</div>'; suggest.style.display='block'; return; }
    suggest.innerHTML = matches.map(function(p){
      var cn=p.country&&p.country.name?p.country.name:(p.country_name||'');
      return '<div style="padding:8px 12px;cursor:pointer;font-size:.75rem;border-bottom:1px solid #f1f5f9;" '
        +'onmousedown="zoomToPort('+JSON.stringify(p).replace(/"/g,\'&quot;')+')">'
        +'<b>'+(p.name||'')+'</b> <span style="color:#94a3b8;font-size:.68rem;">· '+(p.harbor_type||'')+'</span>'
        +'<div style="color:#94a3b8;font-size:.65rem;">'+cn+' · WPI: '+(p.wpi_code||'N/A')+'</div>'
      +'</div>';
    }).join('');
    suggest.style.display = 'block';
  });
  document.addEventListener('click', function(e){
    if (!document.getElementById('portMapSearch')?.contains(e.target)) suggest.style.display='none';
  }, true);
}
function zoomToPort(p) {
  var suggest = document.getElementById('portMapSuggestions');
  var input   = document.getElementById('portMapSearch');
  if (suggest) suggest.style.display='none';
  if (input)   input.value = p.name||'';
  if (!p.latitude || !p.longitude) return;
  [STATE.maps.dashboard, STATE.maps.main].filter(Boolean).forEach(function(m){ m.setView([parseFloat(p.latitude),parseFloat(p.longitude)],10); });
  openPortSidePanel(p);
}

function populateMaps() {
  var maps = [STATE.maps.dashboard, STATE.maps.main].filter(Boolean);
  if (!maps.length) return;

  maps.forEach(function(map) {
    map.eachLayer(function(l){ if (!(l instanceof L.TileLayer)) map.removeLayer(l); });
  });

  // Risk layer
  var riskGrp = L.layerGroup();
  (STATE.risks||[]).forEach(function(r) {
    if (!r.country||!r.country.latitude||!r.country.longitude) return;
    var color  = riskColor(r.risk_level);
    var score  = parseFloat(r.total_score);
    var circle = L.circleMarker([r.country.latitude, r.country.longitude], {
      radius: 7 + score/9, fillColor: color, color:'#fff', weight:1.5, fillOpacity:0.65,
    });
    circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
    riskGrp.addLayer(circle);
  });
  MAP_LAYERS.risk.group = riskGrp;

  // Port layer
  var portCluster = buildPortCluster(STATE.ports||[]);
  MAP_LAYERS.ports.group = portCluster;

  // Routes layer
  MAP_LAYERS.routes.group = buildRoutesLayer();

  // Apply all enabled layers
  maps.forEach(function(map) {
    Object.keys(MAP_LAYERS).forEach(function(name) {
      if (MAP_LAYERS[name].enabled && MAP_LAYERS[name].group) {
        map.addLayer(MAP_LAYERS[name].group);
      }
    });
    setTimeout(function(){ map.invalidateSize(); }, 200);
  });

  updateMapStats(STATE.ports||[]);
  initPortSearch();
}`;

replaceOnce(n(OLD_POP), NEW_POP);
console.log('3. populateMaps() replaced');

/* ══════════════════════════════════════════════════════════════
   4. SAVE
   ══════════════════════════════════════════════════════════════ */
const final = CRLF ? out.replace(/\n/g, '\r\n') : out;
fs.writeFileSync(FILE, final, 'utf8');
console.log('\nSaved dashboard.blade.php — Professional Port Map complete!');
