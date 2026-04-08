<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $vehicleTypes = [
            [
                'name' => 'bike',
                'display_name' => 'Xe máy',
                'icon' => '🏍️',
                'base_fare' => 15000.00,
                'per_km_rate' => 8000.00,
                'per_minute_rate' => 500.00,
                'min_fare' => 15000.00,
                'max_passengers' => 1,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'car',
                'display_name' => 'Ô tô 4 chỗ',
                'icon' => '🚗',
                'base_fare' => 25000.00,
                'per_km_rate' => 12000.00,
                'per_minute_rate' => 800.00,
                'min_fare' => 25000.00,
                'max_passengers' => 4,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'van',
                'display_name' => 'Van 7 chỗ',
                'icon' => '🚐',
                'base_fare' => 35000.00,
                'per_km_rate' => 15000.00,
                'per_minute_rate' => 1000.00,
                'min_fare' => 35000.00,
                'max_passengers' => 7,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'premium',
                'display_name' => 'Ô tô cao cấp',
                'icon' => '🚙',
                'base_fare' => 45000.00,
                'per_km_rate' => 18000.00,
                'per_minute_rate' => 1200.00,
                'min_fare' => 45000.00,
                'max_passengers' => 4,
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('vehicle_types')->insert($vehicleTypes);
    }
}
