<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\PageDetailResource;
use App\Http\Resources\Api\V1\PageResource;
use App\Models\Page;
use App\Support\Studio;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PageController extends ApiController
{
    /** Published content pages (the home page is served by `/home`). */
    public function index(): AnonymousResourceCollection
    {
        return PageResource::collection(Page::published()->where('template', '!=', 'home')->with(PageResource::WITH)->orderBy('sort_order')->orderBy('id')->get());
    }

    public function show(string $page): PageDetailResource
    {
        return PageDetailResource::make($this->findPublished(Page::class, $page, PageDetailResource::WITH));
    }

    public function about(): PageDetailResource
    {
        $page = Page::published()->where('template', 'about')->with(PageDetailResource::WITH)->first() ?? abort(404, Studio::text('not_found'));

        return PageDetailResource::make($page);
    }
}
