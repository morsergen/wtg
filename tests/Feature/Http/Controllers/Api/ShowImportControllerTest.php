<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ShowImportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_returns_import_status(): void
    {
        $supplier = Supplier::factory()->create([
            'slug' => 'supplier-a',
        ]);

        $import = Import::factory()
            ->for($supplier)
            ->create([
                'external_import_id' => 'import-2026-09-01-001',
                'sent_at' => '2026-09-01 10:00:00',
                'status' => ImportStatus::Completed,
                'total_offers' => 20,
                'processed_offers' => 20,
                'error' => null,
                'created_at' => '2026-09-01 10:00:02',
                'completed_at' => '2026-09-01 10:00:04',
            ]);

        $response = $this->getJson(
            route('imports.show', $import),
        );

        $response
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $import->id,
                    'supplier' => 'supplier-a',
                    'external_import_id' => 'import-2026-09-01-001',
                    'sent_at' => '2026-09-01T10:00:00Z',
                    'status' => 'completed',
                    'total_offers' => 20,
                    'processed_offers' => 20,
                    'error' => null,
                    'created_at' => '2026-09-01T10:00:02Z',
                    'completed_at' => '2026-09-01T10:00:04Z',
                ],
            ]);
    }

    public function test_it_returns_not_found_for_an_unknown_import(): void
    {
        $response = $this->getJson(
            route('imports.show', ['import' => 999_999_999]),
        );

        $response->assertNotFound();
    }

    public function test_it_returns_the_import_failure_reason(): void
    {
        $import = Import::factory()->create([
            'status' => ImportStatus::Failed,
            'error' => 'Invalid offer payload.',
        ]);

        $this->getJson(route('imports.show', $import))
            ->assertOk()
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.error', 'Invalid offer payload.');
    }
}
