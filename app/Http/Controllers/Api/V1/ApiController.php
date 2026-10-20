<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\StudioRecord;
use App\Support\Studio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    public const DEFAULT_PER_PAGE = 12;

    public const MAX_PER_PAGE = 50;

    protected function perPage(Request $request): int
    {
        return max(1, min(self::MAX_PER_PAGE, (int) $request->query('per_page', self::DEFAULT_PER_PAGE)));
    }

    /**
     * A published record by numeric id or by its slug in either language.
     *
     * @param  class-string<StudioRecord>  $model
     * @param  array<int, string>  $with
     */
    protected function findPublished(string $model, string $key, array $with = []): StudioRecord
    {
        return $this->whereKeyOrSlug($model::published()->with($with), $key)->first()
            ?? abort(404, Studio::text('not_found'));
    }

    /** @param class-string<StudioRecord> $model */
    protected function resolveId(string $model, mixed $key): ?int
    {
        if (blank($key)) {
            return null;
        }

        return (int) ($this->whereKeyOrSlug($model::published(), (string) $key)->value('id') ?? 0);
    }

    protected function whereKeyOrSlug(Builder $query, string $key): Builder
    {
        if (ctype_digit($key)) {
            return $query->whereKey((int) $key);
        }

        return $query->where(fn (Builder $query) => $query->where('slug->ar', $key)->orWhere('slug->en', $key));
    }

    /**
     * Shared list filters: `search`, `category`, `featured` and `sort` (featured, latest, oldest, name).
     *
     * @param  class-string<StudioRecord>|null  $categoryModel
     */
    protected function applyListFilters(Builder $query, Request $request, string $titleField, ?string $categoryColumn = null, ?string $categoryModel = null, string $defaultSort = 'featured', string $dateColumn = 'created_at'): Builder
    {
        $locale = app()->getLocale();
        $search = trim(mb_substr((string) $request->query('search', ''), 0, 100));

        if ($search !== '') {
            $query->where($titleField.'->'.$locale, 'like', '%'.addcslashes($search, '%_\\').'%');
        }
        if ($categoryColumn && $categoryModel && $request->filled('category')) {
            $query->where($categoryColumn, $this->resolveId($categoryModel, $request->query('category')));
        }
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        return match (in_array($request->query('sort'), ['featured', 'latest', 'oldest', 'name'], true) ? $request->query('sort') : $defaultSort) {
            'latest' => $query->latest($dateColumn)->latest('id'),
            'oldest' => $query->oldest($dateColumn)->oldest('id'),
            'name' => $query->orderBy($titleField.'->'.$locale),
            default => $query->orderByDesc('is_featured')->orderBy('sort_order')->latest(),
        };
    }
}
