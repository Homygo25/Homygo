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
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', [
                'property_alert',
                'price_drop',
                'new_listing',
                'inquiry_response',
                'favorite_update',
                'system'
            ]);
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional structured data
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable(); // URL for click action
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
            $table->index(['priority', 'created_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
