<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $realms = ['master', 'nutrients'];

        return [
            'name' => $this->faker->regexify('[a-z0-9]{8,16}'),
            'realm' => $this->faker->randomElement($realms),
            'client_id' => $this->faker->regexify('[a-z0-9]{8,16}') . '_client',
            'client_secret' => $this->faker->regexify('[A-Za-z0-9]{16}'),
            'grant_type' => 'password',
            'url' => $this->faker->url . ':' . $this->faker->numberBetween(1024, 65535),
            'callback_url' => $this->faker->url . ':' . $this->faker->numberBetween(1024, 65535) . '/callback',
            'description' => substr($this->faker->sentence(), 0, 255)
        ];
    }
}
