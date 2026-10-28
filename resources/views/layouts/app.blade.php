<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name'))</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <header class="app-header">
            <nav class="container app-nav d-flex align-items-center gap-3" aria-label="Main navigation">
                <a class="app-brand d-flex align-items-center gap-2 text-decoration-none" href="{{ route('dashboard') }}">
                    <span class="brand-mark"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
                    <span>Notes</span>
                </a>

                <div class="dropdown ms-auto">
                    <button
                        class="app-user-menu dropdown-toggle d-flex align-items-center gap-2"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Open account menu"
                    >
                        <span class="app-user-copy text-end d-none d-sm-block">
                            <span class="app-user-name d-block">{{ Auth::user()->user_name }}</span>
                            <span class="app-user-label d-block">My account</span>
                        </span>
                        <img
                            class="app-user-avatar"
                            src="{{ route('profile.avatar') }}"
                            data-fallback-src="{{ asset('images/default-avatar.svg') }}"
                            alt="{{ Auth::user()->user_name }}'s profile picture"
                            width="44"
                            height="44"
                        >
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end app-user-dropdown">
                        <li class="px-3 py-2">
                            <span class="d-block fw-semibold">{{ Auth::user()->user_name }}</span>
                            <span class="small text-secondary text-truncate d-block">{{ Auth::user()->user_email }}</span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person-circle" aria-hidden="true"></i>
                                Edit profile
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item d-flex align-items-center gap-2 text-danger" type="submit">
                                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                    Log out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <main class="container pb-5">
            @yield('content')
        </main>

        <div class="toast-container position-fixed top-0 end-0 p-3" aria-live="polite" aria-atomic="true">
            <div
                id="app-toast"
                class="toast align-items-center text-bg-success border-0"
                role="status"
                data-toast-title="{{ $errors->any() ? 'There was a problem' : (session('warning') ? 'Profile updated with a warning' : 'Success') }}"
                data-toast-message="{{ $errors->first() ?: (session('warning') ?: session('status')) }}"
                data-toast-type="{{ $errors->any() ? 'danger' : (session('warning') ? 'warning' : 'success') }}"
            >
                <div class="d-flex">
                    <div class="toast-body">
                        <strong class="d-block" data-toast-title></strong>
                        <span data-toast-message></span>
                    </div>
                    <button
                        type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"
                        aria-label="Close"
                    ></button>
                </div>
            </div>
        </div>
    </body>
</html>
