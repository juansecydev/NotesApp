<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.theme-init')
        <title>Create account — {{ config('app.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <header class="container py-4 d-flex align-items-center justify-content-between">
            <a class="navbar-brand fw-semibold" href="{{ route('home') }}">{{ config('app.name') }}</a>
            <button class="theme-toggle btn btn-sm rounded-circle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
                <i class="bi bi-moon-stars-fill theme-toggle-icon" aria-hidden="true"></i>
            </button>
        </header>

        <main class="auth-page">
            <div class="container">
                <section class="auth-card" aria-labelledby="register-heading">
                    <div class="text-center mb-4">
                        <p class="text-uppercase fw-semibold text-primary small mb-2">Get started</p>
                        <h1 class="h2 fw-semibold mb-2" id="register-heading">Create your account</h1>
                        <p class="text-secondary mb-0">Start keeping your notes in one place.</p>
                    </div>

                    <form method="POST" action="{{ route('register.store') }}" class="vstack gap-3">
                        @csrf

                        <div>
                            <label class="form-label" for="user_name">Name</label>
                            <input class="form-control @error('user_name') is-invalid @enderror" id="user_name" name="user_name" value="{{ old('user_name') }}" maxlength="30" autocomplete="name" required autofocus>
                            @error('user_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="form-label" for="user_email">Email address</label>
                            <input class="form-control @error('user_email') is-invalid @enderror" id="user_email" name="user_email" type="email" value="{{ old('user_email') }}" autocomplete="email" required>
                            @error('user_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="new-password" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="form-label" for="password_confirmation">Confirm password</label>
                            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                        </div>

                        <button class="btn btn-notes btn-lg w-100 rounded-pill" type="submit">Create account</button>
                    </form>

                    <p class="text-center text-secondary mt-4 mb-0">
                        Already registered?
                        <a class="fw-semibold text-decoration-none" href="{{ route('login') }}">Log in</a>
                    </p>
                </section>
            </div>
        </main>
    </body>
</html>
