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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // người đánh giá
            $table->foreignId('driver_id')->constrained()->onDelete('cascade'); // tài xế được đánh giá
            $table->decimal('rating', 3, 2); // 1.00 - 5.00
            $table->text('comment')->nullable();
            $table->enum('type', ['user_to_driver', 'driver_to_user'])->default('user_to_driver');
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
