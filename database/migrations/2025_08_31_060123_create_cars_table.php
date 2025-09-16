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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('make'); // Brand (Toyota, Ford, etc.)
            $table->string('model'); // Model name
            $table->year('year');
            $table->string('trim')->nullable(); // Trim level (LX, EX, etc.)
            $table->enum('condition', ['new', 'used', 'certified_pre_owned'])->default('used');
            $table->integer('mileage')->nullable();
            $table->string('mileage_unit')->default('miles');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('exterior_color')->nullable();
            $table->string('interior_color')->nullable();
            $table->enum('transmission', ['manual', 'automatic', 'cvt'])->nullable();
            $table->enum('fuel_type', ['gasoline', 'diesel', 'hybrid', 'electric', 'flex_fuel'])->nullable();
            $table->string('engine_size')->nullable();
            $table->integer('doors')->nullable();
            $table->enum('body_type', ['sedan', 'suv', 'truck', 'coupe', 'convertible', 'wagon', 'hatchback', 'van'])->nullable();
            $table->enum('drivetrain', ['fwd', 'rwd', 'awd', '4wd'])->nullable();
            $table->string('vin', 17)->nullable()->unique();
            
            // Location
            $table->text('address')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('US');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Car seller
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');
            
            // Status and features
            $table->enum('status', ['active', 'pending', 'sold', 'inactive'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->json('features')->nullable(); // Car features as JSON
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            
            // SEO and meta
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            // Dates
            $table->timestamp('featured_until')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['status', 'is_featured', 'published_at']);
            $table->index(['make', 'model', 'year']);
            $table->index(['price', 'mileage', 'condition']);
            $table->index(['city', 'state']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
