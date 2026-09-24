<?php

use App\Models\VisitorSession;
use App\Support\Studio;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('meta-ads:sync --days=7')->hourly()->withoutOverlapping();
Schedule::command('meta-ads:sync-leads --days=90')->everyFiveMinutes()->withoutOverlapping();
Schedule::call(function (): void {
    $days = max(30, min(730, (int) Studio::setting('analytics.retention_days', 180)));
    VisitorSession::query()->where('last_seen_at', '<', now()->subDays($days))->delete();
})->name('analytics:prune')->dailyAt('03:20')->withoutOverlapping();
