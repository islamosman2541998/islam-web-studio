<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slide extends StudioRecord
{
    protected $table = 'slides';

    public array $translatable = ['title', 'description', 'button_text'];

    public function slider(): BelongsTo
    {
        return $this->belongsTo(Slider::class, 'slider_id');
    }

    public function desktopMedia(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'desktop_media_id');
    }

    public function desktopPoster(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'desktop_poster_id');
    }

    public function mobileMedia(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'mobile_media_id');
    }

    public function mobilePoster(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'mobile_poster_id');
    }
}
