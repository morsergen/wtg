<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Offer;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StoreReservationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_creates_a_reservation_and_decreases_available_units(): void
    {
        $offer = Offer::factory()->create([
            'available_units' => 2,
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->postJson(
            route('offers.reservations.store', $offer),
            [
                'client_reference' => 'web-order-9f782b1c',
                'customer_name' => 'John Smith',
                'customer_email' => 'john@example.com',
            ],
        );

        $response
            ->assertCreated()
            ->assertJsonPath('data.offer_id', $offer->id)
            ->assertJsonPath('data.client_reference', 'web-order-9f782b1c');

        $this->assertDatabaseHas('reservations', [
            'offer_id' => $offer->id,
            'client_reference' => 'web-order-9f782b1c',
            'customer_name' => 'John Smith',
            'customer_email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('offers', [
            'id' => $offer->id,
            'available_units' => 1,
        ]);
    }

    public function test_it_rejects_a_reservation_when_offer_has_no_available_units(): void
    {
        $offer = Offer::factory()->create([
            'available_units' => 0,
            'expires_at' => now()->addHour(),
        ]);

        $this->postJson(
            route('offers.reservations.store', $offer),
            [
                'client_reference' => 'web-order-sold-out',
                'customer_name' => 'John Smith',
                'customer_email' => 'john@example.com',
            ],
        )
            ->assertStatus(409)
            ->assertJson([
                'message' => 'The offer is no longer available.',
            ]);

        $this->assertDatabaseMissing('reservations', [
            'offer_id' => $offer->id,
        ]);
    }

    public function test_it_validates_reservation_data(): void
    {
        $offer = Offer::factory()->create([
            'available_units' => 1,
            'expires_at' => now()->addHour(),
        ]);

        $this->postJson(
            route('offers.reservations.store', $offer),
            [],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'client_reference',
                'customer_name',
                'customer_email',
            ]);

        $this->assertDatabaseMissing('reservations', [
            'offer_id' => $offer->id,
        ]);

        $this->assertDatabaseHas('offers', [
            'id' => $offer->id,
            'available_units' => 1,
        ]);
    }
}
