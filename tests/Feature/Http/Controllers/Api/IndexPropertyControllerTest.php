<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Offer;
use App\Models\Property;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class IndexPropertyControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_requires_dates_and_number_of_guests(): void
    {
        $this->getJson('/api/properties')
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'check_in',
                'check_out',
                'guests',
            ]);
    }

    public function test_it_returns_a_property_with_a_matching_offer(): void
    {
        $property = Property::factory()->create();

        Offer::factory()
            ->for($property)
            ->create([
                'check_in' => '2026-10-10',
                'check_out' => '2026-10-15',
                'max_guests' => 4,
                'price' => 72_500,
                'currency' => 'EUR',
                'available_units' => 2,
                'expires_at' => now()->addHour(),
            ]);

        $response = $this->getJson(route('properties.index', [
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'guests' => 2,
        ]));

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'data' => [
                    [
                        'code',
                        'name',
                        'city',
                        'best_offer' => [
                            'id',
                            'supplier',
                            'price',
                            'currency',
                            'available_units',
                            'expires_at',
                        ],
                    ],
                ],
                'links' => [
                    'prev',
                    'next',
                ],
                'meta' => [
                    'per_page',
                ],
            ]);
    }

    public function test_it_returns_the_cheapest_matching_offer_for_a_property(): void
    {
        $property = Property::factory()->create();

        Offer::factory()
            ->for($property)
            ->create([
                'check_in' => '2026-10-10',
                'check_out' => '2026-10-15',
                'max_guests' => 4,
                'price' => 72_500,
                'currency' => 'EUR',
                'available_units' => 2,
                'expires_at' => now()->addHour(),
            ]);

        $cheapestOffer = Offer::factory()
            ->for($property)
            ->create([
                'check_in' => '2026-10-10',
                'check_out' => '2026-10-15',
                'max_guests' => 2,
                'price' => 65_000,
                'currency' => 'EUR',
                'available_units' => 1,
                'expires_at' => now()->addHour(),
            ]);

        $this->getJson(route('properties.index', [
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'guests' => 2,
        ]))
            ->assertOk()
            ->assertJsonPath('data.0.best_offer.id', $cheapestOffer->id)
            ->assertJsonPath('data.0.best_offer.price', 65_000);
    }

    public function test_it_orders_properties_by_their_best_offer_price(): void
    {
        $expensiveProperty = Property::factory()->create([
            'code' => 'EXPENSIVE',
        ]);

        Offer::factory()
            ->for($expensiveProperty)
            ->create([
                'check_in' => '2026-10-10',
                'check_out' => '2026-10-15',
                'max_guests' => 2,
                'price' => 80_000,
                'available_units' => 1,
                'expires_at' => now()->addHour(),
            ]);

        $cheapProperty = Property::factory()->create([
            'code' => 'CHEAP',
        ]);

        Offer::factory()
            ->for($cheapProperty)
            ->create([
                'check_in' => '2026-10-10',
                'check_out' => '2026-10-15',
                'max_guests' => 2,
                'price' => 50_000,
                'available_units' => 1,
                'expires_at' => now()->addHour(),
            ]);

        $this->getJson(route('properties.index', [
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'guests' => 2,
        ]))
            ->assertOk()
            ->assertJsonPath('data.0.code', 'CHEAP')
            ->assertJsonPath('data.1.code', 'EXPENSIVE');
    }
}
