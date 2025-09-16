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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('type', ['sale', 'rent', 'lease'])->default('sale');
            $table->enum('property_type', ['house', 'apartment', 'condo', 'townhouse', 'land', 'commercial', 'office'])->default('house');
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('area', 8, 2)->nullable(); // Square feet/meters
            $table->string('area_unit', 10)->default('sqft');
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('parking_spaces')->nullable();
            $table->year('year_built')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('US');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Property owner/agent
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');
            
            // Status and features
            $table->enum('status', ['active', 'pending', 'sold', 'rented', 'inactive'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('views')->default(0); // Page views counter
            $table->json('features')->nullable(); // Property features as JSON
            $table->json('amenities')->nullable(); // Available amenities
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            
            // SEO and meta
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            
            // Dates
            $table->timestamp('featured_until')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['status', 'is_featured', 'published_at']);
            $table->index(['type', 'property_type', 'city']);
            $table->index(['price', 'bedrooms', 'bathrooms']);
            $table->index(['latitude', 'longitude']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
