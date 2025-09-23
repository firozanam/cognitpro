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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('prompt_id')->constrained()->onDelete('cascade');
            $table->decimal('price_paid', 10, 2);
            $table->string('payment_gateway', 50);
            $table->string('transaction_id');
            $table->json('metadata')->nullable();
            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'prompt_id']);
            $table->index('user_id');
            $table->index('prompt_id');
            $table->index('transaction_id');
            $table->index('payment_gateway');
            $table->index('purchased_at');
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
