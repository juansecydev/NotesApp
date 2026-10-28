<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies that the dashboard requires an authenticated session.
 */
class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guests are redirected to login and authenticated users reach their notes.
     */
    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('notes.index'));
    }
}
