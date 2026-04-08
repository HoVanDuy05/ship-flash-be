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
        Schema::create('ride_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['created', 'accepted', 'arriving', 'picked_up', 'in_progress', 'completed', 'cancelled']);
            $table->decimal('latitude', 10, 8)->nullable(); // vị trí hiện tại của xe
            $table->decimal('longitude', 11, 8)->nullable(); // vị trí hiện tại của xe
            $table->text('notes')->nullable(); // ghi chú của tài xế hoặc khách hàng
            $table->json('metadata')->nullable(); // additional data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_histories');
    }
};
