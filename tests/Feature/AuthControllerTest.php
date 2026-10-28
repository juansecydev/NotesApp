<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Exercises guest registration and session-based authentication.
 */
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The login form is public and redirects signed-in users to their dashboard.
     */
    public function test_login_form_is_public_and_redirects_authenticated_users(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();

        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    /**
     * Valid credentials authenticate the matching account and start a session.
     */
    public function test_valid_credentials_create_an_authenticated_session(): void
    {
        $user = User::factory()->create([
            'user_email' => 'login@example.com',
            'user_password' => 'correct-password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'login@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Invalid credentials do not authenticate a user.
     */
    public function test_invalid_credentials_return_a_login_error(): void
    {
        User::factory()->create([
            'user_email' => 'login@example.com',
            'user_password' => 'correct-password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Valid registration creates a hashed-password account and authenticates it.
     */
    public function test_registration_creates_and_authenticates_a_user(): void
    {
        $response = $this->post(route('register.store'), [
            'user_name' => 'New User',
            'user_email' => 'new@example.com',
            'password' => 'long-enough-password',
            'password_confirmation' => 'long-enough-password',
        ]);

        $user = User::query()->where('user_email', 'new@example.com')->firstOrFail();

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('long-enough-password', $user->user_password));
    }

    /**
     * Registration rejects an email address that is already in use.
     */
    public function test_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['user_email' => 'taken@example.com']);

        $response = $this->post(route('register.store'), [
            'user_name' => 'New User',
            'user_email' => 'taken@example.com',
            'password' => 'long-enough-password',
            'password_confirmation' => 'long-enough-password',
        ]);

        $response->assertSessionHasErrors(['user_email']);
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    /**
     * Logout clears the authenticated session and returns to the login form.
     */
    public function test_logout_ends_the_authenticated_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get(route('notes.index'))->assertRedirect(route('login'));
    }
}
