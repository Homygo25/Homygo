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
        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_url')->nullable(); // Full URL to the image
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->enum('type', ['image', 'video', 'virtual_tour'])->default('image');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable(); // In bytes
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false); // Main property image
            $table->timestamps();

            $table->index(['property_id', 'sort_order']);
            $table->index(['property_id', 'is_primary']);
            $table->index(['type', 'property_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_images');
    }
};
