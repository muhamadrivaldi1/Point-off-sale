<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc); /* Warna gradient */
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .icon-pos {
            font-size: 50px;
            color: #2575fc;
        }
        h4 {
            text-align: center;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
        <form method="POST" action="{{ route('login.post') }}" class="card p-4" style="width:300px;">
        @csrf
        <div class="text-center mb-3">
            <i class="fas fa-cash-register icon-pos"></i>
        </div>
        <h4 class="mb-3">Login</h4>
        <input name="email" class="form-control mb-2" placeholder="Email" required>
        <input name="password" type="password" class="form-control mb-2" placeholder="Password" required>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="rememberPassword">
            <label class="form-check-label" for="rememberPassword">
                Ingat Password
            </label>
        </div>
        <button class="btn btn-primary w-100">Login</button>
    </form>
</body>
</html>
