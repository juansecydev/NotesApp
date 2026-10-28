<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.theme-init')

        <title>{{ config('app.name')}} — A home for your thoughts</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <nav class="navbar navbar-expand-md" aria-label="Main navigation">
            <div class="container">
                <a class="navbar-brand d-inline-flex align-items-center gap-2 fw-semibold" href="/">
                    <span class="brand-mark" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 4.75h7.5L19 9.25v10A1.75 1.75 0 0 1 17.25 21h-10A1.75 1.75 0 0 1 5.5 19.25v-12A2.5 2.5 0 0 1 8 4.75Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="M14 5v4.5h4.5M9 13h6M9 16.5h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span>{{ config('app.name')}}</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-navigation" aria-controls="main-navigation" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="main-navigation">
                    <ul class="navbar-nav ms-auto align-items-md-center gap-md-3">
                        <li class="nav-item">
                            <button
                                class="theme-toggle btn btn-sm rounded-circle"
                                type="button"
                                data-theme-toggle
                                aria-label="Switch color theme"
                                title="Switch color theme"
                            >
                                <i class="bi bi-moon-stars-fill theme-toggle-icon" aria-hidden="true"></i>
                            </button>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary" href="#features">Features</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary" href="{{ route('login') }}">Log in</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-notes rounded-pill" href="{{ route('register') }}">Create account</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main>
            <section class="hero-section">
                <div class="container">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6">
                            <div class="hero-copy">
                                <p class="text-uppercase fw-semibold text-primary small mb-3">Make room for good ideas</p>
                                <h1 class="hero-title mb-4">A calmer place for your thoughts.</h1>
                                <p class="hero-description mb-4">
                                    Keep your notes, plans, and little sparks of inspiration together. Simple to start, easy to come back to.
                                </p>
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <a class="btn btn-notes btn-lg rounded-pill" href="{{ route('register') }}">Get started</a>
                                    <a class="link-secondary fw-medium text-decoration-none" href="{{ route('login') }}">Already have an account? Log in</a>
                                </div>
                                <p class="small text-secondary mt-3 mb-0">A fresh space for everything on your mind.</p>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="notes-preview" aria-label="Preview of notes in NotesApp">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div>
                                        <p class="small text-secondary mb-1">MONDAY, 9:41 AM</p>
                                        <h2 class="h5 fw-semibold mb-0">Your notes</h2>
                                    </div>
                                    <span class="badge rounded-pill text-bg-light px-3 py-2">3 notes</span>
                                </div>

                                <article class="preview-note mb-3">
                                    <div class="preview-note-title bg-warning text-dark d-flex justify-content-between align-items-start">
                                        <h3 class="h6 fw-semibold mb-0"><span class="note-dot"></span>Ideas for the weekend</h3>
                                        <span class="small text-secondary">9:32</span>
                                    </div>
                                    <p class="text-secondary small p-3 mb-0">A long walk, a good book, and finally try that new coffee place.</p>
                                </article>

                                <article class="preview-note mb-3">
                                    <div class="preview-note-title bg-primary text-white d-flex justify-content-between align-items-start">
                                        <h3 class="h6 fw-semibold mb-0">Things to remember</h3>
                                        <span class="small text-white-50">Yesterday</span>
                                    </div>
                                    <p class="text-secondary small p-3 mb-0">Pick up groceries · Call Sam · Water the plants</p>
                                </article>

                                <article class="preview-note">
                                    <div class="preview-note-title bg-success text-white d-flex justify-content-between align-items-start">
                                        <h3 class="h6 fw-semibold mb-0">A thought worth keeping</h3>
                                        <span class="small text-white-50">Friday</span>
                                    </div>
                                    <p class="text-secondary small p-3 mb-0">Small steps still move you forward.</p>
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="features-section" id="features">
                <div class="container">
                    <div class="row justify-content-center text-center mb-5">
                        <div class="col-lg-7">
                            <p class="text-uppercase fw-semibold text-primary small mb-2">Less clutter, more clarity</p>
                            <h2 class="display-6 fw-semibold mb-3">Your mind, a little more organized.</h2>
                            <p class="text-secondary mb-0">A simple space to collect the things you want to remember and make sense of.</p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <article class="h-100">
                                <span class="feature-icon mb-3" aria-hidden="true">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <h3 class="h5 fw-semibold">Capture the moment</h3>
                                <p class="text-secondary mb-0">Get ideas out of your head and into a space that feels easy to use.</p>
                            </article>
                        </div>
                        <div class="col-md-4">
                            <article class="h-100">
                                <span class="feature-icon mb-3" aria-hidden="true">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.75 6.75h14.5M4.75 12h14.5M4.75 17.25h9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <h3 class="h5 fw-semibold">Keep things clear</h3>
                                <p class="text-secondary mb-0">Bring thoughts, lists, and plans together without adding extra noise.</p>
                            </article>
                        </div>
                        <div class="col-md-4">
                            <article class="h-100">
                                <span class="feature-icon mb-3" aria-hidden="true">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 3.75 5 6.5v5.25c0 4.1 2.9 7.55 7 8.5 4.1-.95 7-4.4 7-8.5V6.5l-7-2.75Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                        <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <h3 class="h5 fw-semibold">Make it your own</h3>
                                <p class="text-secondary mb-0">A personal home for the details, reminders, and ideas that matter to you.</p>
                            </article>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="site-footer py-4">
            <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <a class="d-inline-flex align-items-center gap-2 fw-semibold text-dark text-decoration-none" href="/">
                    <span class="brand-mark" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 4.75h7.5L19 9.25v10A1.75 1.75 0 0 1 17.25 21h-10A1.75 1.75 0 0 1 5.5 19.25v-12A2.5 2.5 0 0 1 8 4.75Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="M14 5v4.5h4.5M9 13h6M9 16.5h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    NotesApp
                </a>
                <p class="small text-secondary mb-0">A little more space for what matters.</p>
            </div>
        </footer>
    </body>
</html>
