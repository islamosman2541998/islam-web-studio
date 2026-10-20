<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\MethodologyStepResource;
use App\Http\Resources\Api\V1\TestimonialResource;
use App\Models\MethodologyStep;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestimonialController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Testimonial::published()->where('is_demo', false)->with(TestimonialResource::WITH)->orderByDesc('is_featured')->orderBy('sort_order');

        return TestimonialResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function methodology(): AnonymousResourceCollection
    {
        return MethodologyStepResource::collection(MethodologyStep::published()->orderBy('sort_order')->get());
    }
}
