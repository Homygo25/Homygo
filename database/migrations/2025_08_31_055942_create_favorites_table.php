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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('favoritable'); // Property, Car, Contractor, etc.
            $table->text('notes')->nullable(); // User's private notes about this favorite
            $table->json('tags')->nullable(); // User-defined tags for organization
            $table->timestamps();

            // Ensure one favorite per user per item
            $table->unique(['user_id', 'favoritable_type', 'favoritable_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
