<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Authentication — SCRI Command Center</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

  <style>
    :root {
      --primary: #2563eb;
      --primary-dark: #1e3a8a;
      --bg: #f8fafc;
      --text: #0f172a;
      --text-muted: #64748b;
      --border: #e2e8f0;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      margin: 0;
      display: flex;
    }

    .login-container {
      display: flex;
      width: 100vw;
      min-height: 100vh;
    }

    /* ─── Visual Panel (Left side on desktop) ─── */
    .visual-panel {
      flex: 1.2;
      background: linear-gradient(135deg, #0b0f19 0%, #1e293b 100%);
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 4rem;
      color: #ffffff;
      overflow: hidden;
    }

    .visual-panel::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: url('https://upload.wikimedia.org/wikipedia/commons/8/80/World_map_blank_black_white.svg');
      background-repeat: no-repeat;
      background-size: cover;
      background-position: center;
      opacity: 0.05;
      z-index: 1;
    }

    /* Decorative glowing mesh elements */
    .glow-circle {
      position: absolute;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, rgba(0,0,0,0) 70%);
      border-radius: 50%;
      pointer-events: none;
    }
    .glow-1 { top: -10%; left: -10%; }
    .glow-2 { bottom: -10%; right: -10%; }

    .visual-content {
      position: relative;
      z-index: 10;
      max-width: 580px;
    }

    .visual-tag {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: #60a5fa;
      margin-bottom: 1.5rem;
      display: inline-block;
      padding: 4px 12px;
      background: rgba(96, 165, 250, 0.1);
      border-radius: 99px;
      border: 1px solid rgba(96, 165, 250, 0.2);
    }

    .visual-title {
      font-size: 36px;
      font-weight: 800;
      line-height: 1.25;
      letter-spacing: -0.02em;
      margin-bottom: 1.5rem;
      background: linear-gradient(to right, #ffffff, #94a3b8);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .visual-desc {
      font-size: 15px;
      color: #94a3b8;
      line-height: 1.6;
      margin-bottom: 2.5rem;
    }

    /* Dynamic visual metric dashboard mimic */
    .metric-dashboard {
      background: rgba(30, 41, 59, 0.4);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 1.5rem;
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1.25rem;
    }

    .metric-item {
      display: flex;
      flex-direction: column;
    }

    .metric-label {
      font-size: 10px;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 2px;
    }

    .metric-value {
      font-size: 20px;
      font-weight: 700;
      color: #f8fafc;
      font-family: 'JetBrains Mono', monospace;
    }

    .metric-status {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      color: #10b981;
      font-weight: 600;
      margin-top: 4px;
    }

    /* ─── Login Panel (Right side on desktop) ─── */
    .login-panel {
      flex: 0.8;
      background: #ffffff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 4rem 2.5rem;
      box-shadow: -10px 0 30px rgba(0, 0, 0, 0.02);
      position: relative;
      z-index: 20;
    }

    .login-form-wrapper {
      width: 100%;
      max-width: 380px;
    }

    .logo-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 2.5rem;
    }

    .logo-box {
      width: 42px; height: 42px;
      background: var(--primary);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; color: #fff;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    .logo-title {
      font-size: 18px;
      font-weight: 800;
      color: var(--primary-dark);
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .form-title {
      font-size: 24px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 6px;
    }

    .form-subtitle {
      font-size: 13.5px;
      color: var(--text-muted);
      margin-bottom: 2rem;
    }

    /* Floating Labels Styling */
    .form-group-custom {
      position: relative;
      margin-bottom: 1.25rem;
    }

    .form-control-custom {
      width: 100%;
      padding: 12px 16px;
      background: #f8fafc;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 13.5px;
      color: var(--text);
      outline: none;
      transition: all 0.2s;
    }

    .form-control-custom:focus {
      background: #ffffff;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
    }

    .input-label-custom {
      font-size: 11px;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.04em;
      margin-bottom: 6px;
      display: block;
    }

    /* Password Toggle Wrapper */
    .password-input-wrapper {
      position: relative;
    }

    .password-toggle-btn {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      font-size: 14px;
      padding: 0;
    }
    .password-toggle-btn:hover {
      color: var(--text);
    }

    .btn-submit {
      width: 100%;
      background: var(--primary);
      border: 1px solid var(--primary);
      color: #ffffff;
      padding: 12px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.2s;
      margin-top: 1rem;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }

    .btn-submit:hover {
      background: var(--primary-hover);
      border-color: var(--primary-hover);
      transform: translateY(-1px);
      box-shadow: 0 6px 15px rgba(37, 99, 235, 0.25);
    }

    /* Custom Checkbox */
    .checkbox-custom {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: var(--text-muted);
      cursor: pointer;
      user-select: none;
    }
    .checkbox-custom input {
      width: 16px; height: 16px;
      border-radius: 4px;
      border: 1px solid var(--border);
      cursor: pointer;
    }

    /* Visual Alert messages */
    .error-alert-wrapper {
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 8px;
      padding: 12px 16px;
      margin-bottom: 1.5rem;
      color: #b91c1c;
      font-size: 12.5px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* ─── Responsive Adjustments ─── */
    @media (max-width: 992px) {
      .visual-panel { display: none; }
      .login-panel { flex: 1; padding: 2.5rem 1.5rem; background: var(--bg); }
      .login-form-wrapper {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 2.5rem 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
      }
    }
  </style>
</head>
<body>

<div class="login-container">

  <!-- Left Visual Side -->
  <div class="visual-panel">
    <div class="glow-circle glow-1"></div>
    <div class="glow-circle glow-2"></div>
    
    <div class="visual-content">
      <span class="visual-tag">Enterprise Command Center</span>
      <h1 class="visual-title">Supply Chain Risk Monitoring Portal</h1>
      <p class="visual-desc">Access key logistics datasets, monitor vessel routes, review real-time sentiment news indices, and assess global operations from a single workspace.</p>

      <div class="metric-dashboard">
        <div class="metric-item">
          <span class="metric-label">System State</span>
          <span class="metric-value">99.98%</span>
          <div class="metric-status">
            <span style="width:6px;height:6px;border-radius:50%;background:#10b981;display:inline-block;"></span>
            Operational
          </div>
        </div>
        <div class="metric-item">
          <span class="metric-label">Secure Access</span>
          <span class="metric-value">AES-256</span>
          <div class="metric-status" style="color:#3b82f6;">
            <i class="fa-solid fa-lock" style="font-size:9px;"></i>
            Encrypted
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Login Form Side -->
  <div class="login-panel">
    <div class="login-form-wrapper">

      <!-- Logo Header -->
      <div class="logo-header">
        <div class="logo-box">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="logo-text">
          <span class="logo-title">SCRI ADMIN</span>
        </div>
      </div>

      <h2 class="form-title">Welcome Admin</h2>
      <p class="form-subtitle">Please authorize credentials to enter the workspace.</p>

      <!-- Alert notifications for errors -->
      @if(session('toast_info'))
        <div class="error-alert-wrapper" style="background:#f0f9ff; border-color:#b3e5fc; color:#0288d1;">
          <i class="fa-solid fa-circle-info"></i>
          <span>{{ session('toast_info') }}</span>
        </div>
      @endif

      @if($errors->any())
        <div class="error-alert-wrapper">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span>{{ $errors->first() }}</span>
        </div>
      @endif

      <form action="{{ route('admin.login.submit') }}" method="POST" id="loginForm">
        @csrf

        <!-- Email field -->
        <div class="form-group-custom">
          <label for="email" class="input-label-custom">Email Address</label>
          <input type="email" name="email" id="email" class="form-control-custom" placeholder="admin@supplychain.com" required value="{{ old('email') }}" autofocus autocomplete="email">
        </div>

        <!-- Password field with Visibility Toggle -->
        <div class="form-group-custom">
          <label for="password" class="input-label-custom">Password</label>
          <div class="password-input-wrapper">
            <input type="password" name="password" id="password" class="form-control-custom" placeholder="••••••••" required autocomplete="current-password">
            <button type="button" class="password-toggle-btn" id="togglePassword" title="Show/Hide Password">
              <i class="fa-solid fa-eye-slash" id="toggleIcon"></i>
            </button>
          </div>
        </div>

        <!-- Remember Me option -->
        <div class="d-flex justify-content-between align-items-center mb-3">
          <label class="checkbox-custom">
            <input type="checkbox" name="remember" id="remember">
            <span>Remember me</span>
          </label>
        </div>

        <!-- Submit action -->
        <button type="submit" class="btn-submit">
          <i class="fa-solid fa-key me-2"></i> Access Dashboard
        </button>

      </form>
    </div>
  </div>

</div>

<!-- Footer Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Show/Hide password toggle logic
  const toggleBtn = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  const toggleIcon = document.getElementById('toggleIcon');

  toggleBtn.addEventListener('click', function () {
    const isPassword = passwordInput.getAttribute('type') === 'password';
    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
    
    // Toggle icon classes
    if (isPassword) {
      toggleIcon.className = 'fa-solid fa-eye';
    } else {
      toggleIcon.className = 'fa-solid fa-eye-slash';
    }
  });
</script>
</body>
</html>
