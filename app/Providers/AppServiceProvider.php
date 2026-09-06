<?php

namespace App\Providers;

use App\Http\Middleware\SetLocale;
use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        foreach (config('studio.modules', []) as $definition) {
            Gate::policy('App\\Models\\'.$definition['model'], 'App\\Policies\\'.$definition['model'].'Policy');
        }
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Blade::directive('t', fn ($expression) => '<?php echo e(\\App\\Support\\Studio::text('.$expression.')); ?>');
        Livewire::addPersistentMiddleware([SetLocale::class]);
    }
}
