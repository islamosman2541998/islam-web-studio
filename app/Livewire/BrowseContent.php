<?php

namespace App\Livewire;

use App\Models\PostCategory;
use App\Models\ProjectCategory;
use App\Models\ServiceCategory;
use App\Support\ModuleRegistry;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BrowseContent extends Component
{
    use WithPagination;

    #[Locked]
    public string $module;

    #[Locked]
    public string $locale;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: 'newest')]
    public string $sort = 'newest';

    public function mount(string $module, ?int $categoryId = null): void
    {
        abort_unless(in_array($module, ['services', 'projects', 'posts']), 404);
        $this->module = $module;
        $this->locale = app()->getLocale();
        if ($categoryId) {
            $this->category = (string) $categoryId;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        app()->setLocale($this->locale);
        $class = ModuleRegistry::model($this->module);
        $title = ModuleRegistry::get($this->module)['title'];
        $categoryField = match ($this->module) {
            'services' => 'service_category_id','projects' => 'project_category_id','posts' => 'post_category_id'
        };
        $categoryClass = match ($this->module) {
            'services' => ServiceCategory::class,'projects' => ProjectCategory::class,'posts' => PostCategory::class
        };
        $with = match ($this->module) {
            'services' => ['imageMedia', 'serviceCategory'],'projects' => ['mainMedia', 'mainMediaPoster', 'projectCategory', 'metrics', 'gallery.media'],'posts' => ['featuredMedia', 'postCategory']
        };
        $query = $class::published($this->locale)->with($with)->when($this->search, fn ($q) => $q->where($title.'->'.$this->locale, 'like', '%'.mb_substr($this->search, 0, 100).'%'))->when(ctype_digit($this->category), fn ($q) => $q->where($categoryField, (int) $this->category))->orderByDesc('is_featured');
        if ($this->sort === 'name') {
            $query->orderBy($title.'->'.$this->locale);
        } elseif ($this->sort === 'oldest') {
            $query->orderBy('created_at');
        } else {
            $query->orderBy('sort_order')->orderByDesc('created_at');
        }

return view('livewire.browse-content', ['records' => $query->paginate(9), 'categories' => $categoryClass::published($this->locale)->orderBy('sort_order')->get()]);
    }
}
