<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Support\MediaPipeline;
use App\Support\StudioNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateAssetConversions implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 240;

    public function __construct(public int $assetId) {}

    public function handle(MediaPipeline $pipeline): void
    {
        if ($asset = Asset::find($this->assetId)) {
            $pipeline->generateConversions($asset);
        }
    }

    public function failed(?\Throwable $error): void
    {
        if ($asset = Asset::find($this->assetId)) {
            $asset->updateQuietly(['metadata' => [...($asset->metadata ?? []), 'optimized' => false, 'error' => 'Image optimization failed. The original file is still available.']]);
            app(StudioNotifier::class)->assetFailed($asset);
        }
    }
}
