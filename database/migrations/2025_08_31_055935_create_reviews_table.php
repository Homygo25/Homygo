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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Reviewer
            $table->morphs('reviewable'); // Property, Car, Contractor, etc.
            $table->foreignId('reviewed_user_id')->nullable()->constrained('users')->onDelete('cascade'); // User being reviewed
            $table->integer('rating')->unsigned(); // 1-5 stars
            $table->string('title')->nullable();
            $table->text('comment');
            $table->json('pros')->nullable(); // Array of positive points
            $table->json('cons')->nullable(); // Array of negative points
            $table->boolean('is_verified')->default(false); // Verified purchase/interaction
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('helpful_count')->default(0); // How many found this helpful
            $table->timestamps();

            // Ensure one review per user per item
            $table->unique(['user_id', 'reviewable_type', 'reviewable_id']);
            $table->index(['reviewable_type', 'reviewable_id', 'is_approved']);
            $table->index(['reviewed_user_id', 'rating']);
            $table->index(['rating', 'is_approved', 'created_at']);
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
