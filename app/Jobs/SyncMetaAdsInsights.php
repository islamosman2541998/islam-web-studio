<?php

namespace App\Jobs;

use App\Support\MetaAds;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncMetaAdsInsights implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $uniqueFor = 900;

    public function __construct(public int $days = 30) {}

    public function handle(MetaAds $meta): void
    {
        $meta->syncInsights($this->days);
    }
}
