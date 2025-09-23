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
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('prompt_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned()->check('rating >= 1 AND rating <= 5');
            $table->string('title')->nullable();
            $table->text('review_text')->nullable();
            $table->boolean('verified_purchase')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'prompt_id']);
            $table->index(['prompt_id', 'rating']);
            $table->index(['user_id', 'prompt_id']);
            $table->index('is_approved');
            $table->index('created_at');
            $table->index('uuid');
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
