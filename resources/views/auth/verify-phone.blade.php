<!DOCTYPE html>
<html>
<head>
    <title>Verify Phone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Verify Your Phone Number</div>
                    <div class="card-body">
                        @if (session('message'))
                            <div class="alert alert-success">
                                {{ session('message') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <p>Please enter the verification code sent to your WhatsApp number:</p>
                        <p clasos="text-muted">{{ auth()->user()->phone_cde }} {{ auth()->user()->phone }}</p>

                        <form method="POST" action="{{ route('phone.verify.submit') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="otp" class="form-label">Verification Code</label>
                                <input type="text" class="form-control" id="otp" name="otp" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Verify</button>
                        </form>

                        <form method="POST" action="{{ route('phone.verify.resend') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-link">Resend Code</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
