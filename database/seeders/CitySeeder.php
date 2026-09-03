<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    use WithoutModelEvents;

    private const array CITIES = [
        'Barcelona',
        'Madrid',
        'Valencia',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CITIES as $name) {
            City::query()->firstOrCreate([
                'name' => $name,
            ]);
        }
    }
}
