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
        Schema::create('property_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->enum('inquiry_type', [
                'general', 
                'viewing_request', 
                'price_inquiry', 
                'financing', 
                'availability'
            ])->default('general');
            $table->enum('status', ['pending', 'responded', 'closed'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('source')->nullable(); // website, mobile_app, etc.
            $table->timestamps();

            // Indexes for performance
            $table->index(['property_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('status');
            $table->index('inquiry_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_inquiries');
    }
};
