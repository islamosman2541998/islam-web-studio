<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageMedia extends StudioRecord
{
    protected $table = 'page_media';

    public array $translatable = ['caption'];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $entry): void {
            $entry->kind = match (Asset::kindOf($entry->media_id)) {
                'image' => 'gallery_image',
                'video' => 'video',
                'file' => 'file',
                default => $entry->kind,
            };
        });
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'media_id');
    }
}
