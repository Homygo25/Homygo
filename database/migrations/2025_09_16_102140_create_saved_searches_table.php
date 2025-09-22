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
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->json('search_criteria'); // Stores search parameters
            $table->boolean('email_alerts')->default(false);
            $table->enum('alert_frequency', ['immediate', 'daily', 'weekly', 'monthly'])->default('daily');
            $table->timestamp('last_alert_sent')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('results_count')->default(0);
            $table->timestamp('last_results_check')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'is_active']);
            $table->index(['email_alerts', 'is_active']);
            $table->index('last_alert_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
