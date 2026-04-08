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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_type_id')->nullable();

            // Driver info
            $table->string('license_number')->unique();
            $table->date('license_expiry');
            $table->string('license_front')->nullable(); // image URL
            $table->string('license_back')->nullable(); // image URL

            // Vehicle info
            $table->string('license_plate')->unique();
            $table->string('vehicle_brand');
            $table->string('vehicle_model');
            $table->string('vehicle_color');
            $table->string('vehicle_year');
            $table->string('vehicle_registration')->unique();
            $table->date('registration_expiry');
            $table->string('vehicle_front')->nullable(); // image URL
            $table->string('vehicle_back')->nullable(); // image URL

            // Location tracking
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('last_location_update')->nullable();

            // Status
            $table->enum('status', ['offline', 'online', 'busy', 'break'])->default('offline');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();

            // Stats
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('total_rides')->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->integer('completed_rides')->default(0);
            $table->integer('cancelled_rides')->default(0);

            // Preferences
            $table->boolean('accepts_cash')->default(true);
            $table->boolean('accepts_electronic')->default(true);
            $table->json('preferred_areas')->nullable(); // JSON array of preferred districts
            $table->integer('max_distance')->default(20); // max distance in km

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
