<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('property_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('viewed_at');
            $table->integer('time_spent')->nullable(); // seconds
            $table->enum('device_type', ['desktop', 'tablet', 'mobile'])->default('desktop');
            $table->json('location_data')->nullable(); // Geographic data if available
            $table->timestamps();

            // Indexes for performance
            $table->index(['property_id', 'viewed_at']);
            $table->index(['user_id', 'viewed_at']);
            $table->index(['ip_address', 'viewed_at']);
            $table->index('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_views');
    }
};
