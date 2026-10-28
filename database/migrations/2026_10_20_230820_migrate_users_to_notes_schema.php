<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert the existing Laravel user schema while preserving account IDs and passwords.
     *
     *
     * @throws RuntimeException When the users table does not have the expected Laravel schema.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'user_id')) {
            return;
        }

        if (! Schema::hasColumn('users', 'id') || ! Schema::hasColumn('users', 'name')) {
            throw new RuntimeException('The users table does not have the expected Laravel schema.');
        }

        Schema::create('users_rebuilt', function (Blueprint $table): void {
            $table->increments('user_id');
            $table->string('user_name', 30);
            $table->string('user_email')->unique();
            $table->string('user_password', 255);
            $table->string('user_pfp', 200)->nullable();
            $table->timestamp('user_created_at');
            $table->timestamp('user_updated_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
        });

        DB::table('users')->orderBy('id')->get()->each(static function (object $user): void {
            DB::table('users_rebuilt')->insert([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_password' => $user->password,
                'user_pfp' => null,
                'user_created_at' => Carbon::parse($user->created_at)->format('Y-m-d\TH:i:sP'),
                'user_updated_at' => $user->updated_at === null
                    ? null
                    : Carbon::parse($user->updated_at)->format('Y-m-d\TH:i:sP'),
                'email_verified_at' => $user->email_verified_at,
                'remember_token' => $user->remember_token,
            ]);
        });

        Schema::drop('users');
        Schema::rename('users_rebuilt', 'users');
    }

    /**
     * Restore the standard Laravel user table shape.
     *
     * Profile picture paths are discarded because the legacy schema has no matching column.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'user_id')) {
            return;
        }

        Schema::create('users_legacy', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')->orderBy('user_id')->get()->each(static function (object $user): void {
            DB::table('users_legacy')->insert([
                'id' => $user->user_id,
                'name' => $user->user_name,
                'email' => $user->user_email,
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->user_password,
                'remember_token' => $user->remember_token,
                'created_at' => Carbon::parse($user->user_created_at)->format('Y-m-d H:i:s'),
                'updated_at' => $user->user_updated_at === null
                    ? null
                    : Carbon::parse($user->user_updated_at)->format('Y-m-d H:i:s'),
            ]);
        });

        Schema::drop('users');
        Schema::rename('users_legacy', 'users');
    }
};
