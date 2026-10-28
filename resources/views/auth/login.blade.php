<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.theme-init')

        <title>Log in — {{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <header class="container py-4 d-flex align-items-center justify-content-between">
            <a class="navbar-brand d-inline-flex align-items-center gap-2 fw-semibold" href="/">
                <span class="brand-mark" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 4.75h7.5L19 9.25v10A1.75 1.75 0 0 1 17.25 21h-10A1.75 1.75 0 0 1 5.5 19.25v-12A2.5 2.5 0 0 1 8 4.75Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="M14 5v4.5h4.5M9 13h6M9 16.5h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>{{ config('app.name') }}</span>
            </a>
            <button class="theme-toggle btn btn-sm rounded-circle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
                <i class="bi bi-moon-stars-fill theme-toggle-icon" aria-hidden="true"></i>
            </button>
        </header>

        <main class="auth-page">
            <div class="container">
                <section class="auth-card" aria-labelledby="login-heading">
                    <div class="text-center mb-4">
                        <p class="text-uppercase fw-semibold text-primary small mb-2">Welcome back</p>
                        <h1 class="h2 fw-semibold mb-2" id="login-heading">Log in to your account</h1>
                        <p class="text-secondary mb-0">Pick up where your thoughts left off.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            Please check your details and try again.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-medium" for="email">Email address</label>
                            <input
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                placeholder="you@example.com"
                                required
                                autofocus
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium" for="password">Password</label>
                            <input
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                required
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-notes btn-lg w-100 rounded-pill" type="submit">Log in</button>
                    </form>

                    <p class="text-center text-secondary mt-4 mb-0">
                        Don’t have an account?
                        <a class="fw-semibold text-decoration-none" href="{{ route('register') }}">Create an account</a>
                    </p>
                </section>
            </div>
        </main>
    </body>
</html>
