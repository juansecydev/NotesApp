<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUserWithNotesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Demo login password: NotesDemo123!

        DB::transaction(function (): void {
            $user = User::query()->updateOrCreate(
                ['user_email' => 'demo.notes@example.com'],
                [
                    'user_name' => 'Demo Notes User',
                    'user_password' => Hash::make('NotesDemo123!'),
                    'user_pfp' => null,
                ],
            );

            Note::query()->where('note_user_id', $user->user_id)->delete();

            Note::factory()->count(30)->create([
                'note_user_id' => $user->user_id,
            ]);
        });
    }
}
