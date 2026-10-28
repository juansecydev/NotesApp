<?php

namespace Tests\Feature\Database\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Verifies conversion of existing Laravel user records to the notes schema.
 */
class MigrateUsersToNotesSchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The forward and reverse migrations preserve account data and normalize date storage.
     */
    public function test_migration_preserves_existing_user_data_and_supports_rollback(): void
    {
        Schema::dropIfExists('notes');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')->insert([
            'id' => 41,
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'email_verified_at' => '2026-10-18 09:00:00',
            'password' => 'existing-password-hash',
            'remember_token' => 'existing-remember-token',
            'created_at' => '2026-10-19 10:00:00',
            'updated_at' => '2026-10-19 12:30:00',
        ]);

        $migration = require base_path('database/migrations/2026_10_20_230820_migrate_users_to_notes_schema.php');
        $migration->up();

        $user = DB::table('users')->where('user_id', 41)->first();

        $this->assertSame('Ada Lovelace', $user->user_name);
        $this->assertSame('ada@example.com', $user->user_email);
        $this->assertSame('existing-password-hash', $user->user_password);
        $this->assertSame('2026-10-19T10:00:00+00:00', $user->user_created_at);
        $this->assertSame('2026-10-19T12:30:00+00:00', $user->user_updated_at);
        $this->assertSame('existing-remember-token', $user->remember_token);

        $migration->down();

        $legacyUser = DB::table('users')->where('id', 41)->first();

        $this->assertSame('Ada Lovelace', $legacyUser->name);
        $this->assertSame('ada@example.com', $legacyUser->email);
        $this->assertSame('existing-password-hash', $legacyUser->password);
        $this->assertSame('2026-10-19 10:00:00', $legacyUser->created_at);
        $this->assertSame('2026-10-19 12:30:00', $legacyUser->updated_at);
        $this->assertSame('existing-remember-token', $legacyUser->remember_token);
    }
}
