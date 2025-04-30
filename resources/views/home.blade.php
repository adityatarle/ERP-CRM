<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Welcome') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        /* Login Page Styling */
        .container {
            /* max-width: 1200px; */
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            width: 100%;
            max-width: 500px; /* Restrict card width for better centering */
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: #3a5b7f;
            color: white;
            font-weight: 500;
            text-align: center;
            border-radius: 10px 10px 0 0;
            padding: 1.25rem;
            font-size: 1.25rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 0.75rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
            outline: none;
        }

        .is-invalid {
            border-color: #dc3545;
       10px;
        }

        .invalid-feedback {
            font-size: 0.875rem;
            color: #dc3545;
        }

        .form-check-label {
            font-size: 0.9rem;
            color: #495057;
        }

        .btn-primary {
            background-color: #3a5b7f;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .btn-primary:hover {
            background-color: #3a5b7f;
            transform: translateY(-2px);
        }

        .btn-link {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
            display: block;
            text-align: center;
        }

        .btn-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        label {
            font-weight: 500;
            color: #343a40;
            text-align: left;
            display: block;
            margin-bottom: 0.5rem;
        }

        .row.mb-3 {
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }

            .card {
                margin: 0 1rem;
            }

            .btn-primary, .btn-link {
                width: 100%;
                margin-top: 0.5rem;
            }
        }

        /* Homepage Specific Styling */
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .hero-section {
            text-align: center;
            padding: 3rem 1rem;
            background: linear-gradient(135deg, #061f3b 0%, #6610f2 100%);
            color: white;
            margin-bottom: 2rem;
        }

        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .hero-section p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .content-section {
            padding: 2rem 0;
            text-align: center;
        }

        .content-section h2 {
            font-size: 1.8rem;
            color: #343a40;
            margin-bottom: 1rem;
        }

        .content-section p {
            font-size: 1rem;
            color: #6c757d;
            max-width: 800px;
            margin: 0 auto 2rem;
        }

        .register-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 1rem 0;
            background-color: #343a40;
            color: white;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <h1>{{ __('Welcome to Our Platform') }}</h1>
        <p>{{ __('Join us today to explore a world of opportunities and connect with our community.') }}</p>
    </div>

    <div class="content-section">
        <h2>{{ __('Log In to Your Account') }}</h2>
        <!-- <p>{{ __('Access your account or') }} <a href="{{ route('register') }}" class="register-link">{{ __('register') }}</a> {{ __('to get started.') }}</p> -->

        <div class="container p-3 mx-auto">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group">
                            <label for="email">{{ __('Email Address') }}</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div> -->
                        

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Login') }}
                            </button>
                            @if (Route::has('password.request'))
                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <p class="mb-0" style="font-size: 0.9rem;">© {{ date('Y') }} CRM Inventory. All rights reserved.</p>
    </footer>
</body>
</html>