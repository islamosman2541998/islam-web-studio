<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        @if ($this->canManageTranslations())
            <div class="flex items-center gap-3">
                <x-filament::button type="submit" icon="heroicon-o-check" wire:loading.attr="disabled" wire:target="save">
                    {{ app()->getLocale() === 'ar' ? 'حفظ الترجمات' : 'Save translations' }}
                </x-filament::button>
                <span wire:loading wire:target="save">{{ \App\Support\Studio::text('saving') }}</span>
            </div>
        @endif
    </form>
</x-filament-panels::page>
