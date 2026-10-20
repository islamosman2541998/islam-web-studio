<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ProjectDetailResource;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends ApiController
{
    /** Also filters by `service` (id or slug). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Project::published()->with(ProjectResource::WITH);
        $this->applyListFilters($query, $request, 'title', 'project_category_id', ProjectCategory::class);

        if ($request->filled('service')) {
            $serviceId = $this->resolveId(Service::class, $request->query('service'));
            $query->whereHas('services', fn ($services) => $services->whereKey($serviceId));
        }

        return ProjectResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function show(string $project): ProjectDetailResource
    {
        /** @var Project $record */
        $record = $this->findPublished(Project::class, $project, ProjectDetailResource::WITH);
        $related = Project::published()
            ->whereKeyNot($record->id)
            ->with(ProjectResource::WITH)
            ->when($record->project_category_id, fn ($query) => $query->orderByRaw('CASE WHEN project_category_id = ? THEN 0 ELSE 1 END', [$record->project_category_id]))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return ProjectDetailResource::make($record)->additional(['related' => ProjectResource::collection($related)]);
    }

    public function categories(): AnonymousResourceCollection
    {
        return CategoryResource::collection(ProjectCategory::published()->orderBy('sort_order')->get());
    }
}
