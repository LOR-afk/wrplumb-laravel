<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - WRPlumb</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(180deg, #eef7ff 0%, #e8f3ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 500px;
            border: none;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.14);
            overflow: hidden;
            background: #fff;
        }

        .login-card .card-body {
            padding: 36px;
        }

        .login-logo {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 18px;
            border: 1px solid #dbe7f3;
            background: #fff;
            padding: 6px;
            margin-bottom: 14px;
        }

        .login-title {
            font-weight: 800;
            color: #0f172a;
        }

        .login-subtitle {
            color: #64748b;
            font-size: 0.95rem;
        }

        .form-label {
            font-weight: 600;
            color: #1e293b;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 14px;
            border: 1px solid #dbe7f3;
        }

        .form-control:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 0.15rem rgba(56, 189, 248, 0.18);
        }

        .login-btn {
            background: linear-gradient(90deg, #2563eb, #0ea5e9);
            border: none;
            border-radius: 12px;
            padding: 13px 18px;
            font-weight: 700;
        }

        .login-btn:hover {
            opacity: 0.95;
        }

        .icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #eff6ff;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 18px;
        }

        .back-link {
            text-decoration: none;
            color: #2563eb;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 14px;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo" class="login-logo">
                <div class="icon-box">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h2 class="login-title mb-1">Admin Login</h2>
                <p class="login-subtitle mb-0">Secure access to the WRPlumb admin control panel</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    Please check your login details.
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.send-otp') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Admin Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter admin email"
                        value="{{ old('email') }}"
                        required
                    >
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter password"
                        required
                    >
                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 login-btn">
                    <i class="fas fa-paper-plane me-2"></i>Send OTP
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('home') }}" class="back-link">← Back to Homepage</a>
            </div>
        </div>
    </div>
</body>
</html>