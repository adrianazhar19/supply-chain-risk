<!DOCTYPE html>
<html lang="en" id="htmlRoot" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Supply Chain Risk Intelligence | Enterprise Dashboard</title>
<meta name="description" content="Real-time global supply chain risk monitoring, port intelligence, business news, and economic analytics platform.">

<!-- Preconnect for performance -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link rel="preconnect" href="https://unpkg.com">

<!-- Google Fonts: Inter + Space Grotesk -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<!-- Bootstrap 5.3 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons 1.11 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<!-- Leaflet 1.9 -->
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
<!-- Leaflet MarkerCluster -->
<link href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" rel="stylesheet">
<link href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" rel="stylesheet">
<!-- DataTables Bootstrap5 -->
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<!-- SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<!-- Animate.css -->
<link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════════════════
   DESIGN SYSTEM — CSS CUSTOM PROPERTIES
   ═══════════════════════════════════════════════════════════ */
:root {
  --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  --font-display: 'Space Grotesk', sans-serif;
  --font-mono: 'JetBrains Mono', monospace;

  /* Brand palette */
  --brand-50:  #eff6ff;
  --brand-100: #dbeafe;
  --brand-200: #bfdbfe;
  --brand-400: #60a5fa;
  --brand-500: #3b82f6;
  --brand-600: #2563eb;
  --brand-700: #1d4ed8;
  --brand-800: #1e40af;
  --brand-900: #1e3a8a;

  /* Semantic colours (light) */
  --bg-page:       #f0f4f8;
  --bg-sidebar:    #ffffff;
  --bg-card:       rgba(255,255,255,0.85);
  --bg-card-hover: rgba(255,255,255,0.97);
  --bg-glass:      rgba(255,255,255,0.65);
  --border-color:  rgba(0,0,0,0.07);
  --text-primary:  #0f172a;
  --text-secondary:#475569;
  --text-muted:    #94a3b8;
  --shadow-card:   0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.05);
  --shadow-hover:  0 8px 30px rgba(0,0,0,.12);
  --nav-bg:        linear-gradient(135deg,#0f172a 0%,#1e3a8a 60%,#1e40af 100%);
  --sidebar-w:     260px;

  /* Risk colours */
  --risk-low:      #10b981;
  --risk-medium:   #f59e0b;
  --risk-high:     #ef4444;
  --risk-critical: #7c3aed;

  --radius-card: 16px;
  --radius-sm:   8px;
  --transition:  0.25s cubic-bezier(.4,0,.2,1);
}

[data-theme="dark"] {
  --bg-page:       #0a0f1e;
  --bg-sidebar:    #0d1526;
  --bg-card:       rgba(15,23,42,0.85);
  --bg-card-hover: rgba(15,23,42,0.97);
  --bg-glass:      rgba(15,23,42,0.65);
  --border-color:  rgba(255,255,255,0.06);
  --text-primary:  #f1f5f9;
  --text-secondary:#94a3b8;
  --text-muted:    #475569;
  --shadow-card:   0 1px 3px rgba(0,0,0,.3), 0 4px 16px rgba(0,0,0,.25);
  --shadow-hover:  0 8px 30px rgba(0,0,0,.5);
}

/* ── Base ─────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html { scroll-behavior: smooth; }

body {
  font-family: var(--font-sans);
  background: var(--bg-page);
  color: var(--text-primary);
  transition: background var(--transition), color var(--transition);
  min-height: 100vh;
  overflow-x: hidden;
}

/* ═══════════════════════════════════════════════════════════
   SIDEBAR
   ═══════════════════════════════════════════════════════════ */
.sidebar {
  position: fixed;
  top: 0; left: 0;
  width: var(--sidebar-w);
  height: 100vh;
  background: var(--bg-sidebar);
  border-right: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  z-index: 1040;
  transition: transform var(--transition), width var(--transition);
  overflow-y: auto;
  overflow-x: hidden;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 20px 16px;
  border-bottom: 1px solid var(--border-color);
  text-decoration: none;
}

.sidebar-brand-icon {
  width: 40px; height: 40px;
  background: linear-gradient(135deg, var(--brand-600), #06b6d4);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-size: 1.1rem;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(37,99,235,.35);
}

.sidebar-brand-name {
  font-family: var(--font-display);
  font-size: .8rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.2;
}

.sidebar-brand-name span {
  display: block;
  font-size: .65rem;
  font-weight: 400;
  color: var(--text-muted);
}

.sidebar-nav { padding: 12px 0; flex: 1; }

.nav-section-label {
  font-size: .6rem;
  font-weight: 700;
  letter-spacing: .1em;
  color: var(--text-muted);
  text-transform: uppercase;
  padding: 12px 20px 6px;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 20px;
  color: var(--text-secondary);
  text-decoration: none;
  font-size: .85rem;
  font-weight: 500;
  border-left: 3px solid transparent;
  transition: all var(--transition);
  cursor: pointer;
  border-radius: 0;
}

.sidebar-link:hover {
  color: var(--brand-500);
  background: rgba(59,130,246,.07);
  border-left-color: var(--brand-500);
}

.sidebar-link.active {
  color: var(--brand-600);
  background: rgba(59,130,246,.1);
  border-left-color: var(--brand-600);
  font-weight: 600;
}

.sidebar-link i { width: 18px; text-align: center; font-size: 1rem; }

.sidebar-link .badge-count {
  margin-left: auto;
  font-size: .65rem;
  padding: 2px 7px;
  border-radius: 20px;
  background: var(--brand-600);
  color: #fff;
  font-weight: 600;
}

/* Sidebar footer */
.sidebar-footer {
  padding: 12px 20px 16px;
  border-top: 1px solid var(--border-color);
}

.sidebar-user {
  display: flex;
  align-items: center;
  gap: 10px;
}

.sidebar-avatar {
  width: 36px; height: 36px;
  background: linear-gradient(135deg,var(--brand-600),#06b6d4);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: .85rem;
  flex-shrink: 0;
}

/* Collapsed sidebar on mobile */
.sidebar-collapsed { transform: translateX(-100%); }

/* ═══════════════════════════════════════════════════════════
   TOPBAR
   ═══════════════════════════════════════════════════════════ */
.topbar {
  position: fixed;
  top: 0;
  left: var(--sidebar-w);
  right: 0;
  height: 60px;
  background: var(--bg-sidebar);
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  padding: 0 24px;
  z-index: 1030;
  gap: 16px;
  transition: left var(--transition);
}

.topbar-title {
  font-family: var(--font-display);
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  flex: 1;
}

.topbar-actions { display: flex; align-items: center; gap: 8px; }

.topbar-btn {
  width: 36px; height: 36px;
  border: 1px solid var(--border-color);
  background: transparent;
  border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--transition);
  font-size: .95rem;
}

.topbar-btn:hover {
  background: rgba(59,130,246,.08);
  color: var(--brand-600);
  border-color: var(--brand-200);
}

/* ═══════════════════════════════════════════════════════════
   MAIN CONTENT
   ═══════════════════════════════════════════════════════════ */
.main-content {
  margin-left: var(--sidebar-w);
  margin-top: 60px;
  min-height: calc(100vh - 60px);
  transition: margin-left var(--transition);
}

.content-page {
  display: none;
  padding: 24px;
  animation: fadeInUp .3s ease;
}

.content-page.active { display: block; }

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ═══════════════════════════════════════════════════════════
   CARDS
   ═══════════════════════════════════════════════════════════ */
.card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-card);
  box-shadow: var(--shadow-card);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
}

.card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-hover);
  border-color: rgba(59,130,246,.25);
}

/* Stat cards */
.stat-card {
  padding: 20px;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: -30px; right: -30px;
  width: 100px; height: 100px;
  border-radius: 50%;
  opacity: .06;
  transition: transform var(--transition);
}

.stat-card:hover::before { transform: scale(1.3); }

.stat-card.blue::before   { background: var(--brand-600); }
.stat-card.green::before  { background: #10b981; }
.stat-card.red::before    { background: #ef4444; }
.stat-card.amber::before  { background: #f59e0b; }
.stat-card.purple::before { background: #8b5cf6; }
.stat-card.teal::before   { background: #06b6d4; }
.stat-card.indigo::before { background: #6366f1; }
.stat-card.rose::before   { background: #f43f5e; }

.stat-icon {
  width: 48px; height: 48px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem;
  margin-bottom: 14px;
  transition: transform var(--transition);
}

.stat-card:hover .stat-icon { transform: scale(1.12) rotate(-4deg); }

.stat-icon.blue   { background: rgba(37,99,235,.12); color: var(--brand-600); }
.stat-icon.green  { background: rgba(16,185,129,.12); color: #10b981; }
.stat-icon.red    { background: rgba(239,68,68,.12);  color: #ef4444; }
.stat-icon.amber  { background: rgba(245,158,11,.12); color: #f59e0b; }
.stat-icon.purple { background: rgba(139,92,246,.12); color: #8b5cf6; }
.stat-icon.teal   { background: rgba(6,182,212,.12);  color: #06b6d4; }
.stat-icon.indigo { background: rgba(99,102,241,.12); color: #6366f1; }
.stat-icon.rose   { background: rgba(244,63,94,.12);  color: #f43f5e; }

.stat-label {
  font-size: .7rem;
  font-weight: 600;
  letter-spacing: .07em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin-bottom: 4px;
}

.stat-value {
  font-family: var(--font-display);
  font-size: 1.9rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1;
  margin-bottom: 10px;
  letter-spacing: -.02em;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: .75rem;
  font-weight: 500;
}

.stat-progress {
  height: 4px;
  border-radius: 4px;
  background: var(--border-color);
  overflow: hidden;
  margin-top: 12px;
}

.stat-progress-bar {
  height: 100%;
  border-radius: 4px;
  transition: width 1.2s cubic-bezier(.4,0,.2,1);
}

/* ═══════════════════════════════════════════════════════════
   MAP
   ═══════════════════════════════════════════════════════════ */
#main-map {
  height: 520px;
  border-radius: var(--radius-card);
  border: 1px solid var(--border-color);
  z-index: 1;
}

.leaflet-popup-content-wrapper {
  border-radius: 12px !important;
  box-shadow: 0 8px 30px rgba(0,0,0,.15) !important;
  font-family: var(--font-sans) !important;
}

/* ═══════════════════════════════════════════════════════════
   SECTION HEADERS
   ═══════════════════════════════════════════════════════════ */
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 20px;
}

.section-title {
  font-family: var(--font-display);
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 8px;
}

.section-title i {
  width: 32px; height: 32px;
  border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem;
}

/* ═══════════════════════════════════════════════════════════
   STATUS INDICATORS
   ═══════════════════════════════════════════════════════════ */
.status-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  display: inline-block;
  flex-shrink: 0;
}

.status-dot.online  { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2); animation: pulse-green 2s infinite; }
.status-dot.offline { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.2); }
.status-dot.warning { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.2); }

@keyframes pulse-green {
  0%, 100% { box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
  50%       { box-shadow: 0 0 0 6px rgba(16,185,129,.05); }
}

/* ═══════════════════════════════════════════════════════════
   RISK BADGES
   ═══════════════════════════════════════════════════════════ */
.risk-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: .7rem;
  font-weight: 700;
  letter-spacing: .04em;
}

.risk-pill.low      { background: rgba(16,185,129,.12);  color: #059669; }
.risk-pill.medium   { background: rgba(245,158,11,.12);  color: #d97706; }
.risk-pill.high     { background: rgba(239,68,68,.12);   color: #dc2626; }
.risk-pill.critical { background: rgba(124,58,237,.12);  color: #7c3aed; }

/* ═══════════════════════════════════════════════════════════
   NEWS CARDS
   ═══════════════════════════════════════════════════════════ */
.news-card {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.news-card-img {
  width: 100%;
  height: 160px;
  object-fit: cover;
  background: linear-gradient(135deg, var(--brand-800), #06b6d4);
  flex-shrink: 0;
  border-radius: var(--radius-card) var(--radius-card) 0 0;
}

.news-card-body { padding: 16px; flex: 1; display: flex; flex-direction: column; }
.news-card-title {
  font-weight: 600;
  font-size: .85rem;
  line-height: 1.4;
  color: var(--text-primary);
  margin-bottom: 8px;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
  overflow: hidden;
}

.news-card-desc {
  font-size: .76rem;
  color: var(--text-secondary);
  line-height: 1.5;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
  flex: 1;
}

.news-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid var(--border-color);
  gap: 8px;
  flex-wrap: wrap;
}

/* ═══════════════════════════════════════════════════════════
   WEATHER CARDS
   ═══════════════════════════════════════════════════════════ */
.weather-card-grid { position: relative; overflow: hidden; }

.weather-main-icon {
  font-size: 3.5rem;
  line-height: 1;
}

.weather-stat {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.weather-stat-label { font-size: .65rem; color: var(--text-muted); font-weight: 500; text-transform: uppercase; }
.weather-stat-value { font-size: .95rem; font-weight: 700; color: var(--text-primary); }

.forecast-item {
  text-align: center;
  padding: 10px 8px;
  border-radius: var(--radius-sm);
  background: rgba(0,0,0,.03);
  border: 1px solid var(--border-color);
  transition: all var(--transition);
}

.forecast-item:hover { background: rgba(59,130,246,.06); border-color: rgba(59,130,246,.2); }
.forecast-day { font-size: .65rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
.forecast-icon { font-size: 1.2rem; margin-bottom: 4px; }
.forecast-temp { font-size: .75rem; font-weight: 700; color: var(--text-primary); }

/* ═══════════════════════════════════════════════════════════
   CURRENCY
   ═══════════════════════════════════════════════════════════ */
.currency-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid var(--border-color);
}

.currency-row:last-child { border-bottom: none; }
.currency-code { font-family: var(--font-mono); font-weight: 600; font-size: .85rem; }
.currency-name { font-size: .72rem; color: var(--text-muted); }
.currency-rate { font-family: var(--font-mono); font-weight: 700; font-size: .9rem; }
.currency-change { font-size: .7rem; font-weight: 600; }
.currency-change.up   { color: #10b981; }
.currency-change.down { color: #ef4444; }

/* Currency converter */
.converter-box {
  background: rgba(59,130,246,.05);
  border: 1px solid rgba(59,130,246,.15);
  border-radius: var(--radius-sm);
  padding: 16px;
}

/* ═══════════════════════════════════════════════════════════
   WATCHLIST
   ═══════════════════════════════════════════════════════════ */
.watchlist-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid var(--border-color);
  transition: all var(--transition);
}

.watchlist-item:last-child { border-bottom: none; }
.watchlist-item:hover { padding-left: 4px; }

/* ═══════════════════════════════════════════════════════════
   LOADING SKELETON
   ═══════════════════════════════════════════════════════════ */
@keyframes shimmer {
  0%   { background-position: -400px 0; }
  100% { background-position: 400px 0; }
}

.skeleton {
  background: linear-gradient(90deg,
    rgba(148,163,184,.15) 25%,
    rgba(148,163,184,.3)  50%,
    rgba(148,163,184,.15) 75%);
  background-size: 800px 100%;
  animation: shimmer 1.5s infinite;
  border-radius: var(--radius-sm);
}

/* ═══════════════════════════════════════════════════════════
   NOTIFICATIONS TOAST
   ═══════════════════════════════════════════════════════════ */
.toast-container-custom {
  position: fixed;
  top: 72px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.toast-custom {
  min-width: 320px;
  max-width: 400px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 14px 16px;
  box-shadow: var(--shadow-hover);
  backdrop-filter: blur(12px);
  display: flex;
  align-items: flex-start;
  gap: 12px;
  animation: slideInRight .3s ease, fadeOut .5s ease 4.5s forwards;
}

@keyframes slideInRight {
  from { transform: translateX(100%); opacity: 0; }
  to   { transform: translateX(0);    opacity: 1; }
}
@keyframes fadeOut {
  to { opacity: 0; transform: translateX(100%); }
}

.toast-icon { font-size: 1.1rem; margin-top: 1px; flex-shrink: 0; }
.toast-body { flex: 1; }
.toast-title { font-weight: 600; font-size: .85rem; color: var(--text-primary); }
.toast-desc  { font-size: .78rem; color: var(--text-secondary); margin-top: 2px; }
.toast-close { background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0; font-size: 1rem; transition: color var(--transition); }
.toast-close:hover { color: var(--text-primary); }

/* ═══════════════════════════════════════════════════════════
   CHART CONTAINERS
   ═══════════════════════════════════════════════════════════ */
.chart-wrapper { position: relative; }
.chart-wrapper canvas { max-width: 100%; }

/* ═══════════════════════════════════════════════════════════
   COUNTRY MODAL
   ═══════════════════════════════════════════════════════════ */
.country-modal .modal-content {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 20px;
  backdrop-filter: blur(20px);
}

.risk-breakdown-bar {
  height: 8px;
  border-radius: 4px;
  background: var(--border-color);
  overflow: hidden;
}

.risk-breakdown-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 1s ease;
}

/* ═══════════════════════════════════════════════════════════
   SYSTEM STATUS BAR
   ═══════════════════════════════════════════════════════════ */
.system-status-bar {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: .72rem;
  font-weight: 500;
  color: var(--text-secondary);
  padding: 4px 12px;
  background: rgba(16,185,129,.07);
  border: 1px solid rgba(16,185,129,.15);
  border-radius: 20px;
}

/* ═══════════════════════════════════════════════════════════
   FAB / BACK TO TOP
   ═══════════════════════════════════════════════════════════ */
#backToTop {
  position: fixed;
  bottom: 28px;
  right: 28px;
  width: 44px; height: 44px;
  border-radius: 50%;
  background: var(--brand-600);
  color: #fff;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(37,99,235,.4);
  transition: all var(--transition);
  z-index: 1050;
  display: flex; align-items: center; justify-content: center;
  opacity: 0; pointer-events: none;
  font-size: 1.1rem;
}

#backToTop.visible { opacity: 1; pointer-events: all; }
#backToTop:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(37,99,235,.5); }

/* ═══════════════════════════════════════════════════════════
   SCROLLBAR
   ═══════════════════════════════════════════════════════════ */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(148,163,184,.4); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: rgba(148,163,184,.7); }

/* ═══════════════════════════════════════════════════════════
   UTILITIES
   ═══════════════════════════════════════════════════════════ */
.text-primary-c  { color: var(--text-primary) !important; }
.text-secondary-c{ color: var(--text-secondary) !important; }
.text-muted-c    { color: var(--text-muted) !important; }
.bg-card         { background: var(--bg-card) !important; }
.border-custom   { border: 1px solid var(--border-color) !important; }
.font-mono       { font-family: var(--font-mono); }
.font-display    { font-family: var(--font-display); }

.btn-brand {
  background: linear-gradient(135deg, var(--brand-600), var(--brand-700));
  border: none;
  color: #fff;
  font-weight: 600;
  font-size: .8rem;
  padding: 7px 16px;
  border-radius: var(--radius-sm);
  transition: all var(--transition);
  cursor: pointer;
}

.btn-brand:hover { background: linear-gradient(135deg, var(--brand-700), var(--brand-800)); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,.3); color:#fff; }

.btn-outline-brand {
  background: transparent;
  border: 1px solid var(--brand-600);
  color: var(--brand-600);
  font-weight: 600;
  font-size: .8rem;
  padding: 6px 16px;
  border-radius: var(--radius-sm);
  transition: all var(--transition);
  cursor: pointer;
}

.btn-outline-brand:hover { background: var(--brand-600); color: #fff; transform: translateY(-1px); }

/* DataTable custom */
.dataTables_wrapper .dataTables_filter input {
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-page);
  color: var(--text-primary);
  padding: 6px 12px;
  font-size: .8rem;
}

table.dataTable thead th {
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: var(--text-muted);
  border-bottom: 2px solid var(--border-color) !important;
  background: transparent;
}

table.dataTable tbody td {
  font-size: .83rem;
  color: var(--text-primary);
  border-bottom: 1px solid var(--border-color) !important;
  vertical-align: middle;
}

table.dataTable tbody tr:hover { background: rgba(59,130,246,.04) !important; }

/* Fullscreen */
:-webkit-full-screen .sidebar { display: none; }
:-webkit-full-screen .topbar  { left: 0; }
:-webkit-full-screen .main-content { margin-left: 0; }

/* RESPONSIVE */
@media (max-width: 991px) {
  .sidebar { transform: translateX(-100%); }
  .sidebar.open { transform: translateX(0); }
  .topbar { left: 0; }
  .main-content { margin-left: 0; }
}
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════
     SIDEBAR
     ═══════════════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">
  <a href="#" class="sidebar-brand" onclick="showPage('dashboard'); return false;">
    <div class="sidebar-brand-icon"><i class="bi bi-shield-shaded"></i></div>
    <div class="sidebar-brand-name">
      SCR Intelligence
      <span>Enterprise v2.0</span>
    </div>
  </a>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Overview</div>
    <a class="sidebar-link active" data-page="dashboard" onclick="showPage('dashboard'); return false;" href="#">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="sidebar-link" data-page="map" onclick="showPage('map'); return false;" href="#">
      <i class="bi bi-map"></i> Global Threat Map
    </a>

    <div class="nav-section-label">Intelligence</div>
    <a class="sidebar-link" data-page="risk" onclick="showPage('risk'); return false;" href="#">
      <i class="bi bi-bar-chart-line"></i> Risk Analytics
    </a>
    <a class="sidebar-link" data-page="countries" onclick="showPage('countries'); return false;" href="#">
      <i class="bi bi-globe"></i> Countries
    </a>
    <a class="sidebar-link" data-page="ports" onclick="showPage('ports'); return false;" href="#">
      <i class="bi bi-water"></i> Port Intelligence
    </a>

    <div class="nav-section-label">Market</div>
    <a class="sidebar-link" data-page="news" onclick="showPage('news'); return false;" href="#">
      <i class="bi bi-newspaper"></i> News Center
      <span class="badge-count" id="newsCountBadge">0</span>
    </a>
    <a class="sidebar-link" data-page="currency" onclick="showPage('currency'); return false;" href="#">
      <i class="bi bi-currency-exchange"></i> Exchange Rates
    </a>
    <a class="sidebar-link" data-page="weather" onclick="showPage('weather'); return false;" href="#">
      <i class="bi bi-cloud-sun"></i> Weather Intel
    </a>

    <div class="nav-section-label">User</div>
    <a class="sidebar-link" data-page="watchlist" onclick="showPage('watchlist'); return false;" href="#">
      <i class="bi bi-bookmark-star"></i> My Watchlist
    </a>
    <a class="sidebar-link" data-page="reports" onclick="showPage('reports'); return false;" href="#">
      <i class="bi bi-file-earmark-bar-graph"></i> Reports
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar">{{ substr($user->name ?? 'U', 0, 1) }}</div>
      <div style="flex:1;min-width:0;">
        <div style="font-size:.8rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $user->name ?? 'User' }}</div>
        <div style="font-size:.68rem;color:var(--text-muted);">{{ strtoupper($user->role ?? 'user') }} · Active</div>
      </div>
      <form action="{{ route('logout') }}" method="POST" class="m-0">
        @csrf
        <button type="submit" class="topbar-btn" title="Logout">
          <i class="bi bi-box-arrow-right text-danger"></i>
        </button>
      </form>
    </div>
  </div>
</aside>

<!-- ═══════════════════════════════════════════════════════
     TOPBAR
     ═══════════════════════════════════════════════════════ -->
<header class="topbar" id="topbar">
  <!-- Mobile sidebar toggle -->
  <button class="topbar-btn d-lg-none" id="sidebarToggle">
    <i class="bi bi-list"></i>
  </button>

  <div class="topbar-title" id="currentPageTitle">Dashboard Overview</div>

  <!-- System Status -->
  <div class="system-status-bar d-none d-md-flex" id="systemStatusBar">
    <span class="status-dot online" id="dbStatusDot"></span>
    <span id="systemStatusText">All Systems Online</span>
  </div>

  <div class="topbar-actions">
    <!-- Auto-refresh toggle -->
    <div class="d-flex align-items-center gap-2 me-1">
      <span style="font-size:.72rem;color:var(--text-muted);">Auto</span>
      <div class="form-check form-switch mb-0">
        <input class="form-check-input" type="checkbox" id="autoRefreshToggle" checked title="Auto Refresh Every 60s">
      </div>
    </div>

    <!-- Clock -->
    <div style="font-family:var(--font-mono);font-size:.78rem;color:var(--text-secondary);min-width:75px;" id="liveClock"></div>

    <!-- Notifications bell -->
    <button class="topbar-btn position-relative" id="notifBtn" title="Notifications">
      <i class="bi bi-bell"></i>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="font-size:.55rem;">0</span>
    </button>

    <!-- Theme toggle -->
    <button class="topbar-btn" id="themeToggleBtn" title="Toggle Theme">
      <i class="bi bi-moon-stars" id="themeIcon"></i>
    </button>

    <!-- Fullscreen -->
    <button class="topbar-btn d-none d-sm-flex" id="fullscreenBtn" title="Fullscreen">
      <i class="bi bi-arrows-fullscreen" id="fullscreenIcon"></i>
    </button>

    <!-- Refresh now -->
    <button class="btn-brand" id="refreshNowBtn">
      <i class="bi bi-arrow-clockwise me-1" id="refreshSpinner"></i>Refresh
    </button>
  </div>
</header>

<!-- ═══════════════════════════════════════════════════════
     TOAST CONTAINER
     ═══════════════════════════════════════════════════════ -->
<div class="toast-container-custom" id="toastContainer"></div>

<!-- ═══════════════════════════════════════════════════════
     MAIN CONTENT
     ═══════════════════════════════════════════════════════ -->
<main class="main-content">

<!-- ─── PAGE: DASHBOARD ──────────────────────────────── -->
<section class="content-page active" id="page-dashboard">

  <!-- Stats Row 1 -->
  <div class="row g-3 mb-4">

    <div class="col-6 col-md-4 col-lg-3 col-xl-auto" style="flex:1;min-width:160px;">
      <div class="card stat-card blue h-100" data-bs-toggle="tooltip" title="Total countries monitored">
        <div class="stat-icon blue"><i class="bi bi-globe-americas"></i></div>
        <div class="stat-label">Countries</div>
        <div class="stat-value" id="sc-countries">{{ $countriesCount }}</div>
        <div class="stat-trend"><i class="bi bi-check-circle text-success"></i><span style="color:var(--text-muted)">All regions tracked</span></div>
        <div class="stat-progress"><div class="stat-progress-bar" style="background:var(--brand-500);width:100%"></div></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl-auto" style="flex:1;min-width:160px;">
      <div class="card stat-card green h-100" data-bs-toggle="tooltip" title="Global ports indexed in World Port Index">
        <div class="stat-icon green"><i class="bi bi-water"></i></div>
        <div class="stat-label">Global Ports</div>
        <div class="stat-value" id="sc-ports">{{ $portsCount }}</div>
        <div class="stat-trend"><i class="bi bi-geo-alt text-success"></i><span style="color:var(--text-muted)">WPI registered</span></div>
        <div class="stat-progress"><div class="stat-progress-bar" style="background:#10b981;width:78%"></div></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl-auto" style="flex:1;min-width:160px;">
      <div class="card stat-card red h-100" data-bs-toggle="tooltip" title="Live news articles fetched from NewsAPI">
        <div class="stat-icon red"><i class="bi bi-newspaper"></i></div>
        <div class="stat-label">Live News</div>
        <div class="stat-value" id="sc-news">{{ $newsCount }}</div>
        <div class="stat-trend"><i class="bi bi-circle-fill text-danger" style="font-size:.4rem;"></i><span style="color:#ef4444;font-size:.7rem;">LIVE</span></div>
        <div class="stat-progress"><div class="stat-progress-bar" style="background:#ef4444;width:65%"></div></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl-auto" style="flex:1;min-width:160px;">
      <div class="card stat-card amber h-100" data-bs-toggle="tooltip" title="Exchange rate pairs monitored">
        <div class="stat-icon amber"><i class="bi bi-currency-exchange"></i></div>
        <div class="stat-label">Exchange Rates</div>
        <div class="stat-value" id="sc-currencies">{{ $currenciesCount }}</div>
        <div class="stat-trend"><i class="bi bi-arrow-up-right text-success"></i><span style="color:var(--text-muted)">USD base</span></div>
        <div class="stat-progress"><div class="stat-progress-bar" style="background:#f59e0b;width:55%"></div></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl-auto" style="flex:1;min-width:160px;">
      <div class="card stat-card purple h-100" data-bs-toggle="tooltip" title="Countries with High or Critical risk levels">
        <div class="stat-icon purple"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-label">Risk Alerts</div>
        <div class="stat-value" id="sc-alerts">{{ $riskAlertsCount }}</div>
        <div class="stat-trend"><i class="bi bi-arrow-up text-danger"></i><span style="color:var(--text-muted)">High+Critical</span></div>
        <div class="stat-progress"><div class="stat-progress-bar" style="background:#8b5cf6;width:40%"></div></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl-auto" style="flex:1;min-width:160px;">
      <div class="card stat-card teal h-100" data-bs-toggle="tooltip" title="Database and API connectivity status">
        <div class="stat-icon teal"><i class="bi bi-cpu"></i></div>
        <div class="stat-label">System Health</div>
        <div class="stat-value" style="font-size:1.3rem;padding-top:4px;">
          <span class="status-dot online me-2"></span>Healthy
        </div>
        <div class="stat-trend"><span style="color:var(--text-muted);font-size:.7rem;">{{ $systemHealth['php_version'] }} · Laravel {{ $systemHealth['laravel_version'] }}</span></div>
        <div class="stat-progress"><div class="stat-progress-bar" style="background:#06b6d4;width:90%"></div></div>
      </div>
    </div>

  </div>

  <!-- Map + Pie Row -->
  <div class="row g-4 mb-4">

    <!-- Map preview -->
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
    </div>

    <!-- Risk Pie + Top 5 -->
    <div class="col-xl-4">
      <div class="card p-3 h-100">
        <div class="section-header mb-2">
          <div class="section-title">
            <span class="stat-icon amber" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-pie-chart"></i></span>
            Risk Distribution
          </div>
        </div>
        <div class="chart-wrapper" style="height:180px;">
          <canvas id="dashRiskPie"></canvas>
        </div>
        <div class="mt-3">
          <div style="font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px;">Top Risk Countries</div>
          <div id="dashTopRisk">
            <div class="skeleton" style="height:40px;margin-bottom:8px;"></div>
            <div class="skeleton" style="height:40px;margin-bottom:8px;"></div>
            <div class="skeleton" style="height:40px;"></div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Charts Row -->
  <div class="row g-4 mb-4">

    <!-- Risk Bar Chart -->
    <div class="col-xl-6">
      <div class="card p-3">
        <div class="section-header mb-2">
          <div class="section-title">
            <span class="stat-icon purple" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-bar-chart"></i></span>
            Country Risk Ranking (Top 10)
          </div>
        </div>
        <div class="chart-wrapper" style="height:280px;">
          <canvas id="dashRiskBar"></canvas>
        </div>
      </div>
    </div>

    <!-- Currency Trend -->
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
    </div>

  </div>

  <!-- News + Currency + System Health Row -->
  <div class="row g-4 mb-4">

    <!-- Latest News (compact) -->
    <div class="col-xl-5">
      <div class="card p-3">
        <div class="section-header">
          <div class="section-title">
            <span class="stat-icon red" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-newspaper"></i></span>
            Latest Intelligence News
          </div>
          <button class="btn-outline-brand" onclick="showPage('news')">View All</button>
        </div>
        <div id="dashNewsCompact">
          <div class="skeleton" style="height:70px;margin-bottom:8px;"></div>
          <div class="skeleton" style="height:70px;margin-bottom:8px;"></div>
          <div class="skeleton" style="height:70px;"></div>
        </div>
      </div>
    </div>

    <!-- Currency Quick -->
    <div class="col-xl-4">
      <div class="card p-3">
        <div class="section-header">
          <div class="section-title">
            <span class="stat-icon amber" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-cash-coin"></i></span>
            Exchange Rates (USD)
          </div>
          <span id="currencyLastSync" style="font-size:.7rem;color:var(--text-muted);">–</span>
        </div>
        <div id="dashCurrencyRates">
          @foreach(['EUR','GBP','JPY','CNY','IDR','AUD'] as $c)
          <div class="currency-row" id="rate-row-{{ $c }}">
            <div><div class="currency-code">{{ $c }}</div><div class="currency-name">–</div></div>
            <div class="text-end"><div class="currency-rate font-mono" id="rate-{{ $c }}">–</div></div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- System Health Details -->
    <div class="col-xl-3">
      <div class="card p-3 h-100">
        <div class="section-header mb-3">
          <div class="section-title" style="font-size:.9rem;">
            <span class="stat-icon teal" style="width:28px;height:28px;font-size:.85rem;border-radius:7px;"><i class="bi bi-hdd-network"></i></span>
            System Telemetry
          </div>
        </div>

        <div class="d-flex flex-column gap-3">
          @foreach([
            ['label'=>'Database', 'value'=>$systemHealth['database'], 'icon'=>'bi-database', 'color'=>'green'],
            ['label'=>'Memory',   'value'=>$systemHealth['memory'],   'icon'=>'bi-memory', 'color'=>'blue'],
            ['label'=>'CPU Load', 'value'=>$systemHealth['cpu'],      'icon'=>'bi-cpu', 'color'=>'purple'],
            ['label'=>'Disk',     'value'=>$systemHealth['disk_usage'],'icon'=>'bi-device-hdd', 'color'=>'amber'],
          ] as $item)
          <div class="d-flex align-items-center gap-3">
            <div class="stat-icon {{ $item['color'] }}" style="width:32px;height:32px;font-size:.85rem;border-radius:8px;flex-shrink:0;">
              <i class="bi {{ $item['icon'] }}"></i>
            </div>
            <div style="flex:1;">
              <div style="font-size:.68rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);">{{ $item['label'] }}</div>
              <div style="font-size:.88rem;font-weight:600;color:var(--text-primary);font-family:var(--font-mono);">{{ $item['value'] }}</div>
            </div>
          </div>
          @endforeach
        </div>

        <div class="mt-auto pt-3 border-top" style="border-color:var(--border-color) !important;font-size:.68rem;color:var(--text-muted);">
          <div class="d-flex justify-content-between mb-1">
            <span>Laravel</span><span class="font-mono">v{{ $systemHealth['laravel_version'] }}</span>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span>PHP</span><span class="font-mono">v{{ $systemHealth['php_version'] }}</span>
          </div>
          <div class="d-flex justify-content-between">
            <span>Last Sync</span><span class="font-mono" id="lastSyncTime">{{ $systemHealth['last_sync'] }}</span>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ─── PAGE: FULL MAP ──────────────────────────────── -->
<section class="content-page" id="page-map">
  <div class="card p-3">
    <div class="section-header">
      <div class="section-title">
        <span class="stat-icon teal" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-pin-map"></i></span>
        Global Supply Chain Intelligence Map
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <select class="form-select form-select-sm" id="mapRegionFilter" style="width:140px;">
          <option value="">All Regions</option>
          <option value="Asia">Asia</option>
          <option value="Europe">Europe</option>
          <option value="Americas">Americas</option>
          <option value="Africa">Africa</option>
          <option value="Oceania">Oceania</option>
        </select>
        <select class="form-select form-select-sm" id="mapCountryFilter" style="width:160px;">
          <option value="">All Countries</option>
        </select>
        <select class="form-select form-select-sm" id="mapLayerSelect" style="width:130px;">
          <option value="risk">Risk Overlay</option>
          <option value="ports">Ports Only</option>
          <option value="both">Both</option>
        </select>
        <button class="btn-brand" id="mapSearchBtn"><i class="bi bi-search me-1"></i>Apply</button>
      </div>
    </div>
    <div id="main-map"></div>
    <div class="d-flex gap-3 mt-2 flex-wrap" style="font-size:.72rem;color:var(--text-muted);">
      <span><i class="bi bi-circle-fill me-1" style="color:#10b981;"></i>Low Risk</span>
      <span><i class="bi bi-circle-fill me-1" style="color:#f59e0b;"></i>Medium Risk</span>
      <span><i class="bi bi-circle-fill me-1" style="color:#ef4444;"></i>High Risk</span>
      <span><i class="bi bi-circle-fill me-1" style="color:#7c3aed;"></i>Critical Risk</span>
      <span class="ms-auto"><i class="bi bi-info-circle me-1"></i>Click markers for detailed profiles</span>
    </div>
  </div>
</section>

<!-- ─── PAGE: RISK ANALYTICS ──────────────────────── -->
<section class="content-page" id="page-risk">
  <div class="row g-4">

    <!-- Top 10 Highest Risk -->
    <div class="col-xl-6">
      <div class="card p-3">
        <div class="section-header mb-2">
          <div class="section-title"><span class="stat-icon red" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-bar-chart"></i></span>Top 10 Highest Risk Countries</div>
          <button class="btn-brand" id="recalcRiskBtn"><i class="bi bi-arrow-clockwise me-1"></i>Recalculate</button>
        </div>
        <div class="chart-wrapper" style="height:320px;"><canvas id="riskBarChart"></canvas></div>
      </div>
    </div>

    <!-- Pie Distribution -->
    <div class="col-xl-3">
      <div class="card p-3">
        <div class="section-header mb-2">
          <div class="section-title"><span class="stat-icon amber" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-pie-chart"></i></span>Risk Distribution</div>
        </div>
        <div class="chart-wrapper" style="height:260px;"><canvas id="riskPieChart"></canvas></div>
        <div id="riskPieStats" class="row g-2 mt-2 text-center small"></div>
      </div>
    </div>

    <!-- Radar Chart -->
    <div class="col-xl-3">
      <div class="card p-3">
        <div class="section-header mb-2">
          <div class="section-title"><span class="stat-icon purple" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-reception-4"></i></span>Risk Vectors</div>
        </div>
        <div class="chart-wrapper" style="height:260px;"><canvas id="riskRadarChart"></canvas></div>
      </div>
    </div>

    <!-- Area trend + Top 10 Lowest Risk -->
    <div class="col-xl-6">
      <div class="card p-3">
        <div class="section-header mb-2">
          <div class="section-title"><span class="stat-icon green" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-graph-down-arrow"></i></span>Top 10 Lowest Risk Countries</div>
        </div>
        <div class="chart-wrapper" style="height:280px;"><canvas id="riskLowestBar"></canvas></div>
      </div>
    </div>

    <!-- Risk Table Ledger -->
    <div class="col-xl-6">
      <div class="card p-3">
        <div class="section-header mb-3">
          <div class="section-title"><span class="stat-icon blue" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-table"></i></span>Full Risk Ledger</div>
          <div class="d-flex gap-2">
            <button class="btn-outline-brand" onclick="exportTable('riskLedgerTable','risk_ledger')"><i class="bi bi-filetype-csv me-1"></i>CSV</button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="riskLedgerTable" class="table table-sm w-100">
            <thead><tr><th>Country</th><th>Weather</th><th>Inflation</th><th>Political</th><th>Currency</th><th>Total</th><th>Level</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ─── PAGE: COUNTRIES ──────────────────────────── -->
<section class="content-page" id="page-countries">
  <div class="card p-4">
    <div class="section-header">
      <div class="section-title">
        <span class="stat-icon blue" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-globe"></i></span>
        Country Intelligence Ledger
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <button class="btn-outline-brand" onclick="exportTable('countriesTable','countries')"><i class="bi bi-filetype-csv me-1"></i>CSV</button>
        <button class="btn-outline-brand" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
      </div>
    </div>
    <p style="font-size:.8rem;color:var(--text-secondary);margin-bottom:16px;">Click any row to view detailed country profile including live weather, GDP, and ports.</p>
    <div class="table-responsive">
      <table id="countriesTable" class="table w-100">
        <thead>
          <tr>
            <th>Flag</th><th>Country</th><th>Code</th><th>Region</th>
            <th>Currency</th><th>GDP (B USD)</th><th>Population (M)</th>
            <th>Inflation</th><th>Risk Score</th><th>Status</th><th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</section>

<!-- ─── PAGE: PORTS ──────────────────────────────── -->
<section class="content-page" id="page-ports">
  <div class="card p-4">
    <div class="section-header">
      <div class="section-title">
        <span class="stat-icon green" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-water"></i></span>
        Port Intelligence Database
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <select class="form-select form-select-sm" id="portCountryFilter" style="width:160px;">
          <option value="">All Countries</option>
        </select>
        <select class="form-select form-select-sm" id="portTypeFilter" style="width:130px;">
          <option value="">All Types</option>
          <option value="Major Port">Major Port</option>
          <option value="Canal">Canal</option>
          <option value="Other">Other</option>
        </select>
        <button class="btn-outline-brand" onclick="exportTable('portsTable','ports')"><i class="bi bi-filetype-csv me-1"></i>CSV</button>
      </div>
    </div>
    <div class="table-responsive">
      <table id="portsTable" class="table w-100">
        <thead><tr><th>Port Name</th><th>Country</th><th>Latitude</th><th>Longitude</th><th>Harbor Size</th><th>Harbor Type</th><th>WPI Code</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</section>

<!-- ─── PAGE: NEWS CENTER ─────────────────────────── -->
<section class="content-page" id="page-news">
  <div class="section-header mb-4">
    <div class="section-title" style="font-size:1.2rem;">
      <span class="stat-icon red" style="width:36px;height:36px;font-size:1rem;border-radius:9px;"><i class="bi bi-newspaper"></i></span>
      Supply Chain Intelligence News Center
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <div class="d-flex align-items-center gap-2 me-1" style="font-size:.75rem;color:var(--text-muted);">
        <span class="status-dot online"></span>Live Feed — Auto-refresh 60s
      </div>
      <input type="text" class="form-control form-control-sm" id="newsSearch" placeholder="Search articles..." style="width:220px;">
      <select class="form-select form-select-sm" id="newsSentimentFilter" style="width:130px;">
        <option value="">All Sentiment</option>
        <option value="Positive">Positive</option>
        <option value="Neutral">Neutral</option>
        <option value="Negative">Negative</option>
      </select>
      <button class="btn-brand" id="newsRefreshBtn"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
    </div>
  </div>
  <div class="row g-3" id="newsGrid">
    @for($i=0;$i<8;$i++)
    <div class="col-sm-6 col-lg-4 col-xl-3">
      <div class="card skeleton" style="height:300px;"></div>
    </div>
    @endfor
  </div>
  <div class="text-center mt-4" id="newsLoadMoreWrap" style="display:none;">
    <button class="btn-outline-brand px-5" id="newsLoadMore">Load More Articles</button>
  </div>
</section>

<!-- ─── PAGE: EXCHANGE RATES ──────────────────────── -->
<section class="content-page" id="page-currency">
  <div class="row g-4">

    <!-- Rates table -->
    <div class="col-xl-5">
      <div class="card p-3 h-100">
        <div class="section-header">
          <div class="section-title"><span class="stat-icon amber" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-currency-exchange"></i></span>Live Exchange Rates</div>
          <span id="currencyFullLastSync" style="font-size:.7rem;color:var(--text-muted);">–</span>
        </div>
        <div id="currencyRatesFull">
          @foreach(['EUR'=>'Euro','GBP'=>'British Pound','JPY'=>'Japanese Yen','CNY'=>'Chinese Renminbi','IDR'=>'Indonesian Rupiah','AUD'=>'Australian Dollar','SGD'=>'Singapore Dollar','CAD'=>'Canadian Dollar'] as $code=>$name)
          <div class="currency-row" id="full-rate-row-{{ $code }}">
            <div class="d-flex align-items-center gap-3">
              <div class="stat-icon amber" style="width:36px;height:36px;font-size:.8rem;border-radius:8px;flex-shrink:0;">
                <span style="font-family:var(--font-mono);font-size:.8rem;font-weight:700;">{{ $code }}</span>
              </div>
              <div>
                <div class="currency-code">{{ $code }}/USD</div>
                <div class="currency-name">{{ $name }}</div>
              </div>
            </div>
            <div class="text-end">
              <div class="currency-rate" id="full-rate-{{ $code }}">–</div>
              <div class="currency-change" id="full-change-{{ $code }}">–</div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- Chart + Converter -->
    <div class="col-xl-7">
      <div class="row g-4 h-100">
        <div class="col-12">
          <div class="card p-3">
            <div class="section-header mb-2">
              <div class="section-title"><span class="stat-icon amber" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-graph-up-arrow"></i></span>Currency Trend Chart</div>
              <div class="d-flex gap-2">
                <select class="form-select form-select-sm" id="currencyChartSelect" style="width:100px;">
                  <option value="EUR">EUR</option>
                  <option value="GBP">GBP</option>
                  <option value="JPY">JPY</option>
                  <option value="CNY">CNY</option>
                  <option value="IDR">IDR</option>
                </select>
              </div>
            </div>
            <div class="chart-wrapper" style="height:220px;"><canvas id="currencyTrendChart"></canvas></div>
          </div>
        </div>
        <div class="col-12">
          <div class="card p-3">
            <div class="section-title mb-3"><span class="stat-icon green" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-calculator"></i></span>Currency Converter</div>
            <div class="converter-box">
              <div class="row g-3 align-items-end">
                <div class="col-5">
                  <label class="form-label" style="font-size:.75rem;font-weight:600;color:var(--text-secondary);">Amount (USD)</label>
                  <input type="number" class="form-control form-control-sm" id="convertAmount" value="1000" min="0">
                </div>
                <div class="col-4">
                  <label class="form-label" style="font-size:.75rem;font-weight:600;color:var(--text-secondary);">To Currency</label>
                  <select class="form-select form-select-sm" id="convertTarget">
                    <option value="EUR">EUR</option><option value="GBP">GBP</option>
                    <option value="JPY">JPY</option><option value="CNY">CNY</option>
                    <option value="IDR">IDR</option><option value="AUD">AUD</option>
                    <option value="SGD">SGD</option>
                  </select>
                </div>
                <div class="col-3">
                  <button class="btn-brand w-100" id="convertBtn">Convert</button>
                </div>
                <div class="col-12">
                  <div class="text-center" style="font-size:1.4rem;font-family:var(--font-mono);font-weight:700;color:var(--text-primary);" id="convertResult">–</div>
                  <div class="text-center" style="font-size:.75rem;color:var(--text-muted);" id="convertMeta">Enter amount and click Convert</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ─── PAGE: WEATHER ─────────────────────────────── -->
<section class="content-page" id="page-weather">
  <div class="section-header mb-4">
    <div class="section-title" style="font-size:1.2rem;">
      <span class="stat-icon teal" style="width:36px;height:36px;font-size:1rem;border-radius:9px;"><i class="bi bi-cloud-sun"></i></span>
      Meteorological Intelligence — Open-Meteo
    </div>
    <button class="btn-brand" id="weatherRefreshBtn"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
  </div>
  <div class="row g-3" id="weatherGrid">
    @for($i=0;$i<8;$i++)
    <div class="col-sm-6 col-lg-4 col-xl-3">
      <div class="card skeleton" style="height:280px;"></div>
    </div>
    @endfor
  </div>
</section>

<!-- ─── PAGE: WATCHLIST ──────────────────────────── -->
<section class="content-page" id="page-watchlist">
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="card p-3">
        <div class="section-header">
          <div class="section-title"><span class="stat-icon indigo" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-bookmark-star"></i></span>My Watchlist</div>
          <button class="btn-brand" data-bs-toggle="modal" data-bs-target="#addWatchlistModal">
            <i class="bi bi-plus me-1"></i>Add Country
          </button>
        </div>
        <div id="watchlistItems">
          <div class="text-center py-5" style="color:var(--text-muted);">
            <i class="bi bi-bookmark-star" style="font-size:3rem;opacity:.3;"></i>
            <p class="mt-3">No countries in watchlist. Add countries to monitor their risk levels.</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="card p-3">
        <div class="section-header mb-3">
          <div class="section-title" style="font-size:.9rem;"><span class="stat-icon purple" style="width:28px;height:28px;font-size:.8rem;border-radius:7px;"><i class="bi bi-bell"></i></span>Risk Notifications</div>
        </div>
        <div id="watchlistAlerts" style="max-height:400px;overflow-y:auto;">
          <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
            <i class="bi bi-bell-slash" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>
            No active alerts
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ─── PAGE: REPORTS ─────────────────────────────── -->
<section class="content-page" id="page-reports">
  <div class="row g-4">
    <div class="col-12">
      <div class="card p-4">
        <div class="section-header mb-4">
          <div class="section-title" style="font-size:1.2rem;"><span class="stat-icon blue" style="width:36px;height:36px;font-size:1rem;border-radius:9px;"><i class="bi bi-file-earmark-bar-graph"></i></span>Report Generation Center</div>
        </div>
        <div class="row g-4">
          @foreach([
            ['title'=>'Country Risk Report','desc'=>'Full risk assessment for all 250 countries with scores, levels, and trend analysis.','icon'=>'bi-globe','color'=>'blue','type'=>'countries'],
            ['title'=>'Port Intelligence Report','desc'=>'Complete database of 556+ global ports with harbor types, locations, and country mapping.','icon'=>'bi-water','color'=>'green','type'=>'ports'],
            ['title'=>'News Analysis Report','desc'=>'Sentiment-analyzed supply chain news with positive/negative scoring and source attribution.','icon'=>'bi-newspaper','color'=>'red','type'=>'news'],
            ['title'=>'Exchange Rate Report','desc'=>'Historical and current exchange rates for USD vs major currency pairs.','icon'=>'bi-currency-exchange','color'=>'amber','type'=>'currency'],
          ] as $report)
          <div class="col-md-6 col-xl-3">
            <div class="card p-3 h-100 text-center">
              <div class="stat-icon {{ $report['color'] }}" style="width:56px;height:56px;font-size:1.4rem;border-radius:14px;margin:0 auto 16px;">
                <i class="bi {{ $report['icon'] }}"></i>
              </div>
              <h6 class="fw-bold mb-2" style="color:var(--text-primary);">{{ $report['title'] }}</h6>
              <p style="font-size:.78rem;color:var(--text-secondary);margin-bottom:20px;">{{ $report['desc'] }}</p>
              <div class="d-flex gap-2 justify-content-center mt-auto">
                <button class="btn-brand" onclick="exportData('{{ $report['type'] }}','csv')"><i class="bi bi-filetype-csv me-1"></i>CSV</button>
                <button class="btn-outline-brand" onclick="exportData('{{ $report['type'] }}','pdf')"><i class="bi bi-file-pdf me-1"></i>PDF</button>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

</main>

<!-- ─── COUNTRY DETAIL MODAL ──────────────────────── -->
<div class="modal fade country-modal" id="countryModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:20px;backdrop-filter:blur(20px);">
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center gap-3">
          <img src="" id="modalCountryFlag" width="48" style="border-radius:6px;border:1px solid var(--border-color);" alt="">
          <div>
            <h5 class="modal-title fw-bold mb-0" id="modalCountryName">–</h5>
            <div style="font-size:.75rem;color:var(--text-muted);" id="modalCountryMeta">–</div>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4" id="modalCountryBody">
        <div class="text-center py-5">
          <div class="spinner-border" style="color:var(--brand-600);"></div>
          <p class="mt-3" style="color:var(--text-muted);">Loading country intelligence...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ─── ADD TO WATCHLIST MODAL ────────────────────── -->
<div class="modal fade" id="addWatchlistModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:20px;">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Add to Watchlist</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold" style="font-size:.85rem;">Select Country</label>
          <select class="form-select" id="watchlistCountrySelect">
            <option value="">– Select country –</option>
          </select>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn-outline-brand" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-brand" id="addWatchlistBtn">
          <i class="bi bi-bookmark-plus me-1"></i>Add to Watchlist
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Back to Top -->
<button id="backToTop" title="Back to top"><i class="bi bi-arrow-up"></i></button>

<!-- ─── SCRIPTS ───────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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
    document.getElementById('dashRiskPie').getContext('2d'), {
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

  const tbody = document.querySelector('#countriesTable tbody');
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

  STATE.dt.countries = new DataTable('#countriesTable', {
    responsive:true, pageLength:25, lengthMenu:[10,25,50,100],
    columnDefs:[{orderable:false,targets:[0,10]}],
    language:{ search:'', searchPlaceholder:'Filter countries...', lengthMenu:'Show _MENU_' }
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
</script>
</body>
</html>
