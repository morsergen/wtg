<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::query()->updateOrCreate(
            ['slug' => 'supplier-a'],
            ['name' => 'Supplier A'],
        );

        Supplier::query()->updateOrCreate(
            ['slug' => 'supplier-b'],
            ['name' => 'Supplier B'],
        );
    }
}
