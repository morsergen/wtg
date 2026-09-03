<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->unique()->bothify('???-####')),
            'name' => Str::limit(
                Str::title(fake()->words(4, true)),
                255,
                '',
            ),
            'city_id' => City::factory(),
        ];
    }
}
