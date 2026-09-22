<?php

namespace App\Console\Commands;

use App\Support\MetaAds;
use Illuminate\Console\Command;

class SyncMetaAds extends Command
{
    protected $signature = 'meta-ads:sync {--days=30 : Number of days to refresh}';

    protected $description = 'Synchronize Meta Ads performance metrics';

    public function handle(MetaAds $meta): int
    {
        if (! $meta->configured()) {
            $this->error('Meta Ads credentials are incomplete.');

            return self::FAILURE;
        }

        $count = $meta->syncInsights(max(1, min(90, (int) $this->option('days'))));
        $this->info("Synchronized {$count} daily ad insight rows.");

        return self::SUCCESS;
    }
}
