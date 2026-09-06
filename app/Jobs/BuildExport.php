<?php

namespace App\Jobs;

use App\Exports\RecordsExport;
use App\Models\ExportRun;
use App\Models\User;
use App\Support\StudioNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class BuildExport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 2;

    public function __construct(public int $exportId) {}

    public function handle(StudioNotifier $notifier): void
    {
        $run = ExportRun::findOrFail($this->exportId);
        $user = User::find($run->user_id);
        if (! $user?->is_active || ! $user->can($run->module.'.export')) {
            $run->update(['status' => 'failed', 'error' => 'Permission revoked']);
            $notifier->exportFailed($run);

            return;
        }
        $run->update(['status' => 'processing']);
        $file = 'exports/'.$run->id.'-'.Str::uuid().'.xlsx';
        Excel::store(new RecordsExport($run), $file, 'local', \Maatwebsite\Excel\Excel::XLSX);
        $run->update(['status' => 'completed', 'file_path' => $file, 'error' => null]);
        $notifier->exportCompleted($run);
    }

    public function failed(?\Throwable $exception): void
    {
        if ($run = ExportRun::find($this->exportId)) {
            $run->update(['status' => 'failed', 'error' => 'Export failed. Check the application log.']);
            app(StudioNotifier::class)->exportFailed($run);
        }
    }
}
