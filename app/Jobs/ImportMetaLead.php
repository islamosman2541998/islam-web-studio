<?php

namespace App\Jobs;

use App\Support\MetaAds;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportMetaLead implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public array $backoff = [15, 60, 180];

    public function __construct(public string $leadId, public array $webhook = []) {}

    public function handle(MetaAds $meta): void
    {
        $meta->importLead($this->leadId, $this->webhook);
    }
}
