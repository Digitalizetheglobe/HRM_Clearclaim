<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Super Admin Register') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Cache Control Headers -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('{{ asset('assets/images/back.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Arial', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            flex-direction: column;
        }

        .login-container {
            display: flex;
            flex-direction: row;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 900px;
            min-height: 500px;
        }

        .login-image {
            flex: 1;
            background: url('{{ asset('assets/images/login.jpg') }}') no-repeat center center;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .login-image .text {
            text-align: center;
            color: #fff;
            font-size: 20px;
            font-weight: 600;
            padding: 20px;
            z-index: 1;
        }

        .login-form {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .logo {
            position: absolute;
            top: 10px;
            left: 10px;
        }

        .logo img {
            width: 150px;
            height: auto;
        }

        .login-form h2 {
            margin-bottom: 20px;
            font-weight: 600;
            color: #333;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 10px 40px 10px 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .eye-icon {
            position: absolute;
            top: 70%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #555;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .text-danger {
            color: #dc3545;
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column-reverse;
                width: 95%;
            }

            .logo{
                left:150px;
            }

            .login-image {
                display: none;
            }

            .login-form {
                padding: 20px;
            }

            .logo img {
                width: 200px;
            }

            .login-form h2 {
                font-size: 22px;
            }

            .btn {
                font-size: 14px;
                padding: 10px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                width: 100%;
                border-radius: 0;
                box-shadow: none;
            }

            .logo{
                left:120px;
            }

            .login-form {
                padding: 15px;
            }

            .logo img {
                width: 100px;
            }

            .login-form h2 {
                font-size: 20px;
            }

            .login-image {
                height: 200px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-image">
            <div class="overlay"></div>
        </div>
        <div class="login-form">
            <div class="logo">
                <img src="{{ asset('storage/uploads/logo/2_dark_logo.png') }}" alt="Logo">
            </div>
            
            @if(session('status'))
                <div class="alert alert-success" style="margin-top: 80px;">
                    {{ session('status') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger" style="margin-top: 80px;">
                    {{ session('error') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('super-admin.register.store') }}" autocomplete="off" style="margin-top: 25px;">
                @csrf
                <input type="hidden" name="timestamp" value="{{ time() }}">
                
                <div class="form-group">
                    <label>{{ __('Name') }}</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                        name="name" placeholder="{{ __('Enter your name') }}" required autofocus value="{{ old('name') }}">
                    @error('name')
                        <span class="text-danger"><small>{{ $message }}</small></span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label>{{ __('Email') }}</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                        name="email" placeholder="{{ __('Enter your email') }}" required value="{{ old('email') }}">
                    @error('email')
                        <span class="text-danger"><small>{{ $message }}</small></span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label>{{ __('Password') }}</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                        name="password" placeholder="{{ __('Password') }}" required autocomplete="new-password">
                    <i class="fa fa-eye eye-icon" id="togglePassword"></i>
                    @error('password')
                        <span class="text-danger"><small>{{ $message }}</small></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>{{ __('Confirm Password') }}</label>
                    <input id="password-confirm" type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                        name="password_confirmation" placeholder="{{ __('Confirm Password') }}" required autocomplete="new-password">
                    <i class="fa fa-eye eye-icon" id="toggleConfirmPassword" style="top: 65%;"></i>
                    @error('password_confirmation')
                        <span class="text-danger"><small>{{ $message }}</small></span>
                    @enderror
                </div>
                
                <button type="submit" class="btn">{{ __('Register as Super Admin') }}</button>
            </form>
            
            <p class="my-4 text-center">{{ __('Already have an account?') }}
                <a href="{{ route('login') }}" tabindex="0">{{ __('Login') }}</a>
            </p>
        </div>
    </div>

    <script>
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPassword = document.getElementById('password-confirm');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        toggleConfirmPassword.addEventListener('click', function () {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
