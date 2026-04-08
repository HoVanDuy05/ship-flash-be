<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Driver;
use App\Models\Ride;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Vehicle Types
        $this->call(VehicleTypeSeeder::class);

        // 2. Users
        $user1 = User::factory()->create([
            'name' => 'Nguyễn Văn A',
            'email' => 'user@shipflash.com',
            'phone' => '0901234567',
        ]);

        $user2 = User::factory()->create([
            'name' => 'Trần Thị B',
            'email' => 'user2@shipflash.com',
            'phone' => '0912345678',
        ]);

        // 3. Drivers
        $driverUser1 = User::factory()->create([
            'name' => 'Tài xế Minh',
            'email' => 'driver1@shipflash.com',
            'phone' => '0923456789',
        ]);

        $driverUser2 = User::factory()->create([
            'name' => 'Tài xế Hùng',
            'email' => 'driver2@shipflash.com',
            'phone' => '0934567890',
        ]);

        $driver1 = Driver::create([
            'user_id' => $driverUser1->id,
            'vehicle_type_id' => 2,
            'license_number' => '51F-12345',
            'license_expiry' => now()->addYears(5),
            'license_plate' => '51F-12345',
            'vehicle_color' => 'Trắng',
            'vehicle_brand' => 'Toyota',
            'vehicle_model' => 'Vios',
            'vehicle_year' => '2022',
            'vehicle_registration' => 'REG-001',
            'registration_expiry' => now()->addYears(2),
            'status' => 'online',
            'is_verified' => true,
            'latitude' => 10.762622,
            'longitude' => 106.660172,
        ]);

        $driver2 = Driver::create([
            'user_id' => $driverUser2->id,
            'vehicle_type_id' => 1,
            'license_number' => '51X-67890',
            'license_expiry' => now()->addYears(3),
            'license_plate' => '51X-67890',
            'vehicle_color' => 'Đen',
            'vehicle_brand' => 'Honda',
            'vehicle_model' => 'Air Blade',
            'vehicle_year' => '2023',
            'vehicle_registration' => 'REG-002',
            'registration_expiry' => now()->addYears(2),
            'status' => 'online',
            'is_verified' => true,
            'latitude' => 10.775658,
            'longitude' => 106.700423,
        ]);

        // 4. Sample Rides
        Ride::create([
            'user_id' => $user1->id,
            'driver_id' => $driver1->id,
            'vehicle_type_id' => 2,
            'pickup_address' => '123 Lê Lợi, Quận 1, TP.HCM',
            'pickup_latitude' => 10.762622,
            'pickup_longitude' => 106.660172,
            'destination_address' => '456 Nguyễn Huệ, Quận 1, TP.HCM',
            'destination_latitude' => 10.775658,
            'destination_longitude' => 106.700423,
            'estimated_distance' => 3.5,
            'estimated_time' => 15,
            'base_fare' => 25000,
            'final_price' => 45000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'completed_at' => now()->subDays(2),
        ]);

        Ride::create([
            'user_id' => $user1->id,
            'driver_id' => null,
            'vehicle_type_id' => 1,
            'pickup_address' => '789 Cách Mạng Tháng 8, Quận 3, TP.HCM',
            'pickup_latitude' => 10.782785,
            'pickup_longitude' => 106.664294,
            'destination_address' => '101 Võ Văn Tần, Quận 3, TP.HCM',
            'destination_latitude' => 10.791234,
            'destination_longitude' => 106.667890,
            'estimated_distance' => 2.0,
            'estimated_time' => 10,
            'base_fare' => 15000,
            'final_price' => 25000,
            'status' => 'pending',
        ]);

        Ride::create([
            'user_id' => $user2->id,
            'driver_id' => $driver2->id,
            'vehicle_type_id' => 1,
            'pickup_address' => '15 Phan Đình Phùng, Quận Phú Nhuận, TP.HCM',
            'pickup_latitude' => 10.800123,
            'pickup_longitude' => 106.684567,
            'destination_address' => '28 Hoàng Văn Thụ, Quận Phú Nhuận, TP.HCM',
            'destination_latitude' => 10.812345,
            'destination_longitude' => 106.692345,
            'estimated_distance' => 2.5,
            'estimated_time' => 12,
            'base_fare' => 15000,
            'final_price' => 30000,
            'status' => 'in_progress',
        ]);

        // 5. Admin user
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@shipflash.com',
            'phone' => '0987654321',
        ]);
    }
}
