<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('B/assets/images/avi2.png') }}">

    <!-- Preloader CSS -->
    <link rel="stylesheet" href="{{ asset('B/assets/css/preloader.min.css') }}" type="text/css" />

    <!-- Bootstrap CSS -->
    <link href="{{ asset('B/assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons CSS -->
    <link href="{{ asset('B/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="{{ asset('B/assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    <style>
        body {
            background: url('{{ asset('B/assets/images/1633.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            color: #fff; 
        }
    </style>
</head>
<body>
<div class="account-pages my-5 pt-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="login-logo text-center mb-4">
                            <img src="{{ asset('B/assets/images/AVI.png') }}" alt="AVI Logo" class="img-fluid" style="max-width: 50%;">
                        </div>
                        <div class="p-2 mt-4">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form action="{{ route('register') }}" method="post">
                                @csrf
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>

                                <div class="d-grid">
                                    <button class="btn btn-primary" type="submit">Register</button>
                                </div>

                                <div class="mt-4 text-center">
                                    <a href="{{ route('login') }}" class="text-muted">
                                        <i class="mdi mdi-login me-1"></i> Sudah memiliki akun?
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
