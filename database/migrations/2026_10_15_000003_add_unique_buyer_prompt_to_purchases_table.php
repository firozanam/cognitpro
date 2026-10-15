<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pre-existing data can hold duplicates from the old check-then-create
        // race — deduplicate before the index, or the deploy aborts.
        $duplicates = DB::table('purchases')
            ->select('buyer_id', 'prompt_id', DB::raw('COUNT(*) as total'))
            ->groupBy('buyer_id', 'prompt_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Keep the completed row if any (the paid one), otherwise the newest.
            $keep = DB::table('purchases')
                ->where('buyer_id', $duplicate->buyer_id)
                ->where('prompt_id', $duplicate->prompt_id)
                ->orderByRaw("CASE WHEN status = 'completed' THEN 0 ELSE 1 END")
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->first();

            DB::table('purchases')
                ->where('buyer_id', $duplicate->buyer_id)
                ->where('prompt_id', $duplicate->prompt_id)
                ->where('id', '!=', $keep->id)
                ->delete();
        }

        Schema::table('purchases', function (Blueprint $table) {
            // One purchase row per buyer and prompt — pending stacking and
            // duplicate charges are prevented at the database level (DB-02).
            $table->unique(['buyer_id', 'prompt_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique(['buyer_id', 'prompt_id']);
        });
    }
};
