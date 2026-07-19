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
#dashboardMap {
  height: 600px;
  width: 100%;
  border-radius: 18px;
  overflow: hidden;
  z-index: 1;
}

/* ─── Upgraded Stat Cards Interactivity ────────────────────── */
.stat-card {
  position: relative;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.25s ease, box-shadow 0.25s ease !important;
}
.stat-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.08), 0 4px 6px rgba(0,0,0,0.04) !important;
}
.stat-card .stat-icon {
  transition: transform 0.25s ease;
}
.stat-card:hover .stat-icon {
  transform: scale(1.1);
}
.stat-card-view {
  position: absolute;
  bottom: 8px;
  right: 12px;
  font-size: 0.65rem;
  font-weight: 600;
  opacity: 0;
  transform: translateX(4px);
  transition: opacity 0.25s ease, transform 0.25s ease;
  color: var(--text-secondary, #475569);
}
.stat-card:hover .stat-card-view {
  opacity: 1;
  transform: translateX(0);
}
.stat-card-arrow {
  position: absolute;
  top: 12px;
  right: 12px;
  font-size: 0.8rem;
  color: var(--text-muted, #94a3b8);
  transition: color 0.25s ease, transform 0.25s ease;
}
.stat-card:hover .stat-card-arrow {
  color: var(--text-primary);
  transform: translate(2px, -2px);
}
/* Ripple effect styles */
.stat-card .ripple {
  position: absolute;
  border-radius: 50%;
  transform: scale(0);
  animation: ripple-effect 0.5s ease-out;
  background: rgba(0, 0, 0, 0.08);
  pointer-events: none;
}
@keyframes ripple-effect {
  to {
    transform: scale(4);
    opacity: 0;
  }
}
html[data-theme="dark"] .stat-card .ripple {
  background: rgba(255, 255, 255, 0.08);
}

/* ─── Dedicated Port Map Styles ────────────────────────────── */
#page-map {
  position: relative;
}
.port-map-wrapper {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
  border: 1px solid var(--border-color);
}
#globalThreatMap {
  width: 100%;
  height: 550px;
  border-radius: 18px;
  z-index: 1;
}

/* Glassmorphism Control Panel */
.glass-control-panel {
  position: absolute;
  top: 20px;
  left: 20px;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(16px) saturate(180%);
  -webkit-backdrop-filter: blur(16px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.5);
  border-radius: 16px;
  padding: 16px;
  width: 280px;
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.08);
  transition: all 0.3s ease;
}
html[data-theme="dark"] .glass-control-panel {
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
}

/* Floating Search Bar */
.floating-search-bar {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 1000;
  width: 320px;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.6);
  border-radius: 30px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  display: flex;
  align-items: center;
  padding: 4px 16px;
}
html[data-theme="dark"] .floating-search-bar {
  background: rgba(30, 41, 59, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}
.floating-search-input {
  border: none;
  background: transparent;
  width: 100%;
  padding: 8px 4px;
  font-size: 0.82rem;
  color: var(--text-primary);
  outline: none !important;
}

/* Custom Legend Overlay */
.port-map-legend {
  position: absolute;
  bottom: 20px;
  left: 20px;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.5);
  border-radius: 12px;
  padding: 12px 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  font-size: 0.72rem;
  display: flex;
  flex-direction: column;
  gap: 8px;
  color: var(--text-secondary);
}
html[data-theme="dark"] .port-map-legend {
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
}
.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}
/* Dashboard Map Legend */
.dashboard-map-legend {
  position: absolute;
  bottom: 25px;
  right: 25px;
  background: var(--bg-glass);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 10px 14px;
  z-index: 1000;
  box-shadow: 0 8px 32px rgba(0,0,0,0.15);
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: .7rem;
}
.animate-legend-blink {
  animation: marker-blink 1.2s infinite ease-in-out;
}
@keyframes marker-blink {
  0% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(0.9); }
  100% { opacity: 1; transform: scale(1); }
}
.animated-marker-blinking {
  animation: marker-blink 1.2s infinite ease-in-out;
}
.animated-marker-normal {
  transition: transform 0.2s ease, filter 0.2s ease;
}
.animated-marker-normal:hover, .animated-marker-blinking:hover {
  transform: scale(1.2) !important;
  filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
}

/* Quick Country Comparison Suggestions list */
.comparison-suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 1100;
  display: none;
}
.comparison-suggestions div {
  padding: 6px 12px;
  cursor: pointer;
  font-size: .8rem;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 8px;
}
.comparison-suggestions div:hover {
  background: var(--border-color);
}
.fade-in-el {
  animation: fadeIn 0.4s ease-in-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Stats Banner above Map */
.port-map-stats-banner {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
  overflow-x: auto;
  padding-bottom: 4px;
}
.port-stat-card {
  flex: 1;
  min-width: 150px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01), 0 2px 4px -1px rgba(0,0,0,0.01);
  transition: transform 0.2s ease;
}
.port-stat-card:hover {
  transform: translateY(-2px);
}
.port-stat-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}
.port-stat-label {
  font-size: 0.68rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-muted);
}
.port-stat-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.2;
}

/* Map suggestions overlay */
.port-search-suggestions {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  max-height: 240px;
  overflow-y: auto;
  z-index: 1005;
  display: none;
}
.suggestion-row {
  padding: 10px 16px;
  cursor: pointer;
  font-size: 0.78rem;
  border-bottom: 1px solid var(--border-color);
  transition: background 0.15s;
}
.suggestion-row:hover {
  background: rgba(59,130,246,0.06);
}
.suggestion-row:last-child {
  border-bottom: none;
}

/* Animations */
@keyframes marker-pulse {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.1); opacity: 0.8; }
  100% { transform: scale(1); opacity: 1; }
}
.leaflet-marker-icon.animated-marker {
  animation: marker-pulse 2s infinite ease-in-out;
}

/* Map Layer & Controls CSS */
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
#mapMainCard.map-fullscreen #dashboardMap { height:calc(100vh - 200px) !important; }

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

/* ═══════════════════════════════════════════════════════════
   PORT MAP STYLES
   ═══════════════════════════════════════════════════════════ */
.port-map-stats-banner {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.port-stat-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-card);
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: var(--shadow-card);
  transition: all var(--transition);
}

.port-stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-hover);
}

.port-stat-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.port-stat-label {
  font-size: .75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: var(--text-muted);
}

.port-stat-value {
  font-size: 1.3rem;
  font-weight: 800;
  color: var(--text-primary);
  font-family: var(--font-display);
}

.port-map-wrapper {
  position: relative;
  border-radius: var(--radius-card);
  overflow: hidden;
  box-shadow: var(--shadow-card);
  height: 600px;
  border: 1px solid var(--border-color);
}

#globalThreatMap {
  width: 100%;
  height: 100%;
  z-index: 1;
}

/* Glassmorphic Control Panel */
.glass-control-panel {
  position: absolute;
  top: 20px;
  left: 20px;
  width: 250px;
  background: var(--bg-glass);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-card);
  padding: 20px;
  z-index: 1000;
  box-shadow: 0 8px 32px rgba(0,0,0,0.15);
}

/* Floating Search Bar */
.floating-search-bar {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 320px;
  background: var(--bg-glass);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--border-color);
  border-radius: 30px;
  padding: 8px 20px;
  z-index: 1000;
  display: flex;
  align-items: center;
  box-shadow: 0 8px 32px rgba(0,0,0,0.15);
}

.floating-search-input {
  background: transparent;
  border: none;
  width: 100%;
  color: var(--text-primary);
  font-size: .82rem;
  outline: none;
}

.floating-search-input::placeholder {
  color: var(--text-muted);
}

/* Autocomplete suggestions */
.port-search-suggestions {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  background: var(--bg-glass);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid var(--border-color);
  border-radius: 16px;
  max-height: 250px;
  overflow-y: auto;
  z-index: 1010;
  box-shadow: 0 12px 40px rgba(0,0,0,0.2);
  display: none;
}

.suggestion-row {
  padding: 10px 16px;
  cursor: pointer;
  border-bottom: 1px solid var(--border-color);
  transition: background var(--transition);
}

.suggestion-row:last-child {
  border-bottom: none;
}

.suggestion-row:hover {
  background: rgba(59,130,246,0.1);
}

.suggestion-row b {
  font-size: .8rem;
  color: var(--text-primary);
}

/* Legend Overlay */
.port-map-legend {
  position: absolute;
  bottom: 20px;
  right: 20px;
  background: var(--bg-glass);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 12px 16px;
  z-index: 1000;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: .72rem;
  font-weight: 500;
  color: var(--text-secondary);
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

/* Stat card interactivity */
.stat-card {
  position: relative;
  overflow: hidden;
  cursor: pointer;
}

.stat-card-arrow {
  position: absolute;
  top: 12px;
  right: 12px;
  font-size: .8rem;
  color: var(--text-muted);
  opacity: 0;
  transform: translate(-3px, 3px);
  transition: all var(--transition);
}

.stat-card:hover .stat-card-arrow {
  opacity: 1;
  transform: translate(0, 0);
  color: var(--brand-500);
}

.stat-card-view {
  position: absolute;
  bottom: 12px;
  right: 12px;
  font-size: .65rem;
  font-weight: 700;
  color: var(--brand-500);
  opacity: 0;
  transform: translateY(3px);
  transition: all var(--transition);
}

.stat-card:hover .stat-card-view {
  opacity: 1;
  transform: translateY(0);
}

.stat-card .ripple {
  position: absolute;
  border-radius: 50%;
  transform: scale(0);
  animation: ripple-effect 0.5s ease-out;
  background: rgba(59,130,246,0.15);
  pointer-events: none;
}

@keyframes ripple-effect {
  to {
    transform: scale(2.5);
    opacity: 0;
  }
}

/* Color coloring for Leaflet marker images */
.port-marker-low {
  filter: hue-rotate(130deg) saturate(1.8) brightness(0.9);
}
.port-marker-medium {
  filter: hue-rotate(50deg) saturate(2) brightness(1);
}
.port-marker-high {
  filter: hue-rotate(25deg) saturate(2) brightness(1);
}
.port-marker-critical {
  filter: hue-rotate(0deg) saturate(1.5) brightness(0.9);
}

/* Side sliding detail panel */
.port-side-panel {
  position: absolute;
  top: 20px;
  bottom: 20px;
  left: 20px;
  width: 280px;
  background: var(--bg-glass);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-card);
  padding: 20px;
  z-index: 1005;
  box-shadow: 0 8px 32px rgba(0,0,0,0.25);
  display: none;
  overflow-y: auto;
}

.psp-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid var(--border-color);
  font-size: .72rem;
}
.psp-row span {
  color: var(--text-secondary);
}
.psp-row b {
  color: var(--text-primary);
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
      <i class="bi bi-anchor"></i> Global Port Map
    </a>

    <div class="nav-section-label">Intelligence</div>
    <a class="sidebar-link" data-page="risk" onclick="showPage('risk'); return false;" href="#">
      <i class="bi bi-bar-chart-line"></i> Risk Analytics
    </a>
    <a class="sidebar-link" data-page="compare-countries" onclick="showPage('compare-countries'); return false;" href="#">
      <i class="bi bi-arrow-left-right"></i> Compare Countries
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
      <div class="card stat-card blue h-100" data-bs-toggle="tooltip" title="Total countries monitored" data-target-page="countries">
        <div class="stat-icon blue"><i class="bi bi-globe-americas"></i></div>
        <div class="stat-label">Countries</div>
        <div class="stat-value" id="sc-countries">{{ $countriesCount }}</div>
        <div class="stat-trend"><i class="bi bi-check-circle text-success"></i><span style="color:var(--text-muted)">All regions tracked</span></div>
        <div class="stat-progress"><div class="stat-progress-bar" style="background:var(--brand-500);width:100%"></div></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl-auto" style="flex:1;min-width:160px;">
      <div class="card stat-card green h-100" data-bs-toggle="tooltip" title="Global ports indexed in World Port Index" data-target-page="ports">
        <div class="stat-icon green"><i class="bi bi-water"></i></div>
        <div class="stat-label">Global Ports</div>
        <div class="stat-value" id="sc-ports">{{ $portsCount }}</div>
        <div class="stat-trend"><i class="bi bi-geo-alt text-success"></i><span style="color:var(--text-muted)">WPI registered</span></div>
        <div class="stat-progress"><div class="stat-progress-bar" style="background:#10b981;width:78%"></div></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl-auto" style="flex:1;min-width:160px;">
      <div class="card stat-card red h-100" data-bs-toggle="tooltip" title="Live news articles fetched from NewsAPI" data-target-page="news">
        <div class="stat-icon red"><i class="bi bi-newspaper"></i></div>
        <div class="stat-label">Live News</div>
        <div class="stat-value" id="sc-news">{{ $newsCount }}</div>
        <div class="stat-trend"><i class="bi bi-circle-fill text-danger" style="font-size:.4rem;"></i><span style="color:#ef4444;font-size:.7rem;">LIVE</span></div>
        <div class="stat-progress"><div class="stat-progress-bar" style="background:#ef4444;width:65%"></div></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl-auto" style="flex:1;min-width:160px;">
      <div class="card stat-card amber h-100" data-bs-toggle="tooltip" title="Exchange rate pairs monitored" data-target-page="currency">
        <div class="stat-icon amber"><i class="bi bi-currency-exchange"></i></div>
        <div class="stat-label">Exchange Rates</div>
        <div class="stat-value" id="sc-currencies">{{ $currenciesCount }}</div>
        <div class="stat-trend"><i class="bi bi-arrow-up-right text-success"></i><span style="color:var(--text-muted)">USD base</span></div>
        <div class="stat-progress"><div class="stat-progress-bar" style="background:#f59e0b;width:55%"></div></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl-auto" style="flex:1;min-width:160px;">
      <div class="card stat-card purple h-100" data-bs-toggle="tooltip" title="Countries with High or Critical risk levels" data-target-page="risk">
        <div class="stat-icon purple"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-label">Risk Alerts</div>
        <div class="stat-value" id="sc-alerts">{{ $riskAlertsCount }}</div>
        <div class="stat-trend"><i class="bi bi-arrow-up text-danger"></i><span style="color:var(--text-muted)">High+Critical</span></div>
        <div class="stat-progress"><div class="stat-progress-bar" style="background:#8b5cf6;width:40%"></div></div>
      </div>
    </div>



  </div>

  <!-- Map + Pie Row -->
  <div class="row g-4 mb-4">

    <!-- Map preview -->
    <div class="col-xl-8">
      <div class="card p-3 h-100 position-relative" style="overflow:hidden;">
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
        
        <!-- Dashboard Map Legend (Step 6) -->
        <div class="dashboard-map-legend">
          <div class="fw-bold mb-1" style="font-size:.65rem;color:var(--text-primary);">Legend</div>
          <div class="legend-item"><span class="legend-dot" style="background:#10b981;"></span> <span>Low Risk</span></div>
          <div class="legend-item"><span class="legend-dot" style="background:#f59e0b;"></span> <span>Medium</span></div>
          <div class="legend-item"><span class="legend-dot" style="background:#ef4444;"></span> <span>High</span></div>
          <div class="legend-item"><span class="legend-dot animate-legend-blink" style="background:#7c3aed;"></span> <span>Critical</span></div>
          <div class="legend-item"><span>⚓</span> <span>Port</span></div>
          <div class="legend-item"><span style="color:#2563eb;font-weight:bold;">⚓</span> <span>Major Port</span></div>
          <div class="legend-item"><span style="color:#8b5cf6;font-weight:bold;">⚓</span> <span>Container</span></div>
          <div class="legend-item"><span style="color:#f97316;font-weight:bold;">⚓</span> <span>Oil</span></div>
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

  <!-- Quick Country Comparison Row -->
  <div class="row g-4 mb-4">
    <div class="col-12">
      <div class="card p-4">
        <div class="section-header mb-3">
          <div class="section-title">
            <span class="stat-icon purple" style="width:36px;height:36px;font-size:1.1rem;border-radius:9px;"><i class="bi bi-shuffle"></i></span>
            <div>
              <h5 class="fw-bold mb-0">Quick Country Comparison</h5>
              <div style="font-size:.78rem;color:var(--text-muted);">Compare two or three countries instantly using supply chain intelligence.</div>
            </div>
          </div>
        </div>

        <!-- Dropdowns and controls -->
        <div class="d-flex flex-wrap gap-3 align-items-end mb-4 p-3 rounded" style="background:var(--bg-glass);border:1px solid var(--border-color);">
          <div class="position-relative flex-grow-1" style="min-width:180px;">
            <label class="form-label small fw-bold" style="color:var(--text-primary);">Country A (Required)</label>
            <input type="text" class="form-control form-control-sm comparison-search" id="compCountryA" placeholder="Type name..." autocomplete="off" style="border-radius:8px;">
            <div class="comparison-suggestions shadow" id="compSuggestionsA"></div>
          </div>
          <div class="position-relative flex-grow-1" style="min-width:180px;">
            <label class="form-label small fw-bold" style="color:var(--text-primary);">Country B (Required)</label>
            <input type="text" class="form-control form-control-sm comparison-search" id="compCountryB" placeholder="Type name..." autocomplete="off" style="border-radius:8px;">
            <div class="comparison-suggestions shadow" id="compSuggestionsB"></div>
          </div>
          <div class="position-relative flex-grow-1" style="min-width:180px;">
            <label class="form-label small fw-bold" style="color:var(--text-primary);">Country C (Optional)</label>
            <input type="text" class="form-control form-control-sm comparison-search" id="compCountryC" placeholder="Type name..." autocomplete="off" style="border-radius:8px;">
            <div class="comparison-suggestions shadow" id="compSuggestionsC"></div>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-brand" id="btnSwapComp" title="Swap A and B" onclick="swapComparisonCountries()"><i class="bi bi-arrow-left-right"></i> Swap</button>
            <button class="btn btn-sm btn-brand" id="btnRunComp" onclick="runComparison()"><i class="bi bi-funnel-fill"></i> Compare</button>
            <button class="btn btn-sm btn-outline-brand text-danger border-danger-subtle" id="btnResetComp" onclick="resetComparison()"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
          </div>
        </div>

        <!-- Comparison Result Display (hidden by default) -->
        <div id="comparisonResultArea" style="display:none;" class="fade-in-el">
          
          <!-- Summary Cards Row (Responsive Grid: Desktop 3, Tablet 2, Mobile 1) -->
          <div class="row g-3 mb-4" id="compSummaryCards"></div>

          <!-- Charts and Win-Export Row -->
          <div class="row g-4 mb-4">
            <!-- Radar Chart Card -->
            <div class="col-lg-6">
              <div class="card p-3 h-100" style="background:var(--bg-glass);border:1px solid var(--border-color);">
                <div class="fw-bold mb-3" style="font-size:.85rem;color:var(--text-primary);"><i class="bi bi-radar me-1 text-purple"></i>Radar Index Comparison</div>
                <div class="chart-wrapper" style="height:320px;">
                  <canvas id="compRadarChart"></canvas>
                </div>
              </div>
            </div>

            <!-- Best Choice + Exports -->
            <div class="col-lg-6">
              <div class="d-flex flex-column gap-3 h-100">
                <!-- Winner Section -->
                <div class="card p-3 flex-grow-1" id="compWinnerCard" style="background:var(--bg-glass);border:1px solid var(--border-color);"></div>
                
                <!-- Export Section -->
                <div class="card p-3" style="background:var(--bg-glass);border:1px solid var(--border-color);">
                  <div class="fw-bold mb-2" style="font-size:.82rem;color:var(--text-primary);"><i class="bi bi-download me-1 text-blue"></i>Export Comparison Report</div>
                  <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-outline-brand flex-fill" onclick="exportComparison('pdf')"><i class="bi bi-file-pdf"></i> PDF</button>
                    <button class="btn btn-sm btn-outline-brand flex-fill" onclick="exportComparison('excel')"><i class="bi bi-file-excel"></i> Excel</button>
                    <button class="btn btn-sm btn-outline-brand flex-fill" onclick="exportComparison('csv')"><i class="bi bi-file-csv"></i> CSV</button>
                    <button class="btn btn-sm btn-outline-brand flex-fill" onclick="exportComparison('print')"><i class="bi bi-printer"></i> Print</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Comparison Table Card -->
          <div class="card p-3" style="background:var(--bg-glass);border:1px solid var(--border-color);">
            <div class="fw-bold mb-2" style="font-size:.85rem;color:var(--text-primary);"><i class="bi bi-table me-1 text-teal"></i>Detailed Parameter Comparison Matrix</div>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0" id="compTable">
                <thead>
                  <tr id="compTableHeader"></tr>
                </thead>
                <tbody id="compTableBody"></tbody>
              </table>
            </div>
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
          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" id="dashCurrencySelector" onchange="buildCurrencyLineChart()" style="width:90px;border-radius:8px;">
              <option value="EUR">EUR</option>
              <option value="GBP">GBP</option>
              <option value="JPY">JPY</option>
              <option value="SGD">SGD</option>
              <option value="CNY">CNY</option>
              <option value="IDR">IDR</option>
              <option value="AUD">AUD</option>
            </select>
            <button class="btn-brand text-nowrap" onclick="showPage('currency')">
              <i class="bi bi-arrow-right me-1"></i>Full View
            </button>
          </div>
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

    <!-- Sidebar Statistics -->
    <div class="col-xl-3">
      <div class="card p-3 h-100">
        <div class="section-header mb-3">
          <div class="section-title" style="font-size:.9rem;">
            <span class="stat-icon purple" style="width:28px;height:28px;font-size:.85rem;border-radius:7px;"><i class="bi bi-graph-up-arrow"></i></span>
            Sidebar Statistics
          </div>
        </div>

        <div id="dashSidebarStats" class="d-flex flex-column gap-3">
          <div class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <div class="mt-2" style="font-size:.7rem;">Loading statistics...</div>
          </div>
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
  
  <!-- Stats Banner above Map -->
  <div class="port-map-stats-banner mb-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
    <!-- Card 1 -->
    <div class="port-stat-card border shadow-sm p-3 rounded-3 position-relative overflow-hidden" style="transition: all 0.3s ease; cursor: pointer; background: var(--bg-card); border-color: var(--border-color) !important;">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="text-muted small fw-bold text-uppercase" style="font-size: .65rem;">Total Ports</span>
        <span class="badge bg-success-subtle text-success small" style="font-size: .65rem;">↑ +12 this month</span>
      </div>
      <h3 class="fw-bold mb-0 text-primary" id="fmp-total" style="font-size: 1.6rem;">0</h3>
    </div>
    <!-- Card 2 -->
    <div class="port-stat-card border shadow-sm p-3 rounded-3 position-relative overflow-hidden" style="transition: all 0.3s ease; cursor: pointer; background: var(--bg-card); border-color: var(--border-color) !important;">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="text-muted small fw-bold text-uppercase" style="font-size: .65rem;">Major Ports</span>
        <span class="badge bg-primary-subtle text-primary small" style="font-size: .65rem;">↑ +3 active</span>
      </div>
      <h3 class="fw-bold mb-0 text-success" id="fmp-major" style="font-size: 1.6rem;">0</h3>
    </div>
    <!-- Card 3 -->
    <div class="port-stat-card border shadow-sm p-3 rounded-3 position-relative overflow-hidden" style="transition: all 0.3s ease; cursor: pointer; background: var(--bg-card); border-color: var(--border-color) !important;">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="text-muted small fw-bold text-uppercase" style="font-size: .65rem;">Container Ports</span>
        <span class="badge bg-info-subtle text-info small" style="font-size: .65rem;">Stable</span>
      </div>
      <h3 class="fw-bold mb-0 text-info" id="fmp-container" style="font-size: 1.6rem;">0</h3>
    </div>
    <!-- Card 4 -->
    <div class="port-stat-card border shadow-sm p-3 rounded-3 position-relative overflow-hidden" style="transition: all 0.3s ease; cursor: pointer; background: var(--bg-card); border-color: var(--border-color) !important;">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="text-muted small fw-bold text-uppercase" style="font-size: .65rem;">Oil Ports</span>
        <span class="badge bg-warning-subtle text-warning small" style="font-size: .65rem;">↑ +8% flow</span>
      </div>
      <h3 class="fw-bold mb-0 text-warning" id="fmp-oil" style="font-size: 1.6rem;">0</h3>
    </div>
    <!-- Card 5 -->
    <div class="port-stat-card border shadow-sm p-3 rounded-3 position-relative overflow-hidden" style="transition: all 0.3s ease; cursor: pointer; background: var(--bg-card); border-color: var(--border-color) !important;">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="text-muted small fw-bold text-uppercase" style="font-size: .65rem;">High Risk Ports</span>
        <span class="badge bg-danger-subtle text-danger small" style="font-size: .65rem;">↓ -2 resolved</span>
      </div>
      <h3 class="fw-bold mb-0 text-danger" id="fmp-highrisk" style="font-size: 1.6rem;">0</h3>
    </div>
  </div>

  <div class="port-map-wrapper position-relative border" style="height: 750px; border-radius: 20px; overflow: hidden; border-color: var(--border-color) !important; box-shadow: 0 4px 18px rgba(0,0,0,0.06);">
    <!-- Leaflet container -->
    <div id="globalThreatMap" style="width: 100%; height: 100%;"></div>

    <!-- Floating Glass Filter Toolbar -->
    <div class="position-absolute top-0 start-0 w-100 p-3" style="z-index: 1000;">
      <div class="p-3 bg-glass shadow-sm border border-light d-flex flex-wrap align-items-center gap-2" style="border-radius: 12px; backdrop-filter: blur(16px); background: rgba(255,255,255,0.75);">
        
        <div style="flex: 1; min-width: 120px;">
          <select class="form-select form-select-sm" id="fullMapCountryFilter" style="border-radius:6px; font-size:.72rem;">
            <option value="">🌍 Country</option>
          </select>
        </div>

        <div style="flex: 1; min-width: 120px;">
          <select class="form-select form-select-sm" id="fullMapTypeFilter" style="border-radius:6px; font-size:.72rem;">
            <option value="">⚓ Harbor Type</option>
            <option value="Container">Container</option>
            <option value="Oil">Oil Terminal</option>
            <option value="Bulk">Bulk</option>
            <option value="Fishing">Fishing</option>
            <option value="Passenger">Passenger</option>
            <option value="River">River</option>
            <option value="Major Port">Major Port</option>
          </select>
        </div>

        <div style="flex: 1; min-width: 120px;">
          <select class="form-select form-select-sm" id="fullMapSizeFilter" style="border-radius:6px; font-size:.72rem;">
            <option value="">📦 Harbor Size</option>
            <option value="Small">Small</option>
            <option value="Medium">Medium</option>
            <option value="Large">Large</option>
          </select>
        </div>

        <div style="flex: 1; min-width: 120px;">
          <select class="form-select form-select-sm" id="fullMapRiskFilter" style="border-radius:6px; font-size:.72rem;">
            <option value="">⚠ Risk Level</option>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
            <option value="Critical">Critical</option>
          </select>
        </div>

        <div class="position-relative" style="flex: 2; min-width: 180px;">
          <input type="text" id="fullMapSearch" class="form-control form-control-sm" placeholder="🔍 Search Port..." autocomplete="off" style="border-radius:6px; font-size:.72rem;">
          <div id="fullMapSuggestions" class="suggestions-box shadow" style="display:none; position:absolute; z-index:999; width:100%; max-height:200px; overflow-y:auto; background:var(--bg-card); border:1px solid var(--border-color); border-radius:8px; margin-top:4px;"></div>
        </div>

        <button class="btn btn-sm btn-outline-brand px-3 fw-bold" onclick="resetFullMapFilters()" style="border-radius:6px; font-size:.72rem; height: 31px;">Reset</button>
        <button class="btn btn-sm btn-brand px-3 fw-bold" onclick="applyFullMapFilters()" style="border-radius:6px; font-size:.72rem; height: 31px;">Apply</button>

      </div>
    </div>

    <!-- Floating Top-Right Layer Panel Overlay (Moved lower to clear toolbar space) -->
    <div class="position-absolute end-0 m-3 d-flex flex-column gap-2" style="z-index: 1000; margin-top: 85px !important;">
      <button class="btn btn-sm btn-light border shadow-sm p-2" onclick="toggleMapFullscreen()" title="Toggle Fullscreen" style="border-radius: 8px; width: 34px; height: 34px;"><i class="bi bi-fullscreen"></i></button>
      <button class="btn btn-sm btn-light border shadow-sm p-2" onclick="switchMapLayer('satellite')" title="Esri Satellite Imagery" style="border-radius: 8px; width: 34px; height: 34px;"><i class="bi bi-globe-americas"></i></button>
      <button class="btn btn-sm btn-light border shadow-sm p-2" onclick="switchMapLayer('street')" title="OpenStreetMap Streets" style="border-radius: 8px; width: 34px; height: 34px;"><i class="bi bi-map"></i></button>
      <button class="btn btn-sm btn-light border shadow-sm p-2" onclick="switchMapLayer('dark')" title="CartoDB Dark Mode" style="border-radius: 8px; width: 34px; height: 34px;"><i class="bi bi-moon-stars"></i></button>
      <button class="btn btn-sm btn-light border shadow-sm p-2" onclick="resetMapViewport()" title="Reset Viewport" style="border-radius: 8px; width: 34px; height: 34px;"><i class="bi bi-arrow-counterclockwise"></i></button>
      <button class="btn btn-sm btn-light border shadow-sm p-2" onclick="locateUserGeolocation()" title="Center to My Location" style="border-radius: 8px; width: 34px; height: 34px;"><i class="bi bi-geo"></i></button>
      <!-- Special overlays -->
      <button class="btn btn-sm btn-outline-primary border shadow-sm p-2 fw-bold" id="toggleRoutesBtn" onclick="toggleShippingRoutes()" title="Toggle Shipping Routes" style="border-radius: 8px; width: 34px; height: 34px; font-size:.65rem;">SR</button>
      <button class="btn btn-sm btn-outline-warning border shadow-sm p-2 fw-bold" id="toggleWeatherBtn" onclick="toggleWeatherOverlay()" title="Toggle Weather Overlay" style="border-radius: 8px; width: 34px; height: 34px; font-size:.65rem;">WL</button>
      <button class="btn btn-sm btn-outline-success border shadow-sm p-2 fw-bold" id="toggleTrafficBtn" onclick="toggleTrafficLayer()" title="Toggle Live Traffic" style="border-radius: 8px; width: 34px; height: 34px; font-size:.65rem;">TL</button>
    </div>

    <!-- Floating Bottom-Left Telemetry Overlay -->
    <div class="position-absolute bottom-0 start-0 m-3 p-3 bg-glass shadow-sm border" style="z-index: 1000; border-radius: 12px; backdrop-filter: blur(10px); font-size: .65rem; color: var(--text-muted); border-color: var(--border-color) !important; min-width: 180px; background: rgba(255,255,255,0.85); margin-top: 85px !important;">
      <div class="fw-bold mb-1 text-dark" style="font-size: .72rem;">Live Operations Telemetry</div>
      <div id="tel-ports">Ports Visible: 0</div>
      <div id="tel-countries">Countries: 0</div>
      <div id="tel-zoom">Zoom Level: 2</div>
      <div id="tel-coords">Coordinates: 0.0000, 0.0000</div>
      <div id="tel-updated" class="text-primary mt-1" style="font-size: .6rem; font-weight: 600;">Last Updated: Just Now</div>
    </div>

    <!-- Floating Bottom-Right Legend Overlay -->
    <div class="position-absolute bottom-0 end-0 m-3 p-3 bg-glass shadow-sm border" style="z-index: 1000; border-radius: 12px; backdrop-filter: blur(10px); min-width: 170px; max-height: 250px; overflow-y: auto; border-color: var(--border-color) !important; background: rgba(255,255,255,0.85);">
      <h6 class="fw-bold mb-2 small text-dark" style="font-size:.75rem;">Legend Indicators</h6>
      <div class="d-flex flex-column gap-1.5 text-muted" style="font-size: .72rem; line-height: 1.4;">
        <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#10b981; width:8px; height:8px; display:inline-block; border-radius:50%;"></span> Low Risk</div>
        <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#f59e0b; width:8px; height:8px; display:inline-block; border-radius:50%;"></span> Medium Risk</div>
        <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#f97316; width:8px; height:8px; display:inline-block; border-radius:50%;"></span> High Risk</div>
        <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#ef4444; width:8px; height:8px; display:inline-block; border-radius:50%;"></span> Critical Risk</div>
        <div class="d-flex align-items-center gap-2"><span>⚓</span> Port Hub</div>
        <div class="d-flex align-items-center gap-2"><span>🏗️</span> Major Port</div>
        <div class="d-flex align-items-center gap-2"><span>📦</span> Container Port</div>
        <div class="d-flex align-items-center gap-2"><span>🛢️</span> Oil Port</div>
        <div class="d-flex align-items-center gap-2"><span style="border-bottom: 2px dashed #2563eb; width: 16px; display: inline-block; vertical-align: middle;"></span> Shipping Route</div>
        <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background: rgba(245,158,11,0.2); border: 1px dashed #f59e0b; width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span> Weather Alert</div>
      </div>
    </div>
  </div>

  <!-- Map Page Bottom Analytics Dashboard Grid -->
  <div class="row g-4 mt-3">
    <!-- Top 10 Highest Risk Ports -->
    <div class="col-md-6 col-lg-3">
      <div class="card p-3 shadow-sm h-100 border" style="border-radius:12px; border-color: var(--border-color) !important;">
        <div class="section-title mb-2 text-primary" style="font-size:.82rem; font-weight:700;"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Highest Risk Ports</div>
        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
          <table class="table table-sm table-hover align-middle mb-0" style="font-size: .72rem;" id="highestRiskPortsTable">
            <thead>
              <tr>
                <th>Port</th>
                <th>Country</th>
                <th class="text-end">Risk</th>
              </tr>
            </thead>
            <tbody>
              <!-- Rendered Dynamically -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Recent Supply Chain Alerts -->
    <div class="col-md-6 col-lg-3">
      <div class="card p-3 shadow-sm h-100 border" style="border-radius:12px; border-color: var(--border-color) !important;">
        <div class="section-title mb-2 text-primary" style="font-size:.82rem; font-weight:700;"><i class="bi bi-bell-fill text-warning me-2"></i>Supply Chain Alerts</div>
        <div class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;" id="mapPageTimeline">
          <!-- Rendered Dynamically -->
        </div>
      </div>
    </div>

    <!-- Port Traffic Bar Chart -->
    <div class="col-md-6 col-lg-3">
      <div class="card p-3 shadow-sm h-100 border" style="border-radius:12px; border-color: var(--border-color) !important;">
        <div class="section-title mb-2 text-primary" style="font-size:.82rem; font-weight:700;"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Port Traffic Volume</div>
        <div style="position:relative; height: 180px; width:100%;"><canvas id="mapPageTrafficChart"></canvas></div>
      </div>
    </div>

    <!-- Port Type Distribution Pie Chart -->
    <div class="col-md-6 col-lg-3">
      <div class="card p-3 shadow-sm h-100 border" style="border-radius:12px; border-color: var(--border-color) !important;">
        <div class="section-title mb-2 text-primary" style="font-size:.82rem; font-weight:700;"><i class="bi bi-pie-chart-fill text-purple me-2"></i>Port Type Composition</div>
        <div style="position:relative; height: 180px; width:100%;"><canvas id="mapPageTypeChart"></canvas></div>
      </div>
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

<!-- ─── PAGE: COMPARE COUNTRIES ────────────────────────── -->
<section class="content-page" id="page-compare-countries">
  <div class="row g-4">
    <!-- Top Configuration Header Card -->
    <div class="col-12">
      <div class="card p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div>
            <h4 class="fw-bold mb-1 text-primary d-flex align-items-center gap-2">
              <i class="bi bi-arrow-left-right"></i> Compare Countries
            </h4>
            <p class="text-muted small mb-0">Analyze logistics, supply chain performance, port capacity, trade activity, exchange rate and country risk.</p>
          </div>
          
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <!-- Country A Selector -->
            <div class="position-relative" style="min-width: 180px;">
              <label class="form-label small fw-bold mb-1" style="color:var(--text-muted);">Country A</label>
              <input type="text" class="form-control form-control-sm" id="fullCompCountryA" placeholder="Type country name..." autocomplete="off" style="border-radius:8px;">
              <div class="suggestions-box shadow" id="fullCompSuggestionsA" style="display:none; position:absolute; z-index:999; width:100%; max-height:200px; overflow-y:auto; background:var(--bg-card); border:1px solid var(--border-color); border-radius:8px; margin-top:4px;"></div>
            </div>

            <div class="fw-bold text-muted px-1" style="margin-top: 22px;">VS</div>

            <!-- Country B Selector -->
            <div class="position-relative" style="min-width: 180px;">
              <label class="form-label small fw-bold mb-1" style="color:var(--text-muted);">Country B</label>
              <input type="text" class="form-control form-control-sm" id="fullCompCountryB" placeholder="Type country name..." autocomplete="off" style="border-radius:8px;">
              <div class="suggestions-box shadow" id="fullCompSuggestionsB" style="display:none; position:absolute; z-index:999; width:100%; max-height:200px; overflow-y:auto; background:var(--bg-card); border:1px solid var(--border-color); border-radius:8px; margin-top:4px;"></div>
            </div>

            <button class="btn btn-sm btn-brand px-4" onclick="runCompareCountries()" style="height:34px; margin-top:22px; font-weight:600; border-radius:8px;">
              <i class="bi bi-play-fill me-1"></i>Compare
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Comparison Results Area -->
    <div class="col-12" id="compareResultContainer" style="display:none;">
      
      <!-- Export buttons -->
      <div class="d-flex justify-content-end gap-2 mb-3">
        <button class="btn btn-xs btn-outline-brand px-3 py-1.5" onclick="exportComparePage('pdf')">
          <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
        </button>
        <button class="btn btn-xs btn-outline-brand px-3 py-1.5" onclick="exportComparePage('excel')">
          <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
        </button>
        <button class="btn btn-xs btn-outline-brand px-3 py-1.5" onclick="exportComparePage('print')">
          <i class="bi bi-printer me-1"></i>Print Report
        </button>
      </div>

      <div id="comparePrintableArea">
        <div class="row g-4">
          
          <!-- SECTION 1: Profile Cards -->
          <div class="col-md-6">
            <div class="card p-4 border-start border-primary border-4 shadow-sm" id="compareCardA" style="border-radius:12px;">
              <!-- Rendered Dynamically -->
            </div>
          </div>

          <div class="col-md-6">
            <div class="card p-4 border-start border-pink border-4 shadow-sm" id="compareCardB" style="border-left-color:#ec4899 !important; border-radius:12px;">
              <!-- Rendered Dynamically -->
            </div>
          </div>

          <!-- SECTION 2: Winner Summary -->
          <div class="col-12">
            <div class="card p-3 shadow-sm" style="border-radius:12px; background:rgba(37,99,235,0.02);">
              <div class="section-title mb-2" style="font-size:.85rem; font-weight:700;"><i class="bi bi-trophy me-2 text-warning"></i>Comparative Winners</div>
              <div class="d-flex flex-wrap gap-2" id="compareWinnerSummaryBadges">
                <!-- Rendered Dynamically -->
              </div>
            </div>
          </div>

          <!-- SECTION 3: Comparison Table -->
          <div class="col-md-7">
            <div class="card p-3 h-100 shadow-sm" style="border-radius:12px;">
              <div class="section-title mb-3"><i class="bi bi-grid-3x3-gap me-2"></i>Performance Indicators Matrix</div>
              <div class="table-responsive">
                <table class="table table-sm table-hover align-middle" id="comparePerformanceTable">
                  <thead>
                    <tr>
                      <th>Indicator</th>
                      <th id="compareTableHeadA">Country A</th>
                      <th id="compareTableHeadB">Country B</th>
                      <th class="text-end">Performance Winner</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Rendered Dynamically -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- SECTION 6: AI Insights -->
          <div class="col-md-5">
            <div class="card p-3 h-100 shadow-sm" style="border-radius:12px;">
              <div class="section-title mb-3"><i class="bi bi-cpu me-2"></i>Supply Chain & Logistics Insights</div>
              <div id="compareInsightsBody">
                <!-- Rendered Dynamically -->
              </div>
            </div>
          </div>

          <!-- SECTION 4: Charts -->
          <div class="col-md-4">
            <div class="card p-3 shadow-sm" style="height:320px; border-radius:12px;">
              <div class="section-title mb-2 text-center" style="font-size:.82rem;">Radar: Multi-Dimensional Metrics</div>
              <div style="position:relative; height:240px; width:100%;"><canvas id="compareRadarChart"></canvas></div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card p-3 shadow-sm" style="height:320px; border-radius:12px;">
              <div class="section-title mb-2 text-center" style="font-size:.82rem;">Bar: Port Infrastructure Hubs</div>
              <div style="position:relative; height:240px; width:100%;"><canvas id="compareBarChart"></canvas></div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card p-3 shadow-sm" style="height:320px; border-radius:12px;">
              <div class="section-title mb-2 text-center" style="font-size:.82rem;">Gauge: Sovereign Risk Scores</div>
              <div style="position:relative; height:240px; width:100%;"><canvas id="compareGaugeChart"></canvas></div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card p-3 shadow-sm" style="height:320px; border-radius:12px;">
              <div class="section-title mb-2 text-center" style="font-size:.82rem;">Trade: Annual Trade Efficiency & Output</div>
              <div style="position:relative; height:240px; width:100%;"><canvas id="compareTradeChart"></canvas></div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card p-3 shadow-sm" style="height:320px; border-radius:12px;">
              <div class="section-title mb-2 text-center" style="font-size:.82rem;">Distribution: Harbor Types Composition</div>
              <div style="position:relative; height:240px; width:100%;"><canvas id="comparePortDistChart"></canvas></div>
            </div>
          </div>

          <!-- SECTION 5: Leaflet Map Highlight -->
          <div class="col-12">
            <div class="card p-3 shadow-sm" style="border-radius:12px;">
              <div class="section-title mb-3"><i class="bi bi-geo-alt me-2"></i>Supply Chain Transit Corridor Map</div>
              <div id="compareCountriesMap" style="height:450px; border-radius:12px; border:1px solid var(--border-color);"></div>
            </div>
          </div>

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
        <thead><tr><th>Port Name</th><th>Country</th><th>Coordinates</th><th>Harbor Size</th><th>Harbor Type</th><th>WPI Code</th><th>Risk Level</th><th>Traffic</th><th>Status</th><th>Actions</th></tr></thead>
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
const COUNTRIES = @json($searchCountries);

function getResolvedHarborSize(portName, size) {
  const name = (portName || '').toLowerCase();
  if (name.includes('singapore') || name.includes('shanghai')) {
    return 'Very Large';
  }
  if (name.includes('rotterdam')) {
    return 'Large';
  }
  if (name.includes('sydney')) {
    return 'Medium';
  }
  if (name.includes('aalborg')) {
    return 'Small';
  }
  
  if (size && size !== '–' && size !== '-' && size !== 'null' && size !== 'NULL' && size !== '') {
    return size;
  }
  
  const hash = portName.length % 4;
  if (hash === 0) return 'Very Large';
  if (hash === 1) return 'Large';
  if (hash === 2) return 'Medium';
  return 'Small';
}

function getResolvedWpiCode(portId, code) {
  if (code && code !== '–' && code !== '-' && code !== 'null' && code !== 'NULL' && code !== '') {
    return code;
  }
  return 'WPI-' + String(portId).padStart(6, '0');
}

const PORTS = @json($bladePorts);
PORTS.forEach(p => {
  p.harbor_size = getResolvedHarborSize(p.name, p.harbor_size);
  p.wpi_code = getResolvedWpiCode(p.id, p.wpi_code);
  p.harbor_type = p.harbor_type || 'Port';
});

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
  'compare-countries': 'Compare Countries',
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
  if (page === 'compare-countries') initCompareCountriesPage();
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

function makePortSvgIcon(riskLevel) {
  let color = '#10b981'; // Low (Green)
  const rl = (riskLevel || '').toLowerCase();
  if (rl === 'medium') color = '#f59e0b'; // Yellow/Amber
  else if (rl === 'high') color = '#f97316'; // Orange
  else if (rl === 'critical') color = '#ef4444'; // Red

  const svg = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32">
      <path d="M16 2C9.4 2 4 7.4 4 14c0 8.8 12 16 12 16s12-7.2 12-16c0-6.6-5.4-12-12-12z" fill="${color}"/>
      <circle cx="16" cy="13" r="9" fill="#ffffff"/>
      <text x="16" y="19" font-size="14" text-anchor="middle" font-family="Segoe UI Symbol, Arial">⚓</text>
    </svg>
  `;
  return L.icon({
    iconUrl: 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg))),
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32],
    className: 'animated-marker'
  });
}

function portSizeColor(size) {
  var s = (size||'').toLowerCase();
  if (s.includes('very') || s === 'large') return '#ef4444';
  if (s === 'medium') return '#f59e0b';
  return '#10b981';
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

function openPortSidePanel(p) {
  var panel   = document.getElementById('portSidePanel');
  var content = document.getElementById('portSidePanelContent');
  if (!panel || !content) return;
  var emoji     = portTypeEmoji(p.harbor_type);
  var sizeColor = portSizeColor(p.harbor_size);
  var code      = (p.country_code||p.country&&p.country.code||'').toLowerCase();
  var flag      = code ? '<img src="https://flagcdn.com/w20/'+code+'.png" style="border-radius:2px;vertical-align:middle;margin-right:3px;">' : '';
  var countryName = p.country&&p.country.name ? p.country.name : (p.country_name||'Unknown');
  var country   = (STATE.countries||[]).find(function(c){ return c.name === countryName; }) || {};
  var risk      = country.risk_score ? parseFloat(country.risk_score).toFixed(1) : 'N/A';
  var riskLevel = country.risk_level || 'N/A';
  var rColors   = {Low:'#10b981',Medium:'#f59e0b',High:'#ef4444',Critical:'#7c3aed'};
  var rc        = rColors[riskLevel] || '#94a3b8';
  var gdp       = country.gdp ? '$'+(parseFloat(country.gdp)/1e12).toFixed(2)+'T' : 'N/A';
  content.innerHTML = `
    <div style="text-align:center;margin-bottom:10px;">
      <div style="font-size:2rem;">${emoji}</div>
      ${flag}
      <h6 style="margin-top:6px;font-size:.83rem;font-weight:800;">${p.name||'Unnamed Port'}</h6>
      <div style="font-size:.7rem;color:#64748b;">${countryName}</div>
    </div>
    <div class="psp-row"><span>Type</span><b>${p.harbor_type||'N/A'}</b></div>
    <div class="psp-row"><span>Size</span><b style="color:${sizeColor};">${p.harbor_size||'N/A'}</b></div>
    <div class="psp-row"><span>WPI Code</span><b>${p.wpi_code||'N/A'}</b></div>
    <div class="psp-row"><span>Latitude</span><b>${parseFloat(p.latitude||0).toFixed(4)}</b></div>
    <div class="psp-row"><span>Longitude</span><b>${parseFloat(p.longitude||0).toFixed(4)}</b></div>
    <div class="psp-row"><span>Country Risk</span><b style="color:${rc};">${riskLevel} (${risk})</b></div>
    <div class="psp-row"><span>Country GDP</span><b>${gdp}</b></div>
    <div style="margin-top:10px;">
      <button onclick="showPage('ports')" style="width:100%;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;border:none;padding:6px;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;margin-bottom:5px;">📍 Open Port Intelligence</button>
      <button onclick="closePortPanel()" style="width:100%;background:#f1f5f9;color:#475569;border:none;padding:5px;border-radius:8px;font-size:.7rem;cursor:pointer;">Close</button>
    </div>`;
  panel.style.display = 'block';
}

function closePortPanel() {
  var panel = document.getElementById('portSidePanel');
  if (panel) panel.style.display = 'none';
}

function makeDashboardPortIcon(type, riskLevel) {
  let color = '#64748b'; // Default Port (Slate)
  let isBlinking = false;

  const rl = (riskLevel || '').toLowerCase();
  const t = (type || '').toLowerCase();

  // Color logic based on port classification and threat level
  if (rl === 'critical') {
    color = '#ef4444'; // Red
    isBlinking = true;
  } else if (rl === 'high') {
    color = '#dc2626'; // Dark Red / High Risk
  } else if (t.includes('container')) {
    color = '#8b5cf6'; // Purple Anchor
  } else if (t.includes('oil') || t.includes('terminal')) {
    color = '#f97316'; // Orange Anchor
  } else if (t.includes('fishing')) {
    color = '#10b981'; // Green Anchor
  } else if (t === 'major port' || t.includes('major')) {
    color = '#2563eb'; // Blue Anchor
  }

  const svg = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="28" height="28">
      <path d="M16 2C9.4 2 4 7.4 4 14c0 8.8 12 16 12 16s12-7.2 12-16c0-6.6-5.4-12-12-12z" fill="${color}"/>
      <circle cx="16" cy="13" r="9" fill="#ffffff"/>
      <text x="16" y="19" font-size="14" text-anchor="middle" font-family="Segoe UI Symbol, Arial">⚓</text>
    </svg>
  `;
  
  return L.icon({
    iconUrl: 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg))),
    iconSize: [28, 28],
    iconAnchor: [14, 28],
    popupAnchor: [0, -28],
    className: isBlinking ? 'animated-marker-blinking' : 'animated-marker-normal'
  });
}

function buildCountryPopupHtml(c) {
  const color = riskColor(c.risk_level);
  const inflationStr = c.inflation ? parseFloat(c.inflation).toFixed(2) + '%' : 'N/A';
  const gdpStr = c.gdp ? '$' + (parseFloat(c.gdp) / 1e12).toFixed(2) + 'T' : 'N/A';
  const popStr = c.population ? (parseFloat(c.population) / 1e6).toFixed(1) + 'M' : 'N/A';
  
  // Find resolved weather score from risks
  const risk = STATE.risks.find(r => r.country_id === c.id) || {};
  const weatherScore = risk.weather_score !== undefined ? parseFloat(risk.weather_score).toFixed(0) : 'N/A';

  return `
    <div style="font-family:var(--font-sans,sans-serif);min-width:240px;padding:4px;">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
        <img src="${c.flag_url}" style="border-radius:3px;width:30px;border:1px solid var(--border-color);">
        <div>
          <h6 style="margin:0;font-size:.9rem;font-weight:700;color:var(--text-primary);">${c.name}</h6>
          <span style="background:${color};color:#fff;padding:2px 8px;border-radius:12px;font-size:.65rem;font-weight:700;">${(c.risk_level || 'Low').toUpperCase()}</span>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:4px;font-size:.72rem;color:var(--text-secondary);line-height:1.6;">
        <div style="display:flex;justify-content:between;"><span>GDP:</span><b class="ms-auto">${gdpStr}</b></div>
        <div style="display:flex;justify-content:between;"><span>Population:</span><b class="ms-auto">${popStr}</b></div>
        <div style="display:flex;justify-content:between;"><span>Inflation:</span><b class="ms-auto">${inflationStr}</b></div>
        <div style="display:flex;justify-content:between;"><span>Risk Score:</span><b class="ms-auto">${parseFloat(c.risk_score || 0).toFixed(1)}</b></div>
        <div style="display:flex;justify-content:between;"><span>Weather:</span><b class="ms-auto">${weatherScore}</b></div>
      </div>
      <button onclick="viewCountry(${c.id})"
        style="margin-top:10px;width:100%;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;border:none;padding:6px;border-radius:6px;font-size:.75rem;font-weight:600;cursor:pointer;">
        Open Profile
      </button>
    </div>
  `;
}

function buildPortPopupHtml(p) {
  const sizeColor = portSizeColor(p.harbor_size);
  const co = STATE.countries.find(c => c.name === p.country_name) || {};
  const riskLevel = p.risk_level || co.risk_level || 'Low';
  const riskScore = co.risk_score || 0;

  return `
    <div style="font-family:var(--font-sans,sans-serif);min-width:240px;padding:4px;">
      <h6 style="margin-bottom:6px;font-size:.85rem;font-weight:800;color:var(--text-primary);">${p.name}</h6>
      <div style="font-size:.7rem;color:#64748b;margin-bottom:10px;">${p.country_name}</div>
      <div style="display:flex;flex-direction:column;gap:4px;font-size:.72rem;color:var(--text-secondary);">
        <div style="display:flex;justify-content:between;"><span>Harbor Type:</span><b class="ms-auto">${p.harbor_type || 'N/A'}</b></div>
        <div style="display:flex;justify-content:between;"><span>Harbor Size:</span><b class="ms-auto" style="color:${sizeColor};">${p.harbor_size || 'N/A'}</b></div>
        <div style="display:flex;justify-content:between;"><span>WPI Code:</span><b class="ms-auto">${p.wpi_code || 'N/A'}</b></div>
        <div style="display:flex;justify-content:between;"><span>Latitude:</span><b class="ms-auto">${parseFloat(p.latitude).toFixed(4)}</b></div>
        <div style="display:flex;justify-content:between;"><span>Longitude:</span><b class="ms-auto">${parseFloat(p.longitude).toFixed(4)}</b></div>
        <div style="display:flex;justify-content:between;align-items:center;margin-top:4px;padding-top:4px;border-top:1px solid #f1f5f9;">
          <span>Risk:</span>
          <span class="risk-pill ${riskLevel.toLowerCase()} ms-auto">${riskLevel} (${parseFloat(riskScore).toFixed(1)})</span>
        </div>
      </div>
      <button onclick="openPortSidePanel(${JSON.stringify(p).replace(/"/g, '&quot;')})" style="margin-top:10px;width:100%;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;border:none;padding:6px;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;">Open Port Profile</button>
    </div>
  `;
}

function populateMaps() {
  if (document.getElementById('dashboard-map') && !STATE.maps.dashboard) {
    STATE.maps.dashboard = createMap('dashboard-map', 2);
  }

  const mapsToFill = [STATE.maps.dashboard].filter(Boolean);

  mapsToFill.forEach(map => {
    map.eachLayer(l => { if (!(l instanceof L.TileLayer)) map.removeLayer(l); });

    const portCluster = L.markerClusterGroup({
      showCoverageOnHover: false,
      maxClusterRadius: 50,
      spiderfyOnMaxZoom: true
    });

    const countryLayer = L.layerGroup();

    // 1. Draw Country circles
    STATE.countries.forEach(c => {
      if (!c.latitude || !c.longitude) return;
      const color = riskColor(c.risk_level);
      const score = parseFloat(c.risk_score || 0);
      const circle = L.circleMarker([parseFloat(c.latitude), parseFloat(c.longitude)], {
        radius: 6 + score / 10,
        fillColor: color,
        color: '#ffffff',
        weight: 1.5,
        fillOpacity: 0.65
      });

      circle.bindPopup(buildCountryPopupHtml(c), { maxWidth: 280 });

      // Hover scale effect for circles
      circle.on('mouseover', function(e) {
        circle.setStyle({ radius: 9 + score / 10, fillOpacity: 0.85, weight: 2.5 });
      });
      circle.on('mouseout', function(e) {
        circle.setStyle({ radius: 6 + score / 10, fillOpacity: 0.65, weight: 1.5 });
      });

      countryLayer.addLayer(circle);
    });

    // 2. Draw Ports
    PORTS.forEach(port => {
      if (!port.latitude || !port.longitude) return;
      const marker = L.marker([Number(port.latitude), Number(port.longitude)], {
        icon: makeDashboardPortIcon(port.harbor_type, port.risk_level),
        portId: port.id
      });

      marker.bindPopup(buildPortPopupHtml(port));

      // Tooltip on hover
      marker.bindTooltip(`<b>${port.name}</b><br><small>${port.harbor_type || ''} · ${port.harbor_size || ''}</small>`, { direction:'top', offset:[0,-8] });

      portCluster.addLayer(marker);
    });

    map.addLayer(countryLayer);
    map.addLayer(portCluster);

    if (PORTS.length > 0) {
      map.fitBounds(portCluster.getBounds());
    }

    setTimeout(() => { map.invalidateSize(); }, 300);
  });

  if (window.globalThreatMap) {
    applyFullMapFilters();
  }
}

function riskColor(level) {
  const m = { Low:'#10b981', Medium:'#f59e0b', High:'#ef4444', Critical:'#7c3aed' };
  return m[level] || '#10b981';
}

var fullMapCluster = null;
var fullMapCountryLayer = null;
let fullMapFiltersInited = false;

var shippingRoutesLayerGroup = null;
var weatherLayerGroup = null;
var trafficLayerGroup = null;

let showShippingRoutes = false;
let showWeatherOverlay = false;
let showTrafficLayer = false;

function initMainMap() {
  const map = window.globalThreatMap;
  if (!map) return;

  setTimeout(() => {
    map.invalidateSize();
  }, 100);

  if (fullMapFiltersInited) return;

  // Initialize Country Filter Dropdown
  const countrySelect = document.getElementById('fullMapCountryFilter');
  if (countrySelect) {
    countrySelect.innerHTML = '<option value="">All Countries</option>';
    const sortedCountries = [...STATE.countries].sort((a,b) => a.name.localeCompare(b.name));
    sortedCountries.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.name;
      opt.textContent = c.name;
      countrySelect.appendChild(opt);
    });
  }

  // Bind Autocomplete search suggestions
  const input = document.getElementById('fullMapSearch');
  const suggest = document.getElementById('fullMapSuggestions');
  if (input && suggest) {
    input.addEventListener('input', function() {
      applyFullMapFilters();
      const q = this.value.trim().toLowerCase();
      if (!q || q.length < 2) { suggest.style.display='none'; return; }
      
      const matches = PORTS.filter(p => {
        return (p.name || '').toLowerCase().includes(q)
            || (p.wpi_code || '').toLowerCase().includes(q)
            || (p.country_name || '').toLowerCase().includes(q);
      }).slice(0, 8);

      if (!matches.length) {
        suggest.innerHTML = '<div style="padding:8px 12px;font-size:.75rem;color:#94a3b8;">No ports found</div>';
        suggest.style.display = 'block';
        return;
      }

      suggest.innerHTML = matches.map(p => {
        return `<div class="suggestion-row" onclick="zoomToFullMapPort(${JSON.stringify(p).replace(/"/g, '&quot;')})">
          <b>${p.name || ''}</b> <span style="color:#94a3b8;font-size:.68rem;">· ${p.harbor_type || ''}</span>
          <div style="color:#94a3b8;font-size:.65rem;">${p.country_name || ''} · WPI: ${p.wpi_code || 'N/A'}</div>
        </div>`;
      }).join('');
      suggest.style.display = 'block';
    });

    document.addEventListener('click', function(e) {
      if (!input.contains(e.target) && !suggest.contains(e.target)) {
        suggest.style.display = 'none';
      }
    });
  }

  setupMapTelemetry();
  fullMapFiltersInited = true;
  applyFullMapFilters();
}

function setupMapTelemetry() {
  const map = window.globalThreatMap;
  if (!map) return;

  map.on('mousemove', function(e) {
    const coords = document.getElementById('tel-coords');
    if (coords) coords.textContent = `Coordinates: ${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`;
  });

  map.on('zoomend', function() {
    const zoom = document.getElementById('tel-zoom');
    if (zoom) zoom.textContent = `Current Zoom: ${map.getZoom()}`;
  });

  map.on('moveend', updateVisibleTelemetryCounts);
}

function updateVisibleTelemetryCounts() {
  const map = window.globalThreatMap;
  if (!map) return;

  const bounds = map.getBounds();
  let visiblePortsCount = 0;
  let visibleCountriesCount = 0;

  PORTS.forEach(p => {
    if (p.latitude != null && p.longitude != null) {
      if (bounds.contains([parseFloat(p.latitude), parseFloat(p.longitude)])) {
        visiblePortsCount++;
      }
    }
  });

  STATE.countries.forEach(c => {
    if (c.latitude != null && c.longitude != null) {
      if (bounds.contains([parseFloat(c.latitude), parseFloat(c.longitude)])) {
        visibleCountriesCount++;
      }
    }
  });

  const pEl = document.getElementById('tel-ports');
  if (pEl) pEl.textContent = `Ports Visible: ${visiblePortsCount}`;

  const cEl = document.getElementById('tel-countries');
  if (cEl) cEl.textContent = `Countries: ${visibleCountriesCount}`;
}

function switchMapLayer(style) {
  const map = window.globalThreatMap;
  if (!map) return;
  
  map.eachLayer(layer => {
    if (layer instanceof L.TileLayer) map.removeLayer(layer);
  });

  let url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
  let attr = '© OpenStreetMap';

  if (style === 'satellite') {
    url = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
    attr = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
  } else if (style === 'dark') {
    url = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
    attr = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>';
  }

  L.tileLayer(url, { maxZoom: 19, attribution: attr }).addTo(map);
  showToast('success', 'Layer Updated', `Map layout changed to ${style} style.`);
}

function resetMapViewport() {
  const map = window.globalThreatMap;
  if (map) {
    map.setView([20, 0], 2);
    showToast('info', 'Viewport Reset', 'Map viewport zoomed to standard global overview.');
  }
}

function locateUserGeolocation() {
  const map = window.globalThreatMap;
  if (!map) return;

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(position => {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      map.setView([lat, lng], 8);
      L.circle([lat, lng], { radius: 10000, color: '#2563eb', fillColor: '#2563eb', fillOpacity: 0.15 }).addTo(map);
      showToast('success', 'Location Found', 'Map zoomed to your current geolocation.');
    }, () => {
      showToast('error', 'Location Error', 'Unable to retrieve your current location.');
    });
  } else {
    showToast('warning', 'Not Supported', 'Geolocation is not supported by your browser.');
  }
}

function toggleMapFullscreen() {
  const mapContainer = document.querySelector('.port-map-wrapper');
  if (!mapContainer) return;

  if (!document.fullscreenElement) {
    mapContainer.requestFullscreen().then(() => {
      setTimeout(() => { window.globalThreatMap.invalidateSize(); }, 200);
    }).catch(err => {
      showToast('error', 'Fullscreen Error', 'Could not enable fullscreen mode.');
    });
  } else {
    document.exitFullscreen().then(() => {
      setTimeout(() => { window.globalThreatMap.invalidateSize(); }, 200);
    });
  }
}

function toggleShippingRoutes() {
  const map = window.globalThreatMap;
  if (!map) return;

  if (!shippingRoutesLayerGroup) {
    shippingRoutesLayerGroup = L.layerGroup();
    const CORRIDORS = [
      [[1.3, 103.8], [31.2, 121.5]], // Singapore to Shanghai
      [[1.3, 103.8], [6.0, 80.0], [12.0, 43.5], [30.0, 32.5], [35.0, 15.0], [51.9, 4.4]], // Singapore to Rotterdam via Suez
      [[35.0, 140.0], [45.0, -170.0], [34.0, -120.0]], // Tokyo to Los Angeles
      [[40.7, -74.0], [48.0, -30.0], [51.9, 4.4]], // New York to Rotterdam
      [[-33.9, 18.4], [1.3, 103.8]], // Cape Town to Singapore
    ];

    CORRIDORS.forEach(coords => {
      L.polyline(coords, {
        color: '#2563eb',
        dashArray: '6, 6',
        weight: 2.5,
        opacity: 0.6
      }).addTo(shippingRoutesLayerGroup);
    });
  }

  showShippingRoutes = !showShippingRoutes;
  const btn = document.getElementById('toggleRoutesBtn');
  if (showShippingRoutes) {
    map.addLayer(shippingRoutesLayerGroup);
    if (btn) btn.classList.replace('btn-outline-primary', 'btn-primary');
  } else {
    map.removeLayer(shippingRoutesLayerGroup);
    if (btn) btn.classList.replace('btn-primary', 'btn-outline-primary');
  }
}

function toggleWeatherOverlay() {
  const map = window.globalThreatMap;
  if (!map) return;

  if (!weatherLayerGroup) {
    weatherLayerGroup = L.layerGroup();
    const alertRegions = [
      { latlng: [15.0, 115.0], color: '#f59e0b', name: 'Low Pressure Zone Alpha' },
      { latlng: [25.0, 130.0], color: '#ef4444', name: 'Typhoon Warning Delta' },
      { latlng: [20.0, -60.0], color: '#3b82f6', name: 'Tropical Storm Zone Beta' }
    ];

    alertRegions.forEach(region => {
      L.circle(region.latlng, {
        radius: 400000,
        color: region.color,
        fillColor: region.color,
        fillOpacity: 0.2,
        weight: 1.5,
        dashArray: '3, 3'
      }).addTo(weatherLayerGroup).bindPopup(`<b>Weather Alert: ${region.name}</b>`);
    });
  }

  showWeatherOverlay = !showWeatherOverlay;
  const btn = document.getElementById('toggleWeatherBtn');
  if (showWeatherOverlay) {
    map.addLayer(weatherLayerGroup);
    if (btn) btn.classList.replace('btn-outline-warning', 'btn-warning');
  } else {
    map.removeLayer(weatherLayerGroup);
    if (btn) btn.classList.replace('btn-warning', 'btn-outline-warning');
  }
}

function toggleTrafficLayer() {
  const map = window.globalThreatMap;
  if (!map) return;

  if (!trafficLayerGroup) {
    trafficLayerGroup = L.layerGroup();
    const vessels = [
      { latlng: [10.0, 60.0], name: 'COSCO Shipping Pride', type: 'Container Carrier' },
      { latlng: [35.0, -150.0], name: 'Maersk Mc-Kinney Moller', type: 'Container Vessel' },
      { latlng: [-10.0, 80.0], name: 'Eneos Breeze', type: 'VLCC Oil Tanker' },
      { latlng: [43.0, -40.0], name: 'CMA CGM Antoine de Saint Exupery', type: 'Container Cargo' }
    ];

    vessels.forEach(v => {
      L.marker(v.latlng, {
        icon: L.divIcon({
          className: 'custom-vessel-icon',
          html: `<div style="font-size:16px;color:#10b981;transform:rotate(45deg);text-shadow:0 0 2px #fff;">🚢</div>`,
          iconAnchor: [8, 8]
        })
      }).addTo(trafficLayerGroup).bindPopup(`<b>Vessel: ${v.name}</b><br>Type: ${v.type}<br>Speed: 16.4 kts`);
    });
  }

  showTrafficLayer = !showTrafficLayer;
  const btn = document.getElementById('toggleTrafficBtn');
  if (showTrafficLayer) {
    map.addLayer(trafficLayerGroup);
    if (btn) btn.classList.replace('btn-outline-success', 'btn-success');
  } else {
    map.removeLayer(trafficLayerGroup);
    if (btn) btn.classList.replace('btn-success', 'btn-outline-success');
  }
}

function zoomToFullMapPort(p) {
  const input = document.getElementById('fullMapSearch');
  const suggest = document.getElementById('fullMapSuggestions');
  if (suggest) suggest.style.display = 'none';
  if (input) input.value = p.name || '';

  if (!p.latitude || !p.longitude) return;
  const map = window.globalThreatMap;
  if (map) {
    map.setView([parseFloat(p.latitude), parseFloat(p.longitude)], 9);
    if (fullMapCluster) {
      const markers = fullMapCluster.getLayers();
      const match = markers.find(m => m.options.portId === p.id);
      if (match) {
        fullMapCluster.zoomToShowLayer(match, () => {
          match.openPopup();
        });
      }
    }
  }
}

function resetFullMapFilters() {
  document.getElementById('fullMapCountryFilter').value = '';
  document.getElementById('fullMapTypeFilter').value = '';
  document.getElementById('fullMapSizeFilter').value = '';
  document.getElementById('fullMapRiskFilter').value = '';
  document.getElementById('fullMapSearch').value = '';
  applyFullMapFilters();
  showToast('info', 'Filters Reset', 'Map controls cleared and reloaded.');
}

function applyFullMapFilters() {
  if (!document.getElementById('pulse-styles-injected')) {
    const style = document.createElement('style');
    style.id = 'pulse-styles-injected';
    style.textContent = `
      @keyframes marker-pulse {
        0% { transform: scale(0.65); opacity: 0.9; }
        80%, 100% { opacity: 0; transform: scale(1.6); }
      }
      .marker-pulse-ring-high {
        border: 3px solid #f97316;
        border-radius: 50%;
        position: absolute;
        animation: marker-pulse 1.8s ease-out infinite;
        pointer-events: none;
      }
      .marker-pulse-ring-critical {
        border: 3px solid #ef4444;
        border-radius: 50%;
        position: absolute;
        animation: marker-pulse 1.4s ease-out infinite;
        pointer-events: none;
      }
    `;
    document.head.appendChild(style);
  }

  const country = document.getElementById('fullMapCountryFilter')?.value || '';
  const type = document.getElementById('fullMapTypeFilter')?.value || '';
  const sizeSelect = document.getElementById('fullMapSizeFilter')?.value || '';
  const risk = document.getElementById('fullMapRiskFilter')?.value || '';
  const searchInput = document.getElementById('fullMapSearch');
  const q = searchInput ? searchInput.value.trim().toLowerCase() : '';

  const filtered = PORTS.filter(p => {
    if (country && p.country_name !== country) return false;
    if (type && p.harbor_type !== type) return false;
    if (sizeSelect && p.harbor_size !== sizeSelect) return false;
    if (risk && p.risk_level !== risk) return false;
    if (q) {
      const name = (p.name || '').toLowerCase();
      const cn = (p.country_name || '').toLowerCase();
      const wpi = (p.wpi_code || '').toLowerCase();
      if (!name.includes(q) && !cn.includes(q) && !wpi.includes(q)) return false;
    }
    return true;
  });

  const map = window.globalThreatMap;
  if (map) {
    if (fullMapCluster) {
      try { map.removeLayer(fullMapCluster); } catch(e) {}
    }
    if (fullMapCountryLayer) {
      try { map.removeLayer(fullMapCountryLayer); } catch(e) {}
    }

    fullMapCluster = L.markerClusterGroup({
      showCoverageOnHover: false,
      maxClusterRadius: 50,
      spiderfyOnMaxZoom: true,
      iconCreateFunction: function(cluster) {
        const count = cluster.getChildCount();
        let bg = 'rgba(16, 185, 129, 0.85)';
        let cls = 'small';
        if (count >= 50) {
          bg = 'rgba(239, 68, 68, 0.85)';
          cls = 'large';
        } else if (count >= 10) {
          bg = 'rgba(245, 158, 11, 0.85)';
          cls = 'medium';
        }
        return L.divIcon({
          html: `<div style="background:${bg}; width: 38px; height: 38px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.8); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.15); font-size: .8rem; backdrop-filter: blur(4px);">${count}</div>`,
          className: 'custom-cluster-icon ' + cls,
          iconSize: [38, 38]
        });
      }
    });

    fullMapCountryLayer = L.layerGroup();

    filtered.forEach(port => {
      if (port.latitude == null || port.longitude == null) return;

      const isHighRisk = (port.risk_level || '').toLowerCase() === 'high' || (port.risk_level || '').toLowerCase() === 'critical';
      const isMajor = (port.harbor_size || '').toLowerCase() === 'very large' || (port.harbor_size || '').toLowerCase() === 'large' || (port.harbor_type || '').toLowerCase().includes('major');
      const isContainer = (port.harbor_type || '').toLowerCase().includes('container');
      const isOil = (port.harbor_type || '').toLowerCase().includes('oil');

      // Symbol
      let symbol = '⚓';
      if (isMajor) symbol = '🏗️';
      else if (isContainer) symbol = '📦';
      else if (isOil) symbol = '🛢️';

      // Colors
      let color = '#10b981'; // Green (default low)
      const level = (port.risk_level || 'Low').toLowerCase();
      if (isMajor) color = '#2563eb'; // Blue
      else if (isContainer) color = '#8b5cf6'; // Purple
      else if (isOil) color = '#000000'; // Black
      else {
        if (level === 'medium') color = '#f59e0b'; // Yellow
        else if (level === 'high') color = '#f97316'; // Orange
        else if (level === 'critical') color = '#ef4444'; // Red
      }

      // Sizing
      let size = 18;
      if (isMajor) size = 26;
      else if (isContainer) size = 22;
      else if (isOil) size = 20;

      // Pulse
      let pulseHtml = '';
      if (level === 'high') {
        pulseHtml = `<div class="marker-pulse-ring-high" style="width: ${size + 6}px; height: ${size + 6}px; left: -3px; top: -3px;"></div>`;
      } else if (level === 'critical') {
        pulseHtml = `<div class="marker-pulse-ring-critical" style="width: ${size + 8}px; height: ${size + 8}px; left: -4px; top: -4px;"></div>`;
      }

      const customIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `
          <div class="position-relative d-flex align-items-center justify-content-center" style="width:${size}px; height:${size}px;">
            ${pulseHtml}
            <div style="font-size:${size}px; color:${color}; text-shadow:0 0 3px #fff; font-weight:bold; z-index:10; line-height: 1;">
              ${symbol}
            </div>
          </div>
        `,
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2]
      });

      const marker = L.marker([Number(port.latitude), Number(port.longitude)], {
        icon: customIcon,
        portId: port.id
      });

      const trade = (Math.random() * 45 + 10).toFixed(1) + ' MT';
      const containerCap = (Math.random() * 10 + 2).toFixed(1) + 'M TEU';
      const oilCap = (Math.random() * 6 + 1).toFixed(1) + 'M Barrels';
      const score = port.risk_score || (port.risk_level === 'High' ? 7.2 : (port.risk_level === 'Critical' ? 9.1 : (port.risk_level === 'Medium' ? 4.5 : 1.8)));
      const weatherText = port.weather || 'Safe';

      marker.bindPopup(`
        <div style="font-family:var(--font-sans); min-width:240px; padding: 4px;">
          <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
            <span style="font-size: 1.1rem;">${symbol}</span>
            <h6 class="fw-bold mb-0 text-dark" style="font-size:.9rem;">${port.name}</h6>
          </div>
          <div class="d-flex flex-column gap-1.5" style="font-size: .75rem; color: var(--text-muted);">
            <div class="d-flex justify-content-between"><span>Country:</span><strong class="text-dark">${port.country_name || 'Global'}</strong></div>
            <div class="d-flex justify-content-between"><span>Harbor Type:</span><strong class="text-dark">${port.harbor_type || 'N/A'}</strong></div>
            <div class="d-flex justify-content-between"><span>Harbor Size:</span><strong class="text-dark">${port.harbor_size || 'N/A'}</strong></div>
            <div class="d-flex justify-content-between"><span>Risk Score:</span><strong style="color:${riskColor(port.risk_level)};">${parseFloat(score).toFixed(1)}</strong></div>
            <div class="d-flex justify-content-between"><span>Container Capacity:</span><strong class="text-dark">${containerCap}</strong></div>
            <div class="d-flex justify-content-between"><span>Oil Capacity:</span><strong class="text-dark">${oilCap}</strong></div>
            <div class="d-flex justify-content-between"><span>Weather:</span><strong class="text-dark">${weatherText}</strong></div>
            <div class="d-flex justify-content-between"><span>Trade Volume:</span><strong class="text-dark">${trade}</strong></div>
            <div class="d-flex justify-content-between"><span>Latitude:</span><strong class="text-dark">${parseFloat(port.latitude).toFixed(4)}</strong></div>
            <div class="d-flex justify-content-between"><span>Longitude:</span><strong class="text-dark">${parseFloat(port.longitude).toFixed(4)}</strong></div>
          </div>
          <button onclick="openPortSidePanelById(${port.id})" class="btn btn-sm btn-brand w-100 mt-2 fw-bold text-white" style="font-size:.7rem; border-radius:6px; background:linear-gradient(135deg,#2563eb,#7c3aed); border:none;">Open Details</button>
        </div>
      `, { maxWidth: 280 });

      fullMapCluster.addLayer(marker);
    });

    STATE.countries.forEach(c => {
      if (c.latitude == null || c.longitude == null) return;

      const color = '#10b981';
      const circle = L.circleMarker([Number(c.latitude), Number(c.longitude)], {
        radius: 12,
        fillColor: color,
        color: '#ffffff',
        weight: 2,
        fillOpacity: 0.8
      });

      circle.bindPopup(`
        <div style="font-family:var(--font-sans);">
          <h6 class="fw-bold mb-1" style="font-size:.85rem;">${c.name}</h6>
          <div class="small text-muted mb-2">${c.region || 'Global'}</div>
          <div class="small mb-1">GDP: <strong>$${c.gdp ? (parseFloat(c.gdp)/1e12).toFixed(2) + 'T' : 'N/A'}</strong></div>
          <div class="small mb-1">Risk Score: <strong style="color:${color};">${parseFloat(c.risk_score || 0).toFixed(1)}</strong></div>
        </div>
      `);

      fullMapCountryLayer.addLayer(circle);
    });

    map.addLayer(fullMapCluster);
    map.addLayer(fullMapCountryLayer);

    if (fullMapCluster.getLayers().length > 0) {
      map.fitBounds(fullMapCluster.getBounds().pad(0.1));
    }
  }

  updateFullMapStats(filtered);
  renderMapPageWidgets(filtered);
  updateVisibleTelemetryCounts();
}

function updateFullMapStats(activePorts) {
  const total = activePorts.length;
  const major = activePorts.filter(p => p.harbor_size === "Very Large" || p.harbor_size === "Large").length;
  const container = activePorts.filter(p => p.harbor_type === "Container").length;
  const oil = activePorts.filter(p => p.harbor_type === "Oil" || p.harbor_type === "Oil Terminal").length;
  const highRisk = activePorts.filter(p => p.risk_level === "High" || p.risk_level === "Critical").length;

  animateStatsCounter('fmp-total', total);
  animateStatsCounter('fmp-major', major);
  animateStatsCounter('fmp-container', container);
  animateStatsCounter('fmp-oil', oil);
  animateStatsCounter('fmp-highrisk', highRisk);
}

function animateStatsCounter(id, targetVal) {
  const el = document.getElementById(id);
  if (!el) return;
  
  let current = 0;
  const duration = 800; // ms
  const stepTime = Math.max(Math.floor(duration / (targetVal || 1)), 15);
  
  const timer = setInterval(() => {
    current += Math.ceil(targetVal / 30) || 1;
    if (current >= targetVal) {
      el.textContent = targetVal;
      clearInterval(timer);
    } else {
      el.textContent = current;
    }
  }, stepTime);
}

function renderMapPageWidgets(filteredPorts) {
  // 1. Highest Risk Ports table
  const riskTableBody = document.querySelector('#highestRiskPortsTable tbody');
  if (riskTableBody) {
    const sorted = [...filteredPorts].sort((a,b) => (b.risk_score || 0) - (a.risk_score || 0)).slice(0, 10);
    riskTableBody.innerHTML = sorted.map(p => {
      const color = riskColor(p.risk_level);
      return `<tr>
        <td class="fw-bold">${p.name}</td>
        <td>${p.country_name || 'N/A'}</td>
        <td class="text-end"><span class="badge" style="background:${color}; color:#fff; font-size:.65rem;">${p.risk_level || 'Low'}</span></td>
      </tr>`;
    }).join('');
  }

  // 2. Supply Chain Alerts Timeline
  const timeline = document.getElementById('mapPageTimeline');
  if (timeline) {
    const alerts = [
      { text: 'Congestion spike at Shanghai port terminal.', time: '10 mins ago', level: 'warning' },
      { text: 'Severe meteorological warnings active near Transpacific shipping routes.', time: '1 hr ago', level: 'danger' },
      { text: 'Customs delays reported at Port of Hamburg due to IT system upgrades.', time: '4 hrs ago', level: 'info' },
      { text: 'Vessel queue times at Los Angeles terminals reduced to normal levels.', time: '1 day ago', level: 'success' }
    ];
    timeline.innerHTML = alerts.map(a => {
      const borderCol = a.level === 'danger' ? '#ef4444' : (a.level === 'warning' ? '#f59e0b' : '#3b82f6');
      return `<div class="p-2 rounded border-start border-4" style="background:rgba(0,0,0,0.02); border-left-color:${borderCol} !important; font-size:.7rem;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <strong class="text-capitalize text-dark" style="font-size:.65rem;">${a.level}</strong>
          <span class="text-muted" style="font-size:.6rem;">${a.time}</span>
        </div>
        <div class="text-muted" style="font-size:.65rem;">${a.text}</div>
      </div>`;
    }).join('');
  }

  // 3. Port Traffic Volume Bar Chart
  const topPorts = [...filteredPorts].slice(0, 6);
  const trafficLabels = topPorts.map(p => p.name);
  const trafficData = topPorts.map(p => p.harbor_size === 'Very Large' ? 95 : (p.harbor_size === 'Large' ? 70 : 40));

  if (window.mapPageChartsTraffic) window.mapPageChartsTraffic.destroy();
  const trafficCanvas = document.getElementById('mapPageTrafficChart');
  if (trafficCanvas) {
    window.mapPageChartsTraffic = new Chart(trafficCanvas.getContext('2d'), {
      type: 'bar',
      data: {
        labels: trafficLabels,
        datasets: [{ label: 'Traffic Index', data: trafficData, backgroundColor: '#2563eb', borderRadius: 4 }]
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
  }

  // 4. Port Type Composition Pie Chart
  const types = { Container: 0, Oil: 0, Bulk: 0, Other: 0 };
  filteredPorts.forEach(p => {
    const t = p.harbor_type;
    if (t === 'Container') types.Container++;
    else if (t === 'Oil' || t === 'Oil Terminal') types.Oil++;
    else if (t === 'Bulk') types.Bulk++;
    else types.Other++;
  });

  if (window.mapPageChartsType) window.mapPageChartsType.destroy();
  const typeCanvas = document.getElementById('mapPageTypeChart');
  if (typeCanvas) {
    window.mapPageChartsType = new Chart(typeCanvas.getContext('2d'), {
      type: 'pie',
      data: {
        labels: ['Container', 'Oil', 'Bulk', 'Other'],
        datasets: [{ data: [types.Container, types.Oil, types.Bulk, types.Other], backgroundColor: ['#6366f1', '#f59e0b', '#10b981', '#94a3b8'] }]
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, font: { size: 8 } } } } }
    });
  }
}

/* ═══════════════════════════════════════════════════════════
   CHARTS — DASHBOARD
   ═══════════════════════════════════════════════════════════ */
function buildDashCharts() {
  chartDefaults();

  // 1. Risk Distribution (Doughnut Chart + stats) (Step 2)
  buildRiskPieChart();

  // 2. Risk Bar (Step 1 Top 10 Ranking)
  buildRiskBarChart();

  // 3. Top 10 Risk List (replaces skeleton) (Step 1)
  buildTopRiskList();

  // 4. Currency Line Chart (Step 3)
  buildCurrencyLineChart();

  // 5. Sidebar Statistics (Step 8)
  updateSidebarStats();
}

function buildRiskPieChart() {
  const low = STATE.countries.filter(c => c.risk_level === 'Low').length;
  const med = STATE.countries.filter(c => c.risk_level === 'Medium').length;
  const high = STATE.countries.filter(c => c.risk_level === 'High').length;
  const crit = STATE.countries.filter(c => c.risk_level === 'Critical').length;
  const total = STATE.countries.length;

  const sorted = [...STATE.countries].filter(c => c.risk_score !== null).sort((a,b) => b.risk_score - a.risk_score);
  const highestCountry = sorted[0] ? sorted[0].name : 'N/A';
  const averageRisk = STATE.countries.reduce((acc, c) => acc + (c.risk_score || 0), 0) / (total || 1);

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

  // Render Risk Stats underneath (Step 2)
  document.getElementById('riskPieStats').innerHTML = `
    <div class="col-4 border-end" style="border-color:var(--border-color)!important;">
      <div style="font-size:.62rem;color:var(--text-muted);text-transform:uppercase;">Total Countries</div>
      <div style="font-weight:800;font-size:.85rem;color:var(--text-primary);">${total}</div>
    </div>
    <div class="col-4 border-end" style="border-color:var(--border-color)!important;">
      <div style="font-size:.62rem;color:var(--text-muted);text-transform:uppercase;">Highest Risk</div>
      <div style="font-weight:800;font-size:.85rem;color:var(--text-primary);text-overflow:ellipsis;overflow:hidden;white-space:nowrap;" title="${highestCountry}">${highestCountry}</div>
    </div>
    <div class="col-4">
      <div style="font-size:.62rem;color:var(--text-muted);text-transform:uppercase;">Average Risk</div>
      <div style="font-weight:800;font-size:.85rem;color:var(--text-primary);">${averageRisk.toFixed(1)}</div>
    </div>
  `;
}

function buildRiskBarChart() {
  const sorted = [...STATE.countries].filter(c => c.risk_score !== null).sort((a,b) => b.risk_score - a.risk_score);
  const top10 = sorted.slice(0, 10);
  
  if (STATE.charts.dashRiskBar) STATE.charts.dashRiskBar.destroy();
  STATE.charts.dashRiskBar = new Chart(
    document.getElementById('dashRiskBar').getContext('2d'), {
    type: 'bar',
    data: {
      labels: top10.map(c => c.name || 'Unknown'),
      datasets: [{
        label: 'Risk Score',
        data: top10.map(c => parseFloat(c.risk_score).toFixed(1)),
        backgroundColor: top10.map(c => riskColor(c.risk_level) + 'CC'),
        borderColor: top10.map(c => riskColor(c.risk_level)),
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
}

function buildTopRiskList() {
  const sorted = [...STATE.countries].filter(c => c.risk_score !== null).sort((a,b) => b.risk_score - a.risk_score);
  const top10 = sorted.slice(0, 10);
  const listEl = document.getElementById('dashTopRisk');
  if (!listEl) return;
  listEl.innerHTML = '';
  top10.forEach((c, i) => {
    const badgeClass = (c.risk_level || 'Low').toLowerCase();
    const trendIcon = c.risk_score > 50 ? 'bi-arrow-up-right text-danger' : 'bi-arrow-down-left text-success';
    listEl.innerHTML += `
      <div class="d-flex align-items-center gap-2 py-2 border-bottom" style="border-color:var(--border-color)!important;cursor:pointer;" onclick="viewCountry(${c.id})">
        <span style="font-size:.7rem;font-weight:700;color:var(--text-muted);width:16px;">${i+1}</span>
        <img src="${c.flag_url}" style="border-radius:2px;width:20px;border:1px solid var(--border-color);" alt="">
        <span style="flex:1;font-size:.82rem;font-weight:500;color:var(--text-primary);">${c.name}</span>
        <span style="font-family:var(--font-mono);font-size:.78rem;font-weight:700;">${parseFloat(c.risk_score).toFixed(1)}</span>
        <span class="risk-pill ${badgeClass}">${c.risk_level || 'Low'}</span>
        <i class="bi ${trendIcon} ms-1" style="font-size:.75rem;"></i>
      </div>
    `;
  });
}

function buildCurrencyLineChart() {
  const activeCur = document.getElementById('dashCurrencySelector')?.value || 'EUR';
  const history = STATE.currencies.history || [];
  
  const labels = history.map(item => item.date);
  const values = history.map(item => item[activeCur] !== undefined ? parseFloat(item[activeCur]) : null);

  if (STATE.charts.dashCurrencyLine) STATE.charts.dashCurrencyLine.destroy();
  if (!labels.length) return; // no history yet
  STATE.charts.dashCurrencyLine = new Chart(
    document.getElementById('dashCurrencyLine').getContext('2d'), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: `1 USD to ${activeCur}`,
        data: values,
        borderColor: '#f59e0b',
        backgroundColor: 'rgba(245,158,11,0.05)',
        borderWidth: 2.5,
        tension: 0.3,
        fill: true,
        pointRadius: 2,
        pointHoverRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
        y: { grid: { color: Chart.defaults.borderColor }, ticks: { font: { size: 10 } } }
      }
    }
  });
}

function updateSidebarStats() {
  const container = document.getElementById('dashSidebarStats');
  if (!container) return;

  // 1. Top Risk Country
  const sortedRisks = [...STATE.countries].filter(c => c.risk_score !== null).sort((a, b) => b.risk_score - a.risk_score);
  const topRisk = sortedRisks[0] || { name: 'N/A', risk_score: 0, risk_level: 'Low' };

  // 2. Top Port
  const majorPorts = PORTS.filter(p => p.harbor_size === 'Large');
  const topPort = majorPorts[0] || PORTS[0] || { name: 'N/A' };

  // 3. Most Active Regions
  const regionCounts = {};
  STATE.countries.forEach(c => {
    if (c.region) regionCounts[c.region] = (regionCounts[c.region] || 0) + 1;
  });
  let activeRegion = 'N/A';
  let maxCount = 0;
  for (const r in regionCounts) {
    if (regionCounts[r] > maxCount) {
      maxCount = regionCounts[r];
      activeRegion = r;
    }
  }

  // 4. Highest Inflation
  const sortedInflation = [...STATE.countries].filter(c => c.inflation !== null).sort((a, b) => b.inflation - a.inflation);
  const topInflation = sortedInflation[0] || { name: 'N/A', inflation: 0 };

  // 5. Highest GDP
  const sortedGdp = [...STATE.countries].filter(c => c.gdp !== null).sort((a, b) => b.gdp - a.gdp);
  const topGdp = sortedGdp[0] || { name: 'N/A', gdp: 0 };

  const rows = [
    { label: 'Top Risk Country', value: `${topRisk.name} (${parseFloat(topRisk.risk_score || 0).toFixed(1)})`, icon: 'bi-exclamation-triangle-fill', color: 'red' },
    { label: 'Top Port', value: topPort.name, icon: 'bi-anchor', color: 'blue' },
    { label: 'Most Active Region', value: `${activeRegion} (${maxCount} nations)`, icon: 'bi-globe2', color: 'green' },
    { label: 'Highest Inflation', value: `${topInflation.name} (${parseFloat(topInflation.inflation || 0).toFixed(2)}%)`, icon: 'bi-percent', color: 'amber' },
    { label: 'Highest GDP', value: `${topGdp.name} ($${(parseFloat(topGdp.gdp || 0) / 1e12).toFixed(2)}T)`, icon: 'bi-currency-dollar', color: 'teal' }
  ];

  container.innerHTML = rows.map(item => `
    <div class="d-flex align-items-center gap-3">
      <div class="stat-icon ${item.color}" style="width:32px;height:32px;font-size:.85rem;border-radius:8px;flex-shrink:0;">
        <i class="bi ${item.icon}"></i>
      </div>
      <div style="flex:1;">
        <div style="font-size:.68rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);">${item.label}</div>
        <div style="font-size:.82rem;font-weight:700;color:var(--text-primary);">${item.value}</div>
      </div>
    </div>
  `).join('');
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
      STATE.ports = portsRes.value.data.map(p => {
        p.harbor_size = getResolvedHarborSize(p.name, p.harbor_size);
        p.wpi_code = getResolvedWpiCode(p.id, p.wpi_code);
        p.harbor_type = p.harbor_type || 'Port';
        return p;
      });
      // Sync global PORTS
      PORTS.length = 0;
      PORTS.push(...STATE.ports);
    }

    // Countries
    if (countriesRes.status === 'fulfilled' && countriesRes.value.status) {
      STATE.countries = countriesRes.value.data;
      populateCountrySelects();
    }

    // Maps
    populateMaps();

    // Charts
    buildDashCharts();
    updateDashNewsCompact();
    const select = document.getElementById('currencyChartSelect');
    if (select) {
      buildFullCurrencyChart(select.value);
    }

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
  tbody.innerHTML = STATE.ports.map(p => {
    // 1. Resolve harbor size (Step 1)
    const rawSize = p.harbor_size || p.size;
    const size = getResolvedHarborSize(p.name, rawSize);
    
    // Size badge (Step 3)
    let sizeBadge = '';
    if (size === 'Very Large') {
      sizeBadge = '<span class="badge" style="background:#7c3aed!important;color:#fff;">Very Large</span>';
    } else if (size === 'Large') {
      sizeBadge = '<span class="badge" style="background:#2563eb!important;color:#fff;">Large</span>';
    } else if (size === 'Medium') {
      sizeBadge = '<span class="badge" style="background:#f97316!important;color:#fff;">Medium</span>';
    } else {
      sizeBadge = '<span class="badge" style="background:#64748b!important;color:#fff;">Small</span>';
    }

    // 2. Resolve harbor type badge (Step 3)
    const type = p.harbor_type || 'Port';
    let typeBadge = '';
    const t = type.toLowerCase();
    if (t.includes('container')) {
      typeBadge = '<span class="badge" style="background:#6366f1!important;color:#fff;">Container</span>';
    } else if (t.includes('oil') || t.includes('terminal')) {
      typeBadge = '<span class="badge text-dark" style="background:#f59e0b!important;color:#1e293b;">Oil</span>';
    } else if (t.includes('river')) {
      typeBadge = '<span class="badge" style="background:#0d9488!important;color:#fff;">River</span>';
    } else if (t.includes('military')) {
      typeBadge = '<span class="badge" style="background:#dc2626!important;color:#fff;">Military</span>';
    } else if (t.includes('fishing')) {
      typeBadge = '<span class="badge" style="background:#10b981!important;color:#fff;">Fishing</span>';
    } else {
      typeBadge = '<span class="badge" style="background:#3b82f6!important;color:#fff;">Port</span>';
    }

    // 3. Resolve WPI code (Step 2)
    const wpi = getResolvedWpiCode(p.id, p.wpi_code);

    // 4. Resolve Risk level (Step 4)
    const countryName = p.country?.name || p.country_name || 'Unknown';
    const co = STATE.countries.find(c => c.name === countryName) || {};
    const riskLevel = p.risk_level || co.risk_level || 'Low';
    let riskBadge = '';
    if (riskLevel === 'Low') {
      riskBadge = '<span class="badge text-success" style="background:rgba(16,185,129,.15);border:1px solid #10b981;">🟢 Low</span>';
    } else if (riskLevel === 'Medium') {
      riskBadge = '<span class="badge text-warning" style="background:rgba(245,158,11,.15);border:1px solid #f59e0b;">🟡 Medium</span>';
    } else {
      riskBadge = '<span class="badge text-danger" style="background:rgba(239,68,68,.15);border:1px solid #ef4444;">🔴 High</span>';
    }

    // 5. Resolve Traffic (Step 4)
    let traffic = 'Normal';
    if (size === 'Very Large' || size === 'Large') traffic = 'Heavy';
    else if (size === 'Small') traffic = 'Low';
    
    let trafficBadge = '';
    if (traffic === 'Heavy') {
      trafficBadge = '<span class="badge text-danger" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);">Heavy</span>';
    } else if (traffic === 'Normal') {
      trafficBadge = '<span class="badge text-primary" style="background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.2);">Normal</span>';
    } else {
      trafficBadge = '<span class="badge text-muted" style="background:rgba(148,163,184,.1);border:1px solid rgba(148,163,184,.2);">Low</span>';
    }

    // 6. Resolve Status (Step 4)
    let status = 'Operational';
    if (p.id % 15 === 0) status = 'Maintenance';
    else if (p.id % 27 === 0) status = 'Closed';

    let statusBadge = '';
    if (status === 'Operational') {
      statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle">Operational</span>';
    } else if (status === 'Maintenance') {
      statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Maintenance</span>';
    } else {
      statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Closed</span>';
    }

    // Flag URL
    const flagImg = p.country?.code
      ? `<img src="https://flagcdn.com/w20/${p.country.code.toLowerCase()}.png" class="me-1" width="16" style="border-radius:2px;border:1px solid var(--border-color);" alt="">`
      : '';

    // Action buttons (Step 5)
    const actionsHtml = `
      <div class="d-flex gap-1">
        <button class="btn btn-xs btn-outline-brand p-1" onclick="openPortSidePanelById(${p.id})" title="View Port Details" data-bs-toggle="tooltip">
          <i class="bi bi-info-circle" style="font-size:.75rem;"></i>
        </button>
        <button class="btn btn-xs btn-outline-brand p-1" onclick="showPortOnMap(${p.latitude}, ${p.longitude})" title="Locate on Map" data-bs-toggle="tooltip">
          <i class="bi bi-geo-alt" style="font-size:.75rem;"></i>
        </button>
        <button class="btn btn-xs btn-outline-brand p-1" onclick="showPortWeather(${p.id}, '${p.name.replace(/'/g, "\\'")}')" title="Check Weather" data-bs-toggle="tooltip">
          <i class="bi bi-cloud-sun" style="font-size:.75rem;"></i>
        </button>
      </div>
    `;

    return `
      <tr>
        <td><strong>${p.name}</strong></td>
        <td>${flagImg}${countryName}</td>
        <td style="font-family:var(--font-mono);font-size:.75rem;">${parseFloat(p.latitude).toFixed(4)}, ${parseFloat(p.longitude).toFixed(4)}</td>
        <td>${sizeBadge}</td>
        <td>${typeBadge}</td>
        <td><code style="font-size:.75rem;">${wpi}</code></td>
        <td>${riskBadge}</td>
        <td>${trafficBadge}</td>
        <td>${statusBadge}</td>
        <td>${actionsHtml}</td>
      </tr>
    `;
  }).join('');

  STATE.dt.ports = new DataTable('#portsTable', {
    responsive:true, pageLength:25, lengthMenu:[10,25,50,100,-1],
    language:{ search:'', searchPlaceholder:'Filter ports...', lengthMenu:'Show _MENU_' }
  });
  
  // Re-initialize tooltips on DataTable draw
  STATE.dt.ports.on('draw', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('#portsTable [data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });

  const tooltipTriggerList = [].slice.call(document.querySelectorAll('#portsTable [data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  portsDtInited = true;
}

function openPortSidePanelById(id) {
  const p = STATE.ports.find(port => port.id === id);
  if (p) openPortSidePanel(p);
}

function showPortOnMap(lat, lng) {
  showPage('map');
  function setViewWhenReady() {
    if (STATE.maps.main) {
      STATE.maps.main.setView([lat, lng], 14);
      if (fullMapCluster) {
        const layers = fullMapCluster.getLayers();
        const match = layers.find(l => {
          const latLng = l.getLatLng();
          return Math.abs(latLng.lat - lat) < 0.0001 && Math.abs(latLng.lng - lng) < 0.0001;
        });
        if (match) {
          fullMapCluster.zoomToShowLayer(match, () => {
            match.openPopup();
          });
        }
      }
    } else {
      setTimeout(setViewWhenReady, 100);
    }
  }
  setViewWhenReady();
}

async function showPortWeather(portId, portName) {
  const port = STATE.ports.find(p => p.id === portId);
  if (!port || !port.latitude || !port.longitude) return;
  
  showToast('info', 'Weather Intel', `Fetching weather for ${portName}...`);
  try {
    const res = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${port.latitude}&longitude=${port.longitude}&current_weather=true`);
    const data = await res.json();
    if (data && data.current_weather) {
      const temp = data.current_weather.temperature;
      const wind = data.current_weather.windspeed;
      showToast('success', `Weather: ${portName}`, `Temperature: ${temp}°C | Wind: ${wind} km/h`);
    } else {
      throw new Error();
    }
  } catch(e) {
    showToast('danger', 'Weather Fetch Failed', 'Could not fetch meteorological weather data.');
  }
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

  const select = document.getElementById('currencyChartSelect');
  if (select) {
    buildFullCurrencyChart(select.value);
    select.onchange = function() {
      buildFullCurrencyChart(this.value);
    };
  }

  const convertBtn = document.getElementById('convertBtn');
  if (convertBtn) {
    convertBtn.onclick = () => {
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
    };
  }
}

function buildFullCurrencyChart(targetCurrency) {
  const historyArray = STATE.currencies.history || [];
  const last7Days = historyArray.slice(-7);
  
  let labels = last7Days.map(item => {
    const d = new Date(item.date);
    if (isNaN(d.getTime())) return item.date;
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  });
  
  let data = last7Days.map(item => item[targetCurrency]).filter(val => val !== undefined && val !== null);
  const currentRate = STATE.currencies.latest_rates?.[targetCurrency] || 1.0;

  if (data.length === 0) {
    const baseVal = parseFloat(currentRate);
    data = [];
    labels = [];
    const offsets = [-0.005, -0.003, -0.001, -0.002, 0.001, 0.000];
    for (let i = 6; i >= 0; i--) {
      const d = new Date();
      d.setDate(d.getDate() - i);
      labels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
      
      const offset = (offsets[6 - i] || 0) * baseVal;
      data.push(Number((baseVal + offset).toFixed(4)));
    }
  }

  const canvas = document.getElementById('currencyTrendChart');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  
  const gradient = ctx.createLinearGradient(0, 0, 0, 220);
  gradient.addColorStop(0, 'rgba(245, 158, 11, 0.25)');
  gradient.addColorStop(1, 'rgba(245, 158, 11, 0.0)');

  if (STATE.charts.currencyTrend) STATE.charts.currencyTrend.destroy();
  STATE.charts.currencyTrend = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: `USD/${targetCurrency}`,
        data,
        borderColor: '#f59e0b',
        backgroundColor: gradient,
        fill: true,
        tension: 0.4,
        borderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: '#f59e0b',
        pointBorderColor: '#fff',
        pointBorderWidth: 1.5,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          enabled: true,
          mode: 'index',
          intersect: false,
          backgroundColor: 'rgba(15, 23, 42, 0.9)',
          titleColor: '#fff',
          bodyColor: '#fff',
          borderColor: 'rgba(245, 158, 11, 0.3)',
          borderWidth: 1,
          padding: 8,
          bodyFont: { family: 'var(--font-sans)', size: 12 },
          titleFont: { family: 'var(--font-sans)', size: 12, weight: 'bold' }
        }
      },
      interaction: {
        intersect: false,
        mode: 'index',
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: 'var(--text-muted)',
            font: { size: 10, family: 'var(--font-sans)' }
          }
        },
        y: {
          grid: { color: 'rgba(148, 163, 184, 0.1)' },
          ticks: {
            color: 'var(--text-muted)',
            font: { size: 10, family: 'var(--font-sans)' }
          }
        }
      }
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

// Bootstrap tooltips & Leaflet Single Map Initialization
document.addEventListener('DOMContentLoaded', () => {
  // Initialize Tooltips
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el, { trigger:'hover' });
  });

  // Verify Blade sends data (Step 4 & 8)
  const countriesData = @json($countries);
  const portsData = @json($ports);
  const riskScoresData = @json($riskScores);
  if (!countriesData || !portsData || !riskScoresData) {
    console.error("Blade template error: Required dataset variables (countries, ports, or riskScores) are NULL!");
  }

  // Create brand new map initialization (Step 2)
  const map = L.map("globalThreatMap", {
    center: [20, 0],
    zoom: 2,
    zoomControl: true
  });

  // Add OpenStreetMap tiles (Step 2 & 9)
  L.tileLayer(
    "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
    {
      maxZoom: 19,
      attribution: '© OpenStreetMap'
    }
  ).addTo(map);

  window.globalThreatMap = map;

  // Immediately draw Singapore test marker after tile layer (Step 5)
  L.marker([1.290270, 103.851959])
    .addTo(map)
    .bindPopup("Singapore Test");

  // Invalidate map size after 500ms (Step 3)
  setTimeout(() => {
    if (window.globalThreatMap) {
      window.globalThreatMap.invalidateSize();
    }
  }, 500);
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
   QUICK COUNTRY COMPARISON
   ═══════════════════════════════════════════════════════════ */
let selectedCompCountries = { A: null, B: null, C: null };

function initComparisonSearch() {
  ['A', 'B', 'C'].forEach(suffix => {
    const input = document.getElementById(`compCountry${suffix}`);
    const suggestions = document.getElementById(`compSuggestions${suffix}`);
    if (!input || !suggestions) return;

    input.addEventListener('input', () => {
      const q = input.value.trim().toLowerCase();
      if (!q) {
        suggestions.innerHTML = '';
        suggestions.style.display = 'none';
        selectedCompCountries[suffix] = null;
        return;
      }

      const matches = STATE.countries.filter(c => 
        c.name.toLowerCase().includes(q) || c.code.toLowerCase().includes(q)
      );

      suggestions.innerHTML = matches.map(c => `
        <div onclick="selectCompCountry('${suffix}', ${c.id}, '${c.name.replace(/'/g, "\\'")}')">
          <img src="${c.flag_url}" width="18" style="border-radius:2px; border: 1px solid var(--border-color);">
          <span>${c.name} (${c.code.toUpperCase()})</span>
        </div>
      `).join('');

      if (matches.length > 0) {
        suggestions.style.display = 'block';
      } else {
        suggestions.innerHTML = '<div class="text-muted small p-2">No matches found</div>';
        suggestions.style.display = 'block';
      }
    });

    input.addEventListener('blur', () => {
      setTimeout(() => {
        suggestions.style.display = 'none';
      }, 250);
    });

    input.addEventListener('focus', () => {
      if (input.value.trim() && suggestions.innerHTML) {
        suggestions.style.display = 'block';
      }
    });
  });
}

function selectCompCountry(suffix, id, name) {
  const input = document.getElementById(`compCountry${suffix}`);
  const suggestions = document.getElementById(`compSuggestions${suffix}`);
  if (!input) return;

  const country = STATE.countries.find(c => c.id === id);
  if (country) {
    selectedCompCountries[suffix] = country;
    input.value = name;
    suggestions.innerHTML = '';
    suggestions.style.display = 'none';
  }
}

function swapComparisonCountries() {
  const tempCountry = selectedCompCountries.A;
  selectedCompCountries.A = selectedCompCountries.B;
  selectedCompCountries.B = tempCountry;

  const valA = document.getElementById('compCountryA').value;
  const valB = document.getElementById('compCountryB').value;

  document.getElementById('compCountryA').value = valB;
  document.getElementById('compCountryB').value = valA;

  if (document.getElementById('comparisonResultArea').style.display === 'block') {
    runComparison();
  }
}

function resetComparison() {
  selectedCompCountries = { A: null, B: null, C: null };
  document.getElementById('compCountryA').value = '';
  document.getElementById('compCountryB').value = '';
  document.getElementById('compCountryC').value = '';
  document.getElementById('comparisonResultArea').style.display = 'none';

  if (STATE.charts.compRadar) {
    STATE.charts.compRadar.destroy();
    STATE.charts.compRadar = null;
  }
}

function runComparison() {
  if (!selectedCompCountries.A || !selectedCompCountries.B) {
    showToast('warning', 'Selection Required', 'Please select at least Country A and Country B to compare.');
    return;
  }

  const activeCountries = [selectedCompCountries.A, selectedCompCountries.B, selectedCompCountries.C].filter(Boolean);
  
  // Show results panel
  document.getElementById('comparisonResultArea').style.display = 'block';

  // 1. Summary Cards Row
  const summaryEl = document.getElementById('compSummaryCards');
  summaryEl.innerHTML = activeCountries.map(c => {
    const risk = STATE.risks.find(r => r.country_id === c.id) || {};
    const color = riskColor(c.risk_level);
    const cPorts = PORTS.filter(p => p.country_name === c.name || p.country?.name === c.name);
    const newsCount = STATE.news.filter(n => {
      const text = ((n.title || '') + ' ' + (n.description || '') + ' ' + (n.content || '')).toLowerCase();
      return text.includes(c.name.toLowerCase());
    }).length;

    const weatherVal = risk.weather_score || 0;
    const weatherDesc = weatherVal > 70 ? '⚠️ High Storm Risk' : weatherVal > 40 ? '⛅ Moderate Weather' : '☀️ Fair Conditions';

    const closedPorts = cPorts.filter(p => {
      const status = (p.id % 27 === 0) ? 'Closed' : (p.id % 15 === 0) ? 'Maintenance' : 'Operational';
      return status === 'Closed';
    }).length;
    const portStatus = closedPorts > 0 ? `⚠️ ${closedPorts} Closed` : '🟢 Operational';

    const gdpStr = c.gdp ? '$' + (parseFloat(c.gdp) / 1e12).toFixed(2) + 'T' : 'N/A';
    const popStr = c.population ? (parseFloat(c.population) / 1e6).toFixed(1) + 'M' : 'N/A';
    const infStr = c.inflation ? parseFloat(c.inflation).toFixed(2) + '%' : 'N/A';

    return `
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card p-3 h-100 position-relative shadow-sm" style="background:var(--bg-glass);border:1px solid var(--border-color);border-radius:16px;">
          <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom" style="border-color:var(--border-color)!important;">
            <img src="${c.flag_url}" style="border-radius:3px;width:36px;border:1px solid var(--border-color);">
            <div>
              <h6 class="fw-bold mb-0" style="font-size:.9rem;color:var(--text-primary);">${c.name}</h6>
              <div style="font-size:.7rem;color:var(--text-muted);">${c.code.toUpperCase()} · ${c.region || 'Other'}</div>
            </div>
            <span class="ms-auto badge" style="background:${color};color:#fff;font-size:.65rem;padding:3px 8px;border-radius:12px;">${(c.risk_level || 'Low').toUpperCase()}</span>
          </div>
          <div class="row g-2 small" style="color:var(--text-secondary);line-height:1.6;">
            <div class="col-6">GDP: <strong class="text-primary">${gdpStr}</strong></div>
            <div class="col-6">Population: <strong>${popStr}</strong></div>
            <div class="col-6">Inflation: <strong>${infStr}</strong></div>
            <div class="col-6">Currency: <strong>${c.currency || 'USD'}</strong></div>
            <div class="col-6">Risk Score: <strong style="color:${color};">${parseFloat(c.risk_score || 0).toFixed(1)}</strong></div>
            <div class="col-6">Total Ports: <strong>${cPorts.length}</strong></div>
            <div class="col-6">News Coverage: <strong>${newsCount} articles</strong></div>
            <div class="col-6">Weather: <strong>${weatherDesc}</strong></div>
            <div class="col-12 border-top pt-2 mt-2" style="border-color:var(--border-color)!important;">
              Port Network Health: <strong>${portStatus}</strong>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');

  // 2. Radar Chart Normalization & Construction
  const maxGdp = Math.max(...STATE.countries.map(co => co.gdp || 0)) || 1;
  const maxPop = Math.max(...STATE.countries.map(co => co.population || 0)) || 1;
  const maxPorts = Math.max(...activeCountries.map(co => PORTS.filter(p => p.country_name === co.name || p.country?.name === co.name).length)) || 1;

  const datasets = activeCountries.map((co, idx) => {
    const risk = STATE.risks.find(r => r.country_id === co.id) || {};
    const portsCount = PORTS.filter(p => p.country_name === co.name || p.country?.name === co.name).length;
    const weatherScore = risk.weather_score !== undefined ? parseFloat(risk.weather_score) : 0;
    
    const normRisk = parseFloat(co.risk_score || 0);
    const normGdp = Math.min(100, ((co.gdp || 0) / maxGdp) * 100);
    const normPop = Math.min(100, ((co.population || 0) / maxPop) * 100);
    const normInf = Math.max(0, 100 - (parseFloat(co.inflation || 0) * 8));
    const normPorts = Math.min(100, (portsCount / maxPorts) * 100);
    const normExchange = co.currency === 'USD' || co.currency === 'EUR' || co.currency === 'GBP' ? 95 : 75;
    const normWeather = Math.max(0, 100 - weatherScore);

    const colors = [
      { border: '#3b82f6', bg: 'rgba(59,130,246,0.2)' },
      { border: '#10b981', bg: 'rgba(16,185,129,0.2)' },
      { border: '#7c3aed', bg: 'rgba(124,92,237,0.2)' }
    ];
    const cl = colors[idx % 3];

    return {
      label: co.name,
      data: [normRisk, normGdp, normPop, normInf, normPorts, normExchange, normWeather],
      borderColor: cl.border,
      backgroundColor: cl.bg,
      borderWidth: 2,
      pointRadius: 3
    };
  });

  if (STATE.charts.compRadar) STATE.charts.compRadar.destroy();
  STATE.charts.compRadar = new Chart(
    document.getElementById('compRadarChart').getContext('2d'), {
    type: 'radar',
    data: {
      labels: ['Risk Score', 'GDP Size', 'Population Size', 'Inflation Control', 'Ports Network', 'Exchange Stability', 'Weather Safety'],
      datasets: datasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { font: { size: 10 } } } },
      scales: {
        r: {
          angleLines: { color: 'rgba(255,255,255,0.06)' },
          grid: { color: 'rgba(255,255,255,0.06)' },
          pointLabels: { font: { size: 10 } },
          ticks: { backdropColor: 'transparent', font: { size: 8 } },
          suggestedMin: 0,
          suggestedMax: 100
        }
      }
    }
  });

  // 3. Winner Algorithm
  const scoredCountries = activeCountries.map(co => {
    const risk = STATE.risks.find(r => r.country_id === co.id) || {};
    const portsCount = PORTS.filter(p => p.country_name === co.name || p.country?.name === co.name).length;
    const scoreVal = (100 - parseFloat(co.risk_score || 0)) * 1.5 +
                     (portsCount * 3) -
                     (parseFloat(co.inflation || 0) * 2) +
                     (co.currency === 'USD' || co.currency === 'EUR' ? 20 : 0);
    return { country: co, score: scoreVal, portsCount };
  });
  
  scoredCountries.sort((a,b) => b.score - a.score);
  const winner = scoredCountries[0].country;
  const winnerPorts = scoredCountries[0].portsCount;
  
  const reasons = [];
  if (parseFloat(winner.risk_score) < 45) reasons.push('Lowest overall supply chain threat profile');
  reasons.push(winner.currency === 'USD' || winner.currency === 'EUR' || winner.currency === 'SGD' ? 'Highly stable trade currency infrastructure' : 'Strong currency exchange metrics');
  if (winnerPorts > 5) reasons.push(`Superior port network capacity (${winnerPorts} logistics hubs)`);
  if (parseFloat(winner.inflation) < 4.0) reasons.push('Stable macroeconomic inflation controls');
  reasons.push('Robust trade corridor logistics performance indicators');

  document.getElementById('compWinnerCard').innerHTML = `
    <div class="d-flex align-items-center gap-2 mb-2">
      <span class="fs-4">🏆</span>
      <h6 class="fw-bold mb-0" style="color:var(--text-primary);">Best Overall Port Corridor</h6>
    </div>
    <div style="font-size:1.4rem;font-weight:800;color:var(--brand-500);margin-bottom:10px;">${winner.name}</div>
    <div class="fw-bold small mb-2" style="color:var(--text-primary);">Key Logistics Strengths:</div>
    <ul class="small mb-0 ps-3" style="color:var(--text-secondary);line-height:1.6;">
      ${reasons.map(r => `<li>${r}</li>`).join('')}
    </ul>
  `;

  // 4. Comparison Table Matrix
  const tableHeader = document.getElementById('compTableHeader');
  const tableBody = document.getElementById('compTableBody');
  
  tableHeader.innerHTML = `<th>Attribute</th>` + activeCountries.map(co => `
    <th>
      <div class="d-flex align-items-center gap-2">
        <img src="${co.flag_url}" width="16" style="border-radius:2px; border:1px solid var(--border-color);">
        <span>${co.name}</span>
      </div>
    </th>
  `).join('');

  const tableRows = [
    { label: 'GDP', valueFn: co => co.gdp ? '$' + (parseFloat(co.gdp) / 1e12).toFixed(2) + 'T' : 'N/A' },
    { label: 'Population', valueFn: co => co.population ? (parseFloat(co.population) / 1e6).toFixed(1) + 'M' : 'N/A' },
    { label: 'Inflation', valueFn: co => co.inflation ? parseFloat(co.inflation).toFixed(2) + '%' : 'N/A' },
    { label: 'Currency', valueFn: co => co.currency || 'USD' },
    { label: 'Risk Score', valueFn: co => parseFloat(co.risk_score || 0).toFixed(1) },
    { label: 'Major Ports', valueFn: co => PORTS.filter(p => (p.country_name === co.name || p.country?.name === co.name) && (p.harbor_size === 'Large' || p.harbor_size === 'Very Large')).length },
    { label: 'Weather Safety Index', valueFn: co => {
        const risk = STATE.risks.find(r => r.country_id === co.id) || {};
        return (100 - (risk.weather_score || 0)).toFixed(0) + '/100';
      }
    },
    { label: 'Exchange Rate vs USD', valueFn: co => {
        if (co.currency === 'USD') return '1.0000';
        const rateObj = STATE.currencies.latest_rates || {};
        const rate = rateObj[co.currency];
        return rate ? parseFloat(rate).toFixed(4) : 'N/A';
      }
    },
    { label: 'Supply Chain Risk', valueFn: co => {
        const color = riskColor(co.risk_level);
        return `<span class="badge" style="background:${color};color:#fff;font-size:.65rem;">${(co.risk_level || 'Low').toUpperCase()}</span>`;
      }
    },
    { label: 'News Coverage', valueFn: co => {
        const newsCount = STATE.news.filter(n => {
          const text = ((n.title || '') + ' ' + (n.description || '') + ' ' + (n.content || '')).toLowerCase();
          return text.includes(co.name.toLowerCase());
        }).length;
        return `${newsCount} articles`;
      }
    },
    { label: 'Container Ports', valueFn: co => PORTS.filter(p => (p.country_name === co.name || p.country?.name === co.name) && p.harbor_type.toLowerCase().includes('container')).length },
    { label: 'Oil Ports', valueFn: co => PORTS.filter(p => (p.country_name === co.name || p.country?.name === co.name) && p.harbor_type.toLowerCase().includes('oil')).length }
  ];

  tableBody.innerHTML = tableRows.map(row => `
    <tr>
      <td class="fw-bold small">${row.label}</td>
      ${activeCountries.map(co => `<td class="small">${row.valueFn(co)}</td>`).join('')}
    </tr>
  `).join('');
}

function exportComparison(format) {
  if (!selectedCompCountries.A || !selectedCompCountries.B) return;
  const activeCountries = [selectedCompCountries.A, selectedCompCountries.B, selectedCompCountries.C].filter(Boolean);

  if (format === 'csv') {
    let csv = 'Attribute,' + activeCountries.map(co => co.name).join(',') + '\n';
    const rows = document.querySelectorAll('#compTable tbody tr');
    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      const rowData = Array.from(cells).map(cell => `"${cell.textContent.trim().replace(/'/g, '').replace(/"/g, '""')}"`);
      csv += rowData.join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `country_comparison_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    showToast('success', 'Export CSV', 'Comparison report CSV file downloaded successfully.');
  } else if (format === 'excel') {
    let csv = 'Attribute,' + activeCountries.map(co => co.name).join(',') + '\n';
    const rows = document.querySelectorAll('#compTable tbody tr');
    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      const rowData = Array.from(cells).map(cell => `"${cell.textContent.trim().replace(/'/g, '').replace(/"/g, '""')}"`);
      csv += rowData.join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'application/vnd.ms-excel;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `country_comparison_${new Date().toISOString().slice(0,10)}.xls`;
    a.click();
    showToast('success', 'Export Excel', 'Comparison report Excel file downloaded successfully.');
  } else if (format === 'print' || format === 'pdf') {
    const w = window.open('', '_blank');
    const tableHtml = document.getElementById('comparisonResultArea').innerHTML;
    w.document.write(`
      <html>
        <head>
          <title>Supply Chain Intelligence - Country Comparison</title>
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
          <style>
            body { font-family: sans-serif; padding: 40px; background: #fff; color: #333; }
            .card { border: 1px solid #ddd; padding: 20px; border-radius: 12px; margin-bottom: 20px; }
            .badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; }
            .row { display: flex; flex-wrap: wrap; margin-right: -15px; margin-left: -15px; }
            .col-12 { flex: 0 0 100%; max-width: 100%; }
            .col-md-6 { flex: 0 0 50%; max-width: 50%; }
            .col-lg-4 { flex: 0 0 33.3333%; max-width: 33.3333%; }
            ul { padding-left: 20px; }
          </style>
        </head>
        <body>
          <h3 class="mb-2">Global Port & Country Comparison Matrix</h3>
          <p class="text-muted small">Generated on: ${new Date().toLocaleString()}</p>
          ${tableHtml}
        </body>
      </html>
    `);
    w.document.close();
    setTimeout(() => {
      w.print();
    }, 500);
  }
}

/* ═══════════════════════════════════════════════════════════
   COMPARE COUNTRIES FEATURE
   ═══════════════════════════════════════════════════════════ */
let compareCountriesSelected = { A: null, B: null };
let compareCharts = { radar: null, bar: null, gauge: null, trade: null, dist: null };
var compareMap = null;
var compareBordersLayer = null;
var comparePortsLayer = null;

function initCompareCountriesPage() {
  setupSearchableCompareInput('fullCompCountryA', 'fullCompSuggestionsA', 'A');
  setupSearchableCompareInput('fullCompCountryB', 'fullCompSuggestionsB', 'B');

  // Seed default selection for Indonesia and Malaysia if they exist
  if (!compareCountriesSelected.A || !compareCountriesSelected.B) {
    const ind = STATE.countries.find(c => c.name.toLowerCase().includes('indonesia'));
    const mys = STATE.countries.find(c => c.name.toLowerCase().includes('malaysia'));
    if (ind && mys) {
      compareCountriesSelected.A = ind;
      compareCountriesSelected.B = mys;
      document.getElementById('fullCompCountryA').value = ind.name;
      document.getElementById('fullCompCountryB').value = mys.name;
      runCompareCountries();
    }
  }

  setTimeout(() => {
    if (compareMap) compareMap.invalidateSize();
  }, 200);
}

function setupSearchableCompareInput(inputId, suggestId, key) {
  const input = document.getElementById(inputId);
  const suggest = document.getElementById(suggestId);
  if (!input || !suggest) return;

  input.oninput = function() {
    const q = this.value.trim().toLowerCase();
    if (!q || q.length < 1) { suggest.style.display = 'none'; return; }

    const matches = STATE.countries.filter(c => c.name.toLowerCase().includes(q)).slice(0, 8);
    if (!matches.length) {
      suggest.innerHTML = '<div style="padding:8px 12px;font-size:.75rem;color:var(--text-muted);">No countries found</div>';
      suggest.style.display = 'block';
      return;
    }

    suggest.innerHTML = matches.map(c => {
      return `<div class="suggestion-row" style="padding:8px 12px; cursor:pointer;" onclick="selectCompareCountryDirect('${key}', ${JSON.stringify(c).replace(/"/g, '&quot;')})">
        <img src="https://flagcdn.com/w20/${c.code.toLowerCase()}.png" class="me-2" width="16" style="border-radius:2px;">
        <strong>${c.name}</strong> <span style="font-size:.7rem;color:var(--text-muted);">(${c.region || 'Global'})</span>
      </div>`;
    }).join('');
    suggest.style.display = 'block';
  };

  document.addEventListener('click', function(e) {
    if (!input.contains(e.target) && !suggest.contains(e.target)) {
      suggest.style.display = 'none';
    }
  });
}

function selectCompareCountry(key, country) {
  compareCountriesSelected[key] = country;
  document.getElementById('fullCompCountry' + key).value = country.name;
  document.getElementById('fullCompSuggestions' + key).style.display = 'none';
  showToast('success', `Selected Country ${key}`, `${country.name} chosen as Comparison target ${key}`);
}

function selectCompareCountryDirect(key, country) {
  selectCompareCountry(key, country);
}

function runCompareCountries() {
  const coA = compareCountriesSelected.A;
  const coB = compareCountriesSelected.B;
  if (!coA || !coB) {
    showToast('warning', 'Selection Required', 'Please select both Country A and Country B before comparing.');
    return;
  }
  if (coA.id === coB.id) {
    showToast('warning', 'Same Country', 'Please select two different countries to compare.');
    return;
  }

  document.getElementById('compareResultContainer').style.display = 'block';

  const portsA = PORTS.filter(p => p.country_name === coA.name || p.country?.name === coA.name);
  const portsB = PORTS.filter(p => p.country_name === coB.name || p.country?.name === coB.name);

  renderCompareDetailCard('compareCardA', coA, portsA, '#2563eb');
  renderCompareDetailCard('compareCardB', coB, portsB, '#ec4899');

  renderCompareWinners(coA, coB, portsA, portsB);

  renderCompareTable(coA, coB, portsA, portsB);

  document.getElementById('compareInsightsBody').innerHTML = generateSmartInsights(coA, coB, portsA, portsB);

  renderCompareCharts(coA, coB, portsA, portsB);

  updateComparePageMap(coA, coB);

  showToast('success', 'Comparison Ready', 'Comparative metrics and corridor map plotted successfully.');
}

function renderCompareDetailCard(containerId, c, ports, themeColor) {
  const el = document.getElementById(containerId);
  if (!el) return;

  const rateObj = STATE.currencies.latest_rates || {};
  const rate = c.currency === 'USD' ? 1.0000 : (rateObj[c.currency] ? parseFloat(rateObj[c.currency]) : null);
  const expImp = getCountryExportImport(c.name);
  const capital = getCountryCapital(c.name);
  const weatherScore = 100 - (STATE.risks.find(r => r.country_id === c.id)?.weather_score || 0);

  el.innerHTML = `
    <div class="d-flex align-items-center gap-3 mb-3">
      <img src="https://flagcdn.com/w40/${c.code.toLowerCase()}.png" width="40" style="border-radius:4px; border:1px solid var(--border-color); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
      <div>
        <h5 class="fw-bold mb-0 text-primary">${c.name}</h5>
        <span class="text-muted small">${c.region || 'Global'}</span>
      </div>
    </div>
    <div class="row g-3 small">
      <div class="col-6">🏛️ Capital: <strong>${capital}</strong></div>
      <div class="col-6">👥 Population: <strong>${c.population ? (parseFloat(c.population)/1e6).toFixed(1) + 'M' : 'N/A'}</strong></div>
      <div class="col-6">💰 GDP: <strong>$${c.gdp ? (parseFloat(c.gdp)/1e12).toFixed(2) + 'T' : 'N/A'}</strong></div>
      <div class="col-6">⚓ Ports Count: <strong>${ports.length}</strong></div>
      <div class="col-6">⚠️ Risk Score: <strong style="color:${riskColor(c.risk_level)};">${parseFloat(c.risk_score || 0).toFixed(1)}</strong></div>
      <div class="col-6">🌦️ Weather Safety: <strong>${weatherScore.toFixed(0)}/100</strong></div>
      <div class="col-6">💵 Exchange Rate: <strong>${rate ? rate.toFixed(4) + ' ' + c.currency : 'N/A'}</strong></div>
      <div class="col-12 mt-2 pt-2 border-top" style="border-color:var(--border-color) !important;">
        <div class="mb-1">🚢 Major Export: <strong class="text-success">${expImp.export}</strong></div>
        <div>📥 Major Import: <strong class="text-primary">${expImp.import}</strong></div>
      </div>
    </div>
  `;
}

function getCountryCapital(name) {
  const m = {
    'indonesia': 'Jakarta',
    'malaysia': 'Kuala Lumpur',
    'singapore': 'Singapore',
    'germany': 'Berlin',
    'japan': 'Tokyo',
    'united kingdom': 'London',
    'united states': 'Washington D.C.',
    'china': 'Beijing',
    'australia': 'Canberra',
    'brazil': 'Brasília',
    'canada': 'Ottawa',
    'france': 'Paris',
    'italy': 'Rome',
    'netherlands': 'Amsterdam'
  };
  return m[name.toLowerCase()] || 'N/A';
}

function getCountryExportImport(countryName) {
  const name = countryName.toLowerCase();
  if (name.includes('saudi') || name.includes('emirates') || name.includes('iraq') || name.includes('kuwait') || name.includes('russia')) {
    return { export: 'Crude Petroleum & Gas', import: 'Machinery & Vehicles' };
  }
  if (name.includes('germany') || name.includes('japan') || name.includes('korea')) {
    return { export: 'Automobiles & High-Tech', import: 'Fossil Fuels & Food' };
  }
  if (name.includes('china') || name.includes('taiwan')) {
    return { export: 'Electronics & Semiconductors', import: 'Iron Ore & Oil' };
  }
  if (name.includes('brazil') || name.includes('argentina') || name.includes('australia')) {
    return { export: 'Soybeans, Iron Ore & Coal', import: 'Refined Petroleum' };
  }
  return { export: 'Industrial Machinery & Services', import: 'Consumer Goods & Energy' };
}

function renderCompareWinners(coA, coB, portsA, portsB) {
  const badgesContainer = document.getElementById('compareWinnerSummaryBadges');
  if (!badgesContainer) return;

  const gdpA = parseFloat(coA.gdp || 0);
  const gdpB = parseFloat(coB.gdp || 0);
  const economyWinner = gdpA > gdpB ? coA.name : coB.name;

  const riskA = parseFloat(coA.risk_score || 0);
  const riskB = parseFloat(coB.risk_score || 0);
  const riskWinner = riskA < riskB ? coA.name : coB.name;

  const portsWinner = portsA.length > portsB.length ? coA.name : (portsB.length > portsA.length ? coB.name : 'Equal');

  const rateObj = STATE.currencies.latest_rates || {};
  const rateA = coA.currency === 'USD' ? 1.0 : (rateObj[coA.currency] ? parseFloat(rateObj[coA.currency]) : 1.0);
  const rateB = coB.currency === 'USD' ? 1.0 : (rateObj[coB.currency] ? parseFloat(rateObj[coB.currency]) : 1.0);
  const exchangeWinner = rateA < rateB ? coA.name : coB.name;

  badgesContainer.innerHTML = `
    <span class="badge text-success border border-success" style="background:rgba(16,185,129,.1); padding: 6px 12px; border-radius: 20px;">✔ Better Economy : ${economyWinner}</span>
    <span class="badge text-primary border border-primary" style="background:rgba(59,130,246,.1); padding: 6px 12px; border-radius: 20px;">✔ Lower Risk : ${riskWinner}</span>
    <span class="badge text-purple border border-purple" style="background:rgba(139,92,246,.1); padding: 6px 12px; border-radius: 20px; color:#8b5cf6;">✔ More Ports : ${portsWinner}</span>
    <span class="badge text-warning border border-warning" style="background:rgba(245,158,11,.1); padding: 6px 12px; border-radius: 20px;">✔ Better Exchange Rate : ${exchangeWinner}</span>
  `;
}

function renderCompareTable(coA, coB, portsA, portsB) {
  document.getElementById('compareTableHeadA').textContent = coA.name;
  document.getElementById('compareTableHeadB').textContent = coB.name;

  const rateObj = STATE.currencies.latest_rates || {};
  const getRate = c => c.currency === 'USD' ? 1.0000 : (rateObj[c.currency] ? parseFloat(rateObj[c.currency]) : null);

  const getPortsOf = (ports, type) => ports.filter(p => p.harbor_type.toLowerCase().includes(type)).length;

  const tableRows = [
    { label: 'Population', valA: coA.population ? parseFloat(coA.population)/1e6 : 0, valB: coB.population ? parseFloat(coB.population)/1e6 : 0, displayA: coA.population ? (parseFloat(coA.population)/1e6).toFixed(1) + 'M' : 'N/A', displayB: coB.population ? (parseFloat(coB.population)/1e6).toFixed(1) + 'M' : 'N/A', better: 'A > B' },
    { label: 'GDP', valA: coA.gdp ? parseFloat(coA.gdp)/1e12 : 0, valB: coB.gdp ? parseFloat(coB.gdp)/1e12 : 0, displayA: coA.gdp ? '$' + (parseFloat(coA.gdp)/1e12).toFixed(2) + 'T' : 'N/A', displayB: coB.gdp ? '$' + (parseFloat(coB.gdp)/1e12).toFixed(2) + 'T' : 'N/A', better: 'A > B' },
    { label: 'Ports', valA: portsA.length, valB: portsB.length, displayA: portsA.length, displayB: portsB.length, better: 'A > B' },
    { label: 'Major Ports', valA: getPortsOf(portsA, 'major'), valB: getPortsOf(portsB, 'major'), displayA: getPortsOf(portsA, 'major'), displayB: getPortsOf(portsB, 'major'), better: 'A > B' },
    { label: 'Container Ports', valA: getPortsOf(portsA, 'container'), valB: getPortsOf(portsB, 'container'), displayA: getPortsOf(portsA, 'container'), displayB: getPortsOf(portsB, 'container'), better: 'A > B' },
    { label: 'Oil Ports', valA: getPortsOf(portsA, 'oil'), valB: getPortsOf(portsB, 'oil'), displayA: getPortsOf(portsA, 'oil'), displayB: getPortsOf(portsB, 'oil'), better: 'A > B' },
    { label: 'Risk', valA: parseFloat(coA.risk_score || 0), valB: parseFloat(coB.risk_score || 0), displayA: parseFloat(coA.risk_score || 0).toFixed(1), displayB: parseFloat(coB.risk_score || 0).toFixed(1), better: 'A < B' },
    { label: 'Trade Volume', valA: portsA.length * 15, valB: portsB.length * 15, displayA: (portsA.length * 15) + ' MT', displayB: (portsB.length * 15) + ' MT', better: 'A > B' },
    { label: 'Exchange Rate', valA: getRate(coA) || 0, valB: getRate(coB) || 0, displayA: getRate(coA) ? (getRate(coA)).toFixed(4) : 'N/A', displayB: getRate(coB) ? (getRate(coB)).toFixed(4) : 'N/A', better: 'stable' },
    { label: 'Weather Risk', valA: STATE.risks.find(r => r.country_id === coA.id)?.weather_score || 0, valB: STATE.risks.find(r => r.country_id === coB.id)?.weather_score || 0, displayA: (STATE.risks.find(r => r.country_id === coA.id)?.weather_score || 0).toFixed(0), displayB: (STATE.risks.find(r => r.country_id === coB.id)?.weather_score || 0).toFixed(0), better: 'A < B' }
  ];

  const tbody = document.querySelector('#comparePerformanceTable tbody');
  tbody.innerHTML = tableRows.map(row => {
    let winner = 'N/A';
    if (row.better === 'A > B') {
      winner = row.valA > row.valB ? coA.name : (row.valB > row.valA ? coB.name : 'Equal');
    } else if (row.better === 'A < B') {
      winner = row.valA < row.valB ? coA.name : (row.valB < row.valA ? coB.name : 'Equal');
    } else {
      winner = 'Comparative';
    }

    return `
      <tr>
        <td class="fw-bold small">${row.label}</td>
        <td class="small">${row.displayA}</td>
        <td class="small">${row.displayB}</td>
        <td class="text-end small"><span class="badge bg-light text-dark border">${winner}</span></td>
      </tr>
    `;
  }).join('');
}

function generateSmartInsights(coA, coB, portsA, portsB) {
  const insights = [];
  
  if (portsA.length > portsB.length) {
    insights.push(`🚢 <strong>${coA.name}</strong> has a larger maritime infrastructure, offering superior logistics capacity with <strong>${portsA.length} ports</strong> vs ${coB.name}'s ${portsB.length} ports.`);
  } else if (portsB.length > portsA.length) {
    insights.push(`🚢 <strong>${coB.name}</strong> has a larger maritime infrastructure, offering superior logistics capacity with <strong>${portsB.length} ports</strong> vs ${coA.name}'s ${portsA.length} ports.`);
  }
  
  const riskA = parseFloat(coA.risk_score || 0);
  const riskB = parseFloat(coB.risk_score || 0);
  if (riskA < riskB) {
    insights.push(`🛡️ <strong>${coA.name}</strong> has lower logistics risk with a risk score of <strong>${riskA.toFixed(1)}</strong> vs ${coB.name}'s ${riskB.toFixed(1)}.`);
  } else if (riskB < riskA) {
    insights.push(`🛡️ <strong>${coB.name}</strong> has lower logistics risk with a risk score of <strong>${riskB.toFixed(1)}</strong> vs ${coA.name}'s ${riskA.toFixed(1)}.`);
  }
  
  const getPortsOf = (ports, type) => ports.filter(p => p.harbor_type.toLowerCase().includes(type)).length;
  const majorA = getPortsOf(portsA, 'major');
  const majorB = getPortsOf(portsB, 'major');
  if (majorA > majorB) {
    insights.push(`🌐 <strong>${coA.name}</strong> owns more international ports and global transit gateways.`);
  } else if (majorB > majorA) {
    insights.push(`🌐 <strong>${coB.name}</strong> owns more international ports and global transit gateways.`);
  }

  // General trade efficiency
  if (portsA.length > portsB.length) {
    insights.push(`📈 <strong>${coA.name}</strong> has stronger trade efficiency and output due to extensive corridor connections.`);
  } else {
    insights.push(`📈 <strong>${coB.name}</strong> has stronger trade efficiency and output due to localized supply chain nodes.`);
  }
  
  return insights.map(i => `<div class="p-2 mb-2 rounded border" style="background:var(--bg-card); border-color:var(--border-color) !important; font-size: .75rem;">${i}</div>`).join('');
}

function renderCompareCharts(coA, coB, portsA, portsB) {
  const maxPop = Math.max(parseFloat(coA.population || 0), parseFloat(coB.population || 0), 1);
  const maxGdp = Math.max(parseFloat(coA.gdp || 0), parseFloat(coB.gdp || 0), 1);
  const maxPorts = Math.max(portsA.length, portsB.length, 1);

  const radarA = [
    (parseFloat(coA.population || 0) / maxPop) * 100,
    (parseFloat(coA.gdp || 0) / maxGdp) * 100,
    (portsA.length / maxPorts) * 100,
    100 - parseFloat(coA.risk_score || 0),
    85,
    100 - (parseFloat(coA.inflation || 0) * 5)
  ];

  const radarB = [
    (parseFloat(coB.population || 0) / maxPop) * 100,
    (parseFloat(coB.gdp || 0) / maxGdp) * 100,
    (portsB.length / maxPorts) * 100,
    100 - parseFloat(coB.risk_score || 0),
    75,
    100 - (parseFloat(coB.inflation || 0) * 5)
  ];

  // 1. Radar
  if (compareCharts.radar) compareCharts.radar.destroy();
  compareCharts.radar = new Chart(document.getElementById('compareRadarChart').getContext('2d'), {
    type: 'radar',
    data: {
      labels: ['Population', 'GDP', 'Ports', 'Risk Index', 'Trade', 'Economy'],
      datasets: [
        { label: coA.name, data: radarA, backgroundColor: 'rgba(37,99,235,0.15)', borderColor: '#2563eb', borderWidth: 2, pointBackgroundColor: '#2563eb' },
        { label: coB.name, data: radarB, backgroundColor: 'rgba(236,72,153,0.15)', borderColor: '#ec4899', borderWidth: 2, pointBackgroundColor: '#ec4899' }
      ]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { r: { beginAtZero: true, max: 100 } } }
  });

  const getPortsOf = (ports, type) => ports.filter(p => p.harbor_type.toLowerCase().includes(type)).length;

  // 2. Bar
  if (compareCharts.bar) compareCharts.bar.destroy();
  compareCharts.bar = new Chart(document.getElementById('compareBarChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: ['Total Ports', 'Major Ports', 'Container', 'Oil Terminals'],
      datasets: [
        { label: coA.name, data: [portsA.length, getPortsOf(portsA, 'major'), getPortsOf(portsA, 'container'), getPortsOf(portsA, 'oil')], backgroundColor: '#2563eb' },
        { label: coB.name, data: [portsB.length, getPortsOf(portsB, 'major'), getPortsOf(portsB, 'container'), getPortsOf(portsB, 'oil')], backgroundColor: '#ec4899' }
      ]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
  });

  // 3. Gauge
  if (compareCharts.gauge) compareCharts.gauge.destroy();
  compareCharts.gauge = new Chart(document.getElementById('compareGaugeChart').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: [coA.name + ' Risk', coB.name + ' Risk', 'Safety Margin'],
      datasets: [{
        data: [parseFloat(coA.risk_score || 0), parseFloat(coB.risk_score || 0), Math.max(100 - parseFloat(coA.risk_score || 0) - parseFloat(coB.risk_score || 0), 10)],
        backgroundColor: ['#2563eb', '#ec4899', 'rgba(0,0,0,0.05)'],
        borderWidth: 1
      }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
  });

  // 4. Trade Comparison
  if (compareCharts.trade) compareCharts.trade.destroy();
  compareCharts.trade = new Chart(document.getElementById('compareTradeChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: ['Export Index', 'Import Index', 'Freight Efficiency', 'Customs Speed'],
      datasets: [
        { label: coA.name, data: [80, 75, 85, 70], backgroundColor: '#2563eb' },
        { label: coB.name, data: [70, 85, 75, 80], backgroundColor: '#ec4899' }
      ]
    },
    options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
  });

  // 5. Port Distribution
  const distA = [getPortsOf(portsA, 'container'), getPortsOf(portsA, 'oil'), Math.max(portsA.length - getPortsOf(portsA, 'container') - getPortsOf(portsA, 'oil'), 0)];
  const distB = [getPortsOf(portsB, 'container'), getPortsOf(portsB, 'oil'), Math.max(portsB.length - getPortsOf(portsB, 'container') - getPortsOf(portsB, 'oil'), 0)];

  if (compareCharts.dist) compareCharts.dist.destroy();
  compareCharts.dist = new Chart(document.getElementById('comparePortDistChart').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: ['Container', 'Oil', 'General Cargo'],
      datasets: [
        { label: coA.name, data: distA, backgroundColor: ['#6366f1', '#f59e0b', '#3b82f6'] },
        { label: coB.name, data: distB, backgroundColor: ['#818cf8', '#fbbf24', '#60a5fa'] }
      ]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });
}

function initComparePageMap() {
  if (compareMap) return;
  
  compareMap = L.map('compareCountriesMap', {
    center: [20, 0],
    zoom: 2,
    zoomControl: true
  });
  
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
  }).addTo(compareMap);
  
  compareBordersLayer = L.layerGroup().addTo(compareMap);
  comparePortsLayer = L.layerGroup().addTo(compareMap);
}

function updateComparePageMap(countryA, countryB) {
  initComparePageMap();
  
  compareBordersLayer.clearLayers();
  comparePortsLayer.clearLayers();
  
  const markers = [];
  
  [countryA, countryB].forEach((c, index) => {
    if (!c || c.latitude == null || c.longitude == null) return;
    
    const color = index === 0 ? '#3b82f6' : '#ec4899';
    const border = L.circle([parseFloat(c.latitude), parseFloat(c.longitude)], {
      radius: 300000,
      color: color,
      fillColor: color,
      fillOpacity: 0.1,
      weight: 2
    }).addTo(compareBordersLayer);
    
    const label = L.marker([parseFloat(c.latitude), parseFloat(c.longitude)], {
      icon: L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background:${color};color:#fff;padding:4px 8px;border-radius:4px;font-size:10px;font-weight:bold;white-space:nowrap;box-shadow:0 2px 5px rgba(0,0,0,0.2);">${c.name}</div>`,
        iconAnchor: [30, 10]
      })
    }).addTo(compareBordersLayer);
    
    markers.push(border);
    
    const countryPorts = PORTS.filter(p => p.country_name === c.name || p.country?.name === c.name);
    countryPorts.forEach(port => {
      if (port.latitude == null || port.longitude == null) return;
      
      const portMarker = L.circleMarker([parseFloat(port.latitude), parseFloat(port.longitude)], {
        radius: 6,
        color: '#fff',
        fillColor: riskColor(port.risk_level),
        fillOpacity: 0.9,
        weight: 1.5
      }).addTo(comparePortsLayer);
      
      portMarker.bindPopup(`
        <b>${port.name}</b><br>
        Type: ${port.harbor_type || 'Port'}<br>
        Risk: ${port.risk_level || 'Low'}
      `);
      
      markers.push(portMarker);
    });
  });

  // Draw trade route line between center coordinates (dashed path)
  if (countryA.latitude != null && countryA.longitude != null && countryB.latitude != null && countryB.longitude != null) {
    const routeCoords = [
      [parseFloat(countryA.latitude), parseFloat(countryA.longitude)],
      [parseFloat(countryB.latitude), parseFloat(countryB.longitude)]
    ];
    L.polyline(routeCoords, {
      color: '#f59e0b',
      dashArray: '8, 8',
      weight: 3,
      opacity: 0.8
    }).addTo(compareBordersLayer).bindPopup("Main Maritime Trade Corridor");
  }
  
  if (markers.length > 0) {
    const group = L.featureGroup(markers);
    compareMap.fitBounds(group.getBounds().pad(0.1));
  }
}

function exportComparePage(format) {
  const coA = compareCountriesSelected.A;
  const coB = compareCountriesSelected.B;
  if (!coA || !coB) return;

  if (format === 'csv' || format === 'excel') {
    let csv = 'Indicator,' + coA.name + ',' + coB.name + ',Winner\n';
    const rows = document.querySelectorAll('#comparePerformanceTable tbody tr');
    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      const rowData = Array.from(cells).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`);
      csv += rowData.join(',') + '\n';
    });

    const type = format === 'excel' ? 'application/vnd.ms-excel' : 'text/csv';
    const ext = format === 'excel' ? 'xls' : 'csv';
    const blob = new Blob([csv], { type: type + ';charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${coA.name}_vs_${coB.name}_comparison.${ext}`;
    a.click();
    showToast('success', 'Export Complete', `File downloaded successfully.`);
  } else if (format === 'print' || format === 'pdf') {
    const w = window.open('', '_blank');
    const tableHtml = document.getElementById('comparePrintableArea').innerHTML;
    w.document.write(`
      <html>
        <head>
          <title>${coA.name} vs ${coB.name} Comparison Report</title>
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
          <style>
            body { font-family: sans-serif; padding: 40px; }
            .card { border: 1px solid #ddd; padding: 20px; border-radius: 12px; margin-bottom: 20px; }
            .badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; }
            .row { display: flex; flex-wrap: wrap; margin-right: -15px; margin-left: -15px; }
            .col-md-6 { flex: 0 0 50%; max-width: 50%; padding: 15px; }
            .col-md-7 { flex: 0 0 58%; max-width: 58%; padding: 15px; }
            .col-md-5 { flex: 0 0 41%; max-width: 41%; padding: 15px; }
            #compareCountriesMap { display: none; }
          </style>
        </head>
        <body>
          <h2 class="mb-2">Enterprise Supply Chain Intelligence Report</h2>
          <h4 class="text-muted mb-4">Comparative Audit: ${coA.name} vs ${coB.name}</h4>
          ${tableHtml}
        </body>
      </html>
    `);
    w.document.close();
    setTimeout(() => { w.print(); }, 500);
  }
}

function initStatCards() {
  document.querySelectorAll('.stat-card').forEach(card => {
    // Add arrow icon to top-right if not present
    if (!card.querySelector('.stat-card-arrow')) {
      const arrow = document.createElement('div');
      arrow.className = 'stat-card-arrow';
      arrow.innerHTML = '<i class="bi bi-arrow-up-right"></i>';
      card.appendChild(arrow);
    }
    
    // Add "View Details →" to bottom-right if not present
    if (!card.querySelector('.stat-card-view')) {
      const viewDetails = document.createElement('div');
      viewDetails.className = 'stat-card-view';
      viewDetails.innerHTML = 'View Details →';
      card.appendChild(viewDetails);
    }

    card.addEventListener('click', function(e) {
      // Ripple effect
      const rect = card.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;

      const ripple = document.createElement('span');
      ripple.className = 'ripple';
      ripple.style.width = ripple.style.height = size + 'px';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';

      card.appendChild(ripple);
      setTimeout(() => ripple.remove(), 500);

      // Page mapping
      const targetPage = card.dataset.targetPage;
      if (targetPage) {
        showPage(targetPage);
      }
    });
  });
}

/* ═══════════════════════════════════════════════════════════
   BOOT
   ═══════════════════════════════════════════════════════════ */
(async function boot() {
  chartDefaults();
  startClock();
  startAutoRefresh();
  initStatCards();
  initComparisonSearch();

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
