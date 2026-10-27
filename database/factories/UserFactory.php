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
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('Admin');

            // Admins are the only role required to have 2FA set up (see
            // AdminPanelProvider::multiFactorAuthentication). A real admin account
            // would already have this configured — tests exercising unrelated
            // features shouldn't have to walk through the setup screen first.
            $user->saveAppAuthenticationSecret('TESTINGTESTINGTESTINGTESTINGTEST');
        });
    }

    public function editor(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('Editor');
        });
    }

    public function viewer(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('Viewer');
        });
    }
}
