<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Login - WRPlumb</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #f8fafc 0%, #eef4ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .login-card {
            width: 100%;
            max-width: 480px;
            border: none;
            border-radius: 22px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }
        .login-card .card-body {
            padding: 32px;
        }
        .login-logo {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 16px;
            border: 1px solid #dbe7f3;
            background: #fff;
            margin-bottom: 14px;
        }
        .login-btn {
            background: linear-gradient(90deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 12px;
            padding: 12px 18px;
            font-weight: 700;
        }
        .captcha-box {
            background: #f8fbfe;
            border: 1px solid #dbe7f3;
            border-radius: 12px;
            padding: 12px 14px;
            font-weight: 700;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo" class="login-logo">
                <h3 class="mb-1">HR Login</h3>
                <p class="text-muted mb-0">Access the WRPlumb HR support panel</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    Please check your login details.
                </div>
            @endif

            <form method="POST" action="{{ route('hr.login.attempt') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Username or Email</label>
                    <input type="text" name="login" class="form-control" value="{{ old('login') }}" required>
                    @error('login')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label class="form-label">Simple Captcha</label>
                    <div class="captcha-box">{{ session('hr_login_captcha_question', $captchaQuestion ?? '1 + 1 = ?') }}</div>
                </div>

                <div class="mb-4">
                    <input type="number" name="captcha" class="form-control" placeholder="Enter captcha answer" required>
                    @error('captcha')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 login-btn">
                    Login as HR
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('home') }}" class="text-decoration-none">← Back to Homepage</a>
            </div>
        </div>
    </div>
</body>
</html>