<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // car, motorbike, bike, van
            $table->string('display_name'); // Ô tô 4 chỗ, Xe máy, Xe đạp, Van 7 chỗ
            $table->string('icon')->nullable();
            $table->decimal('base_fare', 10, 2); // giá cơ bản
            $table->decimal('per_km_rate', 10, 2); // giá mỗi km
            $table->decimal('per_minute_rate', 10, 2); // giá mỗi phút
            $table->decimal('min_fare', 10, 2); // giá tối thiểu
            $table->integer('max_passengers'); // số hành khách tối đa
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
