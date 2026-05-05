<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - WRPlumb</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/login.css') }}">

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