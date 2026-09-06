<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Support\MediaPipeline;
use App\Support\StudioNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAsset implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 240;

    public function __construct(public int $assetId) {}

    public function handle(MediaPipeline $pipeline): void
    {
        if ($asset = Asset::find($this->assetId)) {
            $pipeline->process($asset);
        }
    }

    public function failed(?\Throwable $error): void
    {
        if ($asset = Asset::find($this->assetId)) {
            $asset->updateQuietly(['metadata' => [...($asset->metadata ?? []), 'processed' => false, 'error' => 'Processing failed. Check the file and application log.']]);
            app(StudioNotifier::class)->assetFailed($asset);
        }
    }
}
