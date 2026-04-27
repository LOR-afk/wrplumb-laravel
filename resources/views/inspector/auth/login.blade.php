<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspector Login - WRPlumb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background:linear-gradient(180deg,#f8fafc 0%,#eef4ff 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
        .login-card{width:100%;max-width:480px;border:none;border-radius:22px;box-shadow:0 18px 45px rgba(15,23,42,.12);overflow:hidden;}
        .login-card .card-body{padding:32px;}
        .login-btn{background:linear-gradient(90deg,#2563eb,#1d4ed8);border:none;border-radius:12px;padding:12px 18px;font-weight:700;}
        .captcha-box{background:#f8fbfe;border:1px solid #dbe7f3;border-radius:12px;padding:12px 14px;font-weight:700;color:#1f2937;}
    </style>
</head>
<body>
<div class="card login-card">
    <div class="card-body">
        <div class="text-center mb-4">
            <h3 class="mb-1">Inspector Login</h3>
            <p class="text-muted mb-0">Access the WRPlumb inspector panel</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">Please check your login details.</div>
        @endif

        <form method="POST" action="{{ route('inspector.login.attempt') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" name="login" class="form-control" value="{{ old('login') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-2">
                <label class="form-label">Simple Captcha</label>
                <div class="captcha-box">{{ session('inspector_login_captcha_question', $captchaQuestion ?? '1 + 1 = ?') }}</div>
            </div>

            <div class="mb-4">
                <input type="number" name="captcha" class="form-control" placeholder="Enter captcha answer" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 login-btn">
                Login as Inspector
            </button>
        </form>
    </div>
</div>
</body>
</html>