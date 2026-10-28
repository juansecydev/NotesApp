<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Handles guest registration, session login, and logout.
 */
class AuthController extends Controller
{
    /**
     * Display the login form.
     *
     * @return View The login form.
     */
    public function createLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate a user with the submitted email and password.
     *
     * @param  Request  $request  The login form submission.
     * @return RedirectResponse A redirect to the intended page or login form.
     *
     * @throws ValidationException When the credentials are invalid.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([
            'user_email' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Display the public registration form.
     *
     * @return View The registration form.
     */
    public function createRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Validate and create an account, then authenticate the new user.
     *
     * @param  Request  $request  The registration form submission.
     * @return RedirectResponse A redirect to the authenticated dashboard.
     *
     * @throws ValidationException When the submitted account data is invalid.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_name' => ['required', 'string', 'max:30'],
            'user_email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'user_email')],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'max:255'],
        ]);

        $user = User::create([
            'user_name' => $validated['user_name'],
            'user_email' => $validated['user_email'],
            'user_password' => $validated['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * End the current session and invalidate its identifier and CSRF token.
     *
     * @param  Request  $request  The authenticated browser request.
     * @return RedirectResponse A redirect to the public login form.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
