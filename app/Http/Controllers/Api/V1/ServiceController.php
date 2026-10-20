<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Http\Resources\Api\V1\ServiceDetailResource;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Service::published()->with(ServiceResource::WITH);
        $this->applyListFilters($query, $request, 'name', 'service_category_id', ServiceCategory::class);

        return ServiceResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function show(string $service): ServiceDetailResource
    {
        /** @var Service $record */
        $record = $this->findPublished(Service::class, $service, ServiceDetailResource::WITH);
        $projects = $record->projects()->published()->with(ProjectResource::WITH)->orderByDesc('is_featured')->orderBy('sort_order')->limit(6)->get();

        return ServiceDetailResource::make($record)->additional(['related_projects' => ProjectResource::collection($projects)]);
    }

    public function categories(): AnonymousResourceCollection
    {
        return CategoryResource::collection(ServiceCategory::published()->orderBy('sort_order')->get());
    }
}
