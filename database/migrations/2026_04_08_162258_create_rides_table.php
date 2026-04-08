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
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('vehicle_type_id')->nullable();
            $table->enum('status', ['pending', 'accepted', 'arriving', 'picked_up', 'in_progress', 'completed', 'cancelled'])->default('pending');

            // Pickup location
            $table->string('pickup_address');
            $table->decimal('pickup_latitude', 10, 8);
            $table->decimal('pickup_longitude', 11, 8);
            $table->string('pickup_note')->nullable();

            // Destination location
            $table->string('destination_address');
            $table->decimal('destination_latitude', 10, 8);
            $table->decimal('destination_longitude', 11, 8);
            $table->string('destination_note')->nullable();

            // Distance and time
            $table->decimal('estimated_distance', 8, 2); // in km
            $table->decimal('actual_distance', 8, 2)->nullable(); // in km
            $table->decimal('estimated_time', 8, 2); // in minutes
            $table->decimal('actual_time', 8, 2)->nullable(); // in minutes

            // Pricing
            $table->decimal('base_fare', 10, 2);
            $table->decimal('distance_fare', 10, 2)->default(0);
            $table->decimal('time_fare', 10, 2)->default(0);
            $table->decimal('waiting_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);

            // Payment
            $table->string('payment_method')->default('cash'); // cash, momo, zalopay, vnpay
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');

            // Timestamps
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Additional info
            $table->text('user_note')->nullable();
            $table->text('driver_note')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('cancellation_by')->nullable(); // user, driver, system

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
