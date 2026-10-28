<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_name' => Str::limit(fake()->name(), 30, ''),
            'user_email' => fake()->unique()->safeEmail(),
            'user_password' => static::$password ??= Hash::make('password'),
            'user_pfp' => null,
        ];
    }
}
