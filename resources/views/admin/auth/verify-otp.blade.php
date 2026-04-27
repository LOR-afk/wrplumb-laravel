<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify OTP - WRPlumb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="mb-3">Verify OTP</h3>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @error('otp')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <form method="POST" action="{{ route('admin.otp.verify') }}">
                        @csrf

                        <div class="mb-3">
                            <label>OTP Code</label>
                            <input type="text" name="otp" class="form-control" maxlength="6" required>
                        </div>

                        <button class="btn btn-primary w-100">Verify OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
