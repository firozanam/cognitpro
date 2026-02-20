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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('prompt_id')->constrained('prompts')->onDelete('cascade');
            $table->string('order_number', 50)->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('platform_fee', 10, 2);
            $table->decimal('seller_earnings', 10, 2);
            $table->string('payment_method', 50);
            $table->string('payment_id')->nullable();
            $table->enum('status', ['pending', 'completed', 'refunded', 'disputed'])->default('pending');
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index('buyer_id');
            $table->index('prompt_id');
            $table->index('order_number');
            $table->index('status');
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
