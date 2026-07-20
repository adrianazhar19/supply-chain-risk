<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Admin Dashboard') — SCRI Panel</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
  
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
  
  @vite([
    'resources/css/app.css',
    'resources/css/admin.css',
    'resources/js/app.js'
])
  @yield('styles')
</head>
<body>

<!-- ═══ TOAST CONTAINER ═══ -->
<div class="admin-toast-container" id="toastContainer"></div>

<!-- ═══ SIDEBAR (Dark Navy / Black) ═══ -->
<aside class="admin-sidebar" id="adminSidebar">

  <!-- Sidebar Brand Header -->
  <div class="sidebar-brand">
    <div class="brand-icon">
      <i class="fa-solid fa-shield-halved"></i>
    </div>
    <div class="brand-text">
      SCRI Admin
      <span>Enterprise Risk Portal</span>
    </div>
  </div>

  <!-- Sidebar Navigation -->
  <nav class="sidebar-nav">
    
    <div class="nav-section-title">CORE</div>

    <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <div class="s-icon"><i class="fa-solid fa-chart-line"></i></div>
      <span class="s-label">Dashboard</span>
    </a>

    <div class="nav-section-title">MANAGEMENT</div>

    <a href="{{ route('admin.users') }}" class="sidebar-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
      <div class="s-icon"><i class="fa-solid fa-users"></i></div>
      <span class="s-label">User Management</span>
      <span class="s-badge">{{ \App\Models\User::count() }}</span>
    </a>

    <a href="{{ route('admin.countries') }}" class="sidebar-item {{ request()->routeIs('admin.countries') ? 'active' : '' }}">
      <div class="s-icon"><i class="fa-solid fa-globe"></i></div>
      <span class="s-label">Countries Management</span>
    </a>

    <a href="{{ route('admin.ports') }}" class="sidebar-item {{ request()->routeIs('admin.ports') ? 'active' : '' }}">
      <div class="s-icon"><i class="fa-solid fa-anchor"></i></div>
      <span class="s-label">Ports Management</span>
    </a>

    <a href="{{ route('admin.news') }}" class="sidebar-item {{ request()->routeIs('admin.news') ? 'active' : '' }}">
      <div class="s-icon"><i class="fa-solid fa-newspaper"></i></div>
      <span class="s-label">News Management</span>
    </a>

    <a href="{{ route('admin.rates') }}" class="sidebar-item {{ request()->routeIs('admin.rates') ? 'active' : '' }}">
      <div class="s-icon"><i class="fa-solid fa-dollar-sign"></i></div>
      <span class="s-label">Exchange Rates</span>
    </a>

    <a href="{{ route('admin.weather') }}" class="sidebar-item {{ request()->routeIs('admin.weather') ? 'active' : '' }}">
      <div class="s-icon"><i class="fa-solid fa-cloud-sun"></i></div>
      <span class="s-label">Weather Management</span>
    </a>

    <a href="{{ route('admin.risks') }}" class="sidebar-item {{ request()->routeIs('admin.risks') ? 'active' : '' }}">
      <div class="s-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <span class="s-label">Risk Analytics</span>
    </a>

    <a href="{{ route('admin.reports') }}" class="sidebar-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
      <div class="s-icon"><i class="fa-solid fa-file-contract"></i></div>
      <span class="s-label">Reports</span>
    </a>

    <a href="{{ route('admin.settings') }}" class="sidebar-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
      <div class="s-icon"><i class="fa-solid fa-cog"></i></div>
      <span class="s-label">Settings</span>
    </a>

    <!-- Simple separate form for log out in sidebar menu -->
    <form action="{{ route('admin.logout') }}" method="POST" class="m-0 mt-3">
      @csrf
      <button type="submit" class="sidebar-item w-100 border-0 text-start" style="background:transparent; cursor:pointer;">
        <div class="s-icon" style="color:var(--danger);"><i class="fa-solid fa-sign-out-alt"></i></div>
        <span class="s-label" style="color:var(--danger);">Sign Out</span>
      </button>
    </form>

  </nav>

  <!-- Sidebar User Profile Footer -->
  <div class="sidebar-footer">
    <div class="admin-avatar">
      <div class="avatar-circle">
        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
      </div>
      <div class="avatar-info">
        <div class="a-name">{{ Auth::user()->name }}</div>
        <div class="a-role">Administrator</div>
      </div>
    </div>
  </div>

</aside>

<!-- ═══ CONTENT WRAPPER ═══ -->
<div class="admin-wrapper" id="adminWrapper">

  <!-- ═══ NAVBAR (Top Header) ═══ -->
  <nav class="admin-navbar" id="adminNavbar">

    <!-- Toggle Sidebar Button -->
    <button class="navbar-btn me-3" id="sidebarToggle" title="Toggle Sidebar">
      <i class="fa-solid fa-bars"></i>
    </button>

    <!-- Header Title/Sub -->
    <div class="d-none d-md-flex flex-column me-auto">
      <span style="font-size:11px; font-weight:600; text-transform:uppercase; color:var(--text-muted); letter-spacing:0.05em;">Admin Center</span>
      <span style="font-size:15px; font-weight:700; color:var(--primary-dark);">Supply Chain Risk Intelligence</span>
    </div>

    <!-- Dropdowns & Tools -->
    <div class="d-flex align-items-center gap-3">

      <!-- Database Status Indicator -->
      <div class="d-none d-lg-flex align-items-center gap-2 px-3 py-1.5 rounded" style="background:var(--success-soft); border:1px solid rgba(16,185,129,0.2);">
        <span style="width:7px; height:7px; border-radius:50%; background:var(--success); display:inline-block;"></span>
        <span style="font-size:11px; font-weight:700; color:var(--success);">DB CONNECTED</span>
      </div>

      <!-- User Info Profile Dropdown -->
      <div class="dropdown">
        <button class="d-flex align-items-center gap-2 border-0 bg-transparent" style="cursor:pointer;" data-bs-toggle="dropdown">
          <div class="avatar-circle" style="width:34px; height:34px;">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
          </div>
          <div class="text-start d-none d-md-block">
            <div style="font-size:13px; font-weight:700; color:var(--text); line-height:1.2;">{{ Auth::user()->name }}</div>
            <div style="font-size:10px; color:var(--text-muted);">{{ Auth::user()->email }}</div>
          </div>
          <i class="fa-solid fa-angle-down ms-1" style="font-size:11px; color:var(--text-muted);"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width:200px; border-radius:var(--radius-lg); padding:8px;">
          <li class="px-3 py-2 mb-1" style="border-bottom:1px solid var(--border);">
            <div style="font-weight:700; color:var(--text); font-size:13px;">{{ Auth::user()->name }}</div>
            <div style="font-size:11px; color:var(--text-muted);">{{ Auth::user()->email }}</div>
          </li>
          <li>
            <a class="dropdown-item py-2" href="{{ route('admin.settings') }}">
              <i class="fa-solid fa-sliders me-2 text-muted" style="font-size:12px;"></i> Settings
            </a>
          </li>
          <li>
            <a class="dropdown-item py-2" href="{{ route('admin.logs') }}">
              <i class="fa-solid fa-list-check me-2 text-muted" style="font-size:12px;"></i> Activity Logs
            </a>
          </li>
          <li class="mt-2 pt-2" style="border-top:1px solid var(--border);">
            <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
              @csrf
              <button type="submit" class="dropdown-item py-2 text-danger border-0 w-100 text-start bg-transparent" style="font-weight:600;">
                <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Log Out
              </button>
            </form>
          </li>
        </ul>
      </div>

    </div>
  </nav>

  <!-- ═══ MAIN CONTENT AREA ═══ -->
  <main class="admin-main">
    @yield('content')
  </main>

</div>

<!-- ═══ FOOTER SCRIPTS ═══ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
// Sidebar toggle interaction
const sidebar = document.getElementById('adminSidebar');
const wrapper = document.getElementById('adminWrapper');

document.getElementById('sidebarToggle').addEventListener('click', () => {
  sidebar.classList.toggle('collapsed');
  wrapper.classList.toggle('sidebar-collapsed');
});

// Toast notification helper
function showToast(type, message) {
  const palette = {
    success: { border:'#10b981', icon:'fa-circle-check', color:'#10b981', bg:'#ecfdf5' },
    error:   { border:'#ef4444', icon:'fa-circle-xmark', color:'#ef4444', bg:'#fef2f2' },
    info:    { border:'#0ea5e9', icon:'fa-circle-info',  color:'#0ea5e9', bg:'#f0f9ff' },
    warning: { border:'#f59e0b', icon:'fa-triangle-exclamation', color:'#f59e0b', bg:'#fffbeb' },
  };
  const c = palette[type] || palette.info;
  const id = 'toast_' + Date.now();
  const el = document.createElement('div');
  el.className = 'admin-toast';
  el.id = id;
  el.style.cssText = `background:var(--surface); border:1px solid var(--border); border-left:4px solid ${c.border}; display:flex; align-items:center; gap:12px; padding:12px 16px; border-radius:var(--radius); box-shadow:var(--shadow-md); min-width:280px; max-width:380px; margin-bottom:10px;`;
  el.innerHTML = `
    <i class="fa-solid ${c.icon}" style="color:${c.color}; font-size:18px;"></i>
    <div style="flex:1; font-size:13px; font-weight:500; color:var(--text);">${message}</div>
    <button onclick="document.getElementById('${id}').remove()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding:0; line-height:1;"><i class="fa-solid fa-xmark"></i></button>
  `;
  document.getElementById('toastContainer').appendChild(el);
  setTimeout(() => el?.remove(), 6000);
}

// Animate values
function animateCounter(el) {
  const target = parseInt(el.dataset.counter || el.textContent);
  if (isNaN(target)) return;
  let current = 0;
  const step = Math.max(1, Math.ceil(target / 45));
  const timer = setInterval(() => {
    current = Math.min(current + step, target);
    el.textContent = current.toLocaleString();
    if (current >= target) clearInterval(timer);
  }, 16);
}
document.querySelectorAll('[data-counter]').forEach(animateCounter);

// Mobile Drawer Overlay
if (window.innerWidth <= 992) {
  const overlay = document.createElement('div');
  overlay.id = 'sidebarOverlay';
  overlay.style.cssText = 'position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:999; display:none; backdrop-filter:blur(2px);';
  document.body.appendChild(overlay);
  
  document.getElementById('sidebarToggle').addEventListener('click', () => {
    sidebar.classList.toggle('mobile-open');
    overlay.style.display = sidebar.classList.contains('mobile-open') ? 'block' : 'none';
  });
  
  overlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    overlay.style.display = 'none';
  });
}
</script>

@yield('scripts')
</body>
</html>
