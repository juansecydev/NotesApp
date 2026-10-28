<?php

namespace Tests\Feature\Database\Seeders;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Verifies the initial user account seeded for the notes application.
 */
class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The seeder creates the specified account once and preserves its password hash.
     */
    public function test_seed_creates_the_initial_user_idempotently(): void
    {
        $this->travelTo('2026-10-20T12:00:00+00:00');

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->where('user_email', 'marin.kitagawa@email.com')->firstOrFail();

        $this->assertSame('Marin Kitagawa', $user->user_name);
        $this->assertSame(
            '$2y$11$OuS6XhPbsoTidBbdQtC/xeA3LOt4DIZip7pSKZfqtPtGC7SdssaIq',
            $user->user_password,
        );
        $this->assertSame('2026-10-20', $user->user_created_at->toDateString());
        $this->assertSame(
            '2026-10-20T12:00:00+00:00',
            DB::table('users')->where('user_id', $user->user_id)->value('user_created_at'),
        );
        $this->assertDatabaseCount('users', 1);
    }
}
