<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        Dashboard
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Logout</button>
                        </form>
                    </div>
                    <div class="card-body">
                        <h5>Welcome, {{ Auth::user()->name }}!</h5>
                        <p>Your email: {{ Auth::user()->email }}</p>
                        <p>Your phone: {{ Auth::user()->phone_code }} {{ Auth::user()->phone }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 