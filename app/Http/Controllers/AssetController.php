<?php

namespace App\Http\Controllers;

use App\Models\Asset;

class AssetController extends Controller
{
    public function __invoke(Asset $asset)
    {
        abort_unless(auth()->user()?->is_active && auth()->user()->can('assets.view'), 403);
        $media = $asset->original();
        abort_unless($media, 404);

        return response()->download($media->getPath(), $media->file_name, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
