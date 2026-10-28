<?php

namespace Database\Factories;

use App\Enums\NoteColor;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'note_user_id' => User::factory(),
            'note_color' => fake()->randomElement(NoteColor::cases())->value,
            'note_title' => Str::limit(fake()->sentence(3), 30),
            'note_content' => Str::limit(fake()->paragraph(), 300),
        ];
    }
}
