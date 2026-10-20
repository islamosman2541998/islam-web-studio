<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\PostDetailResource;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends ApiController
{
    /** Also filters by `tag` (id or slug). Newest first unless `sort` is given. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Post::published()->with(PostResource::WITH);
        $this->applyListFilters($query, $request, 'title', 'post_category_id', PostCategory::class, defaultSort: 'latest', dateColumn: 'published_at');

        if ($request->filled('tag')) {
            $tag = (string) $request->query('tag');
            $query->whereHas('tags', fn (Builder $tags) => $tags->where('is_active', true)->where(fn (Builder $match) => ctype_digit($tag)
                ? $match->whereKey((int) $tag)
                : $match->where('slug->ar', $tag)->orWhere('slug->en', $tag)));
        }

        return PostResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function show(string $post): PostDetailResource
    {
        /** @var Post $record */
        $record = $this->findPublished(Post::class, $post, PostDetailResource::WITH);
        $related = Post::published()->whereKeyNot($record->id)->with(PostResource::WITH)->latest('published_at')->limit(3)->get();

        return PostDetailResource::make($record)->additional(['related' => PostResource::collection($related)]);
    }

    public function categories(): AnonymousResourceCollection
    {
        return CategoryResource::collection(PostCategory::published()->orderBy('sort_order')->get());
    }

    public function tags(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Tag::query()->where('is_active', true)->orderBy('sort_order')->get()->filter(fn (Tag $tag): bool => filled($tag->titleText()))->values());
    }
}
