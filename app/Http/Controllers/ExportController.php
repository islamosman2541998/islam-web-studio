<?php

namespace App\Http\Controllers;

use App\Models\ExportRun;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function __invoke(ExportRun $export)
    {
        abort_unless(auth()->user()?->is_active && $export->user_id === auth()->id() && auth()->user()->can($export->module.'.export'), 403);
        abort_unless($export->status === 'completed' && $export->created_at->gt(now()->subDays(7)) && Storage::disk('local')->exists($export->file_path), 404);

        return Storage::disk('local')->download($export->file_path, $export->module.'-'.$export->id.'.xlsx', ['Cache-Control' => 'private, no-store']);
    }
}
