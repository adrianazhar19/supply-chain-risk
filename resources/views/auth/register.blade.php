<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Supply Chain Risk Intelligence</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0d9488 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }
        .blob-1 { top: -10%; left: -10%; }
        .blob-2 { bottom: -10%; right: -10%; }

        .auth-card {
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            z-index: 10;
            color: #f8fafc;
            transition: all 0.3s ease;
        }

        .auth-card:hover {
            border: 1px solid rgba(59, 130, 246, 0.3);
            box-shadow: 0 25px 50px -12px rgba(59, 130, 246, 0.15);
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
            color: #ffffff;
        }

        .form-control, .form-select {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #f8fafc;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #3b82f6;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25);
        }

        .form-select option {
            background-color: #1e293b;
            color: #f8fafc;
        }

        .form-label {
            font-weight: 500;
            color: #cbd5e1;
            margin-bottom: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 12px;
            padding: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
        }

        .text-link {
            color: #3b82f6;
            text-decoration: none;
            transition: color 0.2s;
        }

        .text-link:hover {
            color: #60a5fa;
            text-decoration: underline;
        }

        .alert-custom {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="auth-card">
        <div class="logo-icon">
            <i class="bi bi-person-plus-fill"></i>
        </div>
        
        <h3 class="text-center fw-bold mb-1">Create Account</h3>
        <p class="text-center text-muted mb-4">Register for Supply Chain Risk Intelligence Dashboard</p>

        @if ($errors->any())
            <div class="alert alert-custom mb-3 py-2 px-3">
                <ul class="mb-0 list-unstyled small">
                    @foreach ($errors->all() as $error)
                        <li><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="John Doe" required autofocus>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="••••••••" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="user" selected>Standard User</option>
                    <option value="admin">System Administrator</option>
                </select>
                <div class="form-text text-secondary mt-1 small">First user registered is granted Administrator privileges.</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
                Sign Up <i class="bi bi-person-check-fill ms-1"></i>
            </button>
            
            <div class="text-center small text-secondary">
                Already have an account? <a href="{{ route('login') }}" class="text-link">Sign in here</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
