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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable(); // Profile picture path
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('role', ['buyer', 'seller', 'agent', 'contractor', 'admin'])->default('buyer');
            $table->string('company_name')->nullable();
            $table->string('website')->nullable();
            $table->string('license_number')->nullable(); // For agents/contractors
            $table->text('address')->nullable();
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->json('social_links')->nullable(); // Facebook, Twitter, LinkedIn, etc.
            $table->json('preferences')->nullable(); // User preferences and settings
            $table->timestamps();

            $table->index(['role', 'is_verified']);
            $table->index(['location_id', 'is_featured']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
