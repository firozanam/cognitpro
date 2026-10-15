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
        Schema::table('purchases', function (Blueprint $table) {
            // failPurchase (Stripe payment_intent.payment_failed webhook)
            // writes 'failed', which was not in the enum (DB-03).
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'disputed'])
                ->default('pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'refunded', 'disputed'])
                ->default('pending')
                ->change();
        });
    }
};
