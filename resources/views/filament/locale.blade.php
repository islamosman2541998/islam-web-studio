<div class="studio-topbar-tools">
    <a class="studio-topbar-tool" href="{{ route('admin.locale',['locale'=>app()->getLocale()==='ar'?'en':'ar','back'=>request()->getPathInfo()]) }}" lang="{{ app()->getLocale()==='ar'?'en':'ar' }}">
        <x-filament::icon icon="heroicon-o-language" class="studio-topbar-tool__icon" />
        <span>{{ app()->getLocale()==='ar'?'English':'العربية' }}</span>
    </a>
    <a class="studio-topbar-tool studio-site-link" href="{{ route('home',['locale'=>app()->getLocale()]) }}" target="_blank" rel="noopener">
        <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="studio-topbar-tool__icon" />
        <span>{{ \App\Support\Studio::text('view_site') }}</span>
    </a>
</div>
