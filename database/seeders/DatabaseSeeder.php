<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the database with the initial account.
     */
    public function run(): void
    {
        if (! User::query()->where('user_email', 'marin.kitagawa@email.com')->exists()) {
            // Store the supplied hash unchanged instead of passing it through the model's hashed cast.
            DB::table('users')->insert([
                'user_name' => 'Marin Kitagawa',
                'user_password' => '$2y$11$OuS6XhPbsoTidBbdQtC/xeA3LOt4DIZip7pSKZfqtPtGC7SdssaIq',
                'user_email' => 'marin.kitagawa@email.com',
                'user_pfp' => null,
                'user_created_at' => now()->toIso8601String(),
                'user_updated_at' => null,
                'email_verified_at' => null,
                'remember_token' => null,
            ]);
        }
    }
}
