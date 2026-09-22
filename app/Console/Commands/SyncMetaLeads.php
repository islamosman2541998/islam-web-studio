<?php

namespace App\Console\Commands;

use App\Support\MetaAds;
use Illuminate\Console\Command;

class SyncMetaLeads extends Command
{
    protected $signature = 'meta-ads:sync-leads {--days=90 : Number of days to refresh}';

    protected $description = 'Synchronize leads from Meta instant forms';

    public function handle(MetaAds $meta): int
    {
        if (! $meta->leadsConfigured()) {
            $this->error('Meta Page credentials are incomplete.');

            return self::FAILURE;
        }

        $count = $meta->syncLeads(max(1, min(365, (int) $this->option('days'))));
        $this->info("Synchronized {$count} Meta lead records.");

        return self::SUCCESS;
    }
}
