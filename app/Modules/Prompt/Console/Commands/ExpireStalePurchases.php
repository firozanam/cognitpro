<?php

namespace App\Modules\Prompt\Console\Commands;

use App\Modules\Prompt\Models\Purchase;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Fail pending purchases abandoned at checkout so retries are not
 * blocked by the unique buyer/prompt constraint (CFG-13).
 */
class ExpireStalePurchases extends Command
{
    protected $signature = 'purchases:expire-stale';

    protected $description = 'Fail pending purchases older than 24 hours';

    public function handle(): int
    {
        $expired = Purchase::query()
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subDay())
            ->update(['status' => 'failed']);

        $this->info(sprintf('%d stale %s expired.', $expired, Str::plural('purchase', $expired)));

        return self::SUCCESS;
    }
}
