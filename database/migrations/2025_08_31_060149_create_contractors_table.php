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
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('services_offered');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->decimal('min_project_budget', 10, 2)->nullable();
            $table->decimal('max_project_budget', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->integer('years_experience')->nullable();
            $table->string('license_number')->nullable();
            $table->boolean('is_licensed')->default(false);
            $table->boolean('is_insured')->default(false);
            $table->string('insurance_provider')->nullable();
            $table->date('insurance_expiry')->nullable();
            
            // Business hours
            $table->json('business_hours')->nullable(); // Operating hours for each day
            $table->boolean('available_weekends')->default(false);
            $table->boolean('emergency_services')->default(false);
            
            // Location and service area
            $table->text('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('US');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('service_radius')->nullable(); // Miles/KM they will travel
            $table->json('service_areas')->nullable(); // Array of cities/regions they serve
            
            // Relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Contractor user
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');
            
            // Status and verification
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('background_checked')->default(false);
            $table->json('certifications')->nullable(); // Professional certifications
            $table->json('specializations')->nullable(); // Areas of expertise
            
            // Contact info
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('website')->nullable();
            
            // Portfolio and work samples
            $table->json('portfolio_images')->nullable();
            $table->text('previous_work_description')->nullable();
            
            // SEO and meta
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            // Dates
            $table->timestamp('featured_until')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['status', 'is_featured', 'is_verified']);
            $table->index(['city', 'state', 'service_radius']);
            $table->index(['hourly_rate', 'min_project_budget']);
            $table->index(['user_id', 'status']);
            $table->index(['category_id', 'is_licensed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
