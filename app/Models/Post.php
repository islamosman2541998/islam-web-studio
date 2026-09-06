<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends StudioRecord
{
    protected $table = 'posts';

    public array $translatable = ['title', 'slug', 'excerpt', 'content', 'meta_title', 'meta_description', 'keywords'];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'featured_media_id');
    }

    public function postCategory(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'og_image_id');
    }
}
