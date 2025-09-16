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
        Schema::create('search_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // User-defined name for the saved search
            $table->enum('type', ['property', 'car', 'contractor']); // What type of listing
            $table->json('filters'); // All search criteria as JSON
            $table->json('location_filters')->nullable(); // Geographic filters
            $table->decimal('min_price', 12, 2)->nullable();
            $table->decimal('max_price', 12, 2)->nullable();
            $table->boolean('email_alerts')->default(false); // Send new listing alerts
            $table->enum('alert_frequency', ['immediate', 'daily', 'weekly'])->default('daily');
            $table->timestamp('last_alert_sent')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'type', 'is_active']);
            $table->index(['email_alerts', 'alert_frequency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_filters');
    }
};
