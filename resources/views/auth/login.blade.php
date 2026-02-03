<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
<form method="POST" action="/login" class="card p-4" style="width:300px">
@csrf
<h4 class="mb-3">Login</h4>
<input name="email" class="form-control mb-2" placeholder="Email">
<input name="password" type="password" class="form-control mb-3" placeholder="Password">
<button class="btn btn-primary w-100">Login</button>
</form>
</body>
</html>