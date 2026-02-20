<?php

namespace Database\Migrations;

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
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('content');
            $table->string('ai_model', 100);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->enum('pricing_model', ['fixed', 'pay_what_you_want', 'free'])->default('fixed');
            $table->decimal('min_price', 10, 2)->nullable();
            $table->integer('version')->default(1);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('purchases_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->unsignedInteger('rating_count')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('seller_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('featured');
            $table->index('ai_model');
            $table->index('pricing_model');
            $table->fullText(['title', 'description'], 'prompt_search_index');
        });

        // Create prompt_tag pivot table
        Schema::create('prompt_tag', function (Blueprint $table) {
            $table->foreignId('prompt_id')->constrained('prompts')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->timestamps();
            $table->primary(['prompt_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_tag');
        Schema::dropIfExists('prompts');
    }
};
