<?php

namespace Database\Factories;

use App\Models\Import;
use App\Models\Offer;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = now()
            ->addDays(fake()->numberBetween(1, 90))
            ->startOfDay();

        $checkOut = $checkIn
            ->copy()
            ->addDays(fake()->numberBetween(1, 14));

        return [
            'import_id' => Import::factory(),
            'supplier_id' => fn (array $attributes) => Import::query()->findOrFail($attributes['import_id'])->supplier_id,
            'property_id' => Property::factory(),
            'external_id' => fake()->uuid(),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'max_guests' => fake()->numberBetween(1, 8),
            'price' => fake()->numberBetween(3_000, 200_000),
            'currency' => 'EUR',
            'available_units' => fake()->numberBetween(1, 20),
            'expires_at' => now()->addHours(fake()->numberBetween(1, 48)),
        ];
    }
}
