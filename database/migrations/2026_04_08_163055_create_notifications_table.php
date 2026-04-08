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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable'); // user hoặc driver
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['ride_request', 'ride_accepted', 'ride_cancelled', 'ride_completed', 'payment_received', 'system', 'promotion'])->default('system');
            $table->string('data')->nullable(); // JSON data
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('push_token')->nullable(); // FCM token
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
