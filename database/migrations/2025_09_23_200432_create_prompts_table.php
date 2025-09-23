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
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('price_type', ['fixed', 'pay_what_you_want', 'free'])->default('fixed');
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('minimum_price', 10, 2)->nullable()->default(5.00);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index('price_type');
            $table->index('status');
            $table->index('featured');
            $table->index('published_at');
            $table->index('uuid');
            $table->fullText(['title', 'description', 'excerpt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompts');
    }
};
