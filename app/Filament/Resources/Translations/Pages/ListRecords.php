<?php

namespace App\Filament\Resources\Translations\Pages;

use App\Filament\Resources\Translations\TranslationResource;
use App\Models\Translation;
use App\Support\ModuleRegistry;
use App\Support\Studio;
use App\Support\TranslationRegistry;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class ListRecords extends Page
{
    protected static string $resource = TranslationResource::class;

    protected string $view = 'filament.resources.translations.pages.manage-translations';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function getTitle(): string|Htmlable
    {
        return TranslationResource::getPluralModelLabel();
    }

    public function getSubheading(): ?string
    {
        return app()->getLocale() === 'ar'
            ? 'كل نصوص الموقع الثابتة مرتبة حسب الصفحة والسكشن.'
            : 'All fixed website copy, organized by page and section.';
    }

    public function mount(): void
    {
        abort_unless(TranslationResource::canViewAny(), 403);

        $records = Translation::query()
            ->withTrashed()
            ->whereIn('key', TranslationRegistry::keys())
            ->get()
            ->keyBy('key');
        $data = [];

        foreach (TranslationRegistry::groups() as $groupKey => $group) {
            foreach ($group['tabs'] as $tabKey => $tab) {
                foreach ($tab['fields'] as $translationKey => $definition) {
                    $record = $records->get($translationKey);
                    $values = $record?->storedValues() ?? [];
                    $stateKey = TranslationRegistry::stateKey($translationKey);

                    foreach (['ar', 'en'] as $locale) {
                        $data[$groupKey][$tabKey][$stateKey][$locale] = array_key_exists($locale, $values)
                            ? (string) $values[$locale]
                            : (string) trans('studio.'.$translationKey, [], $locale);
                    }
                }
            }
        }

        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        $scopeTabs = [];

        foreach (TranslationRegistry::groups() as $groupKey => $group) {
            $sectionTabs = [];

            foreach ($group['tabs'] as $tabKey => $tab) {
                $fields = [];

                foreach ($tab['fields'] as $translationKey => $definition) {
                    $stateKey = TranslationRegistry::stateKey($translationKey);
                    $component = ($definition['type'] ?? 'text') === 'textarea' ? Textarea::class : TextInput::class;

                    $arabic = $component::make($groupKey.'.'.$tabKey.'.'.$stateKey.'.ar')
                        ->label($definition['ar'].' — العربية')
                        ->maxLength(5000)
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->disabled(! $this->canManageTranslations());
                    $english = $component::make($groupKey.'.'.$tabKey.'.'.$stateKey.'.en')
                        ->label($definition['en'].' — English')
                        ->maxLength(5000)
                        ->extraInputAttributes(['dir' => 'ltr'])
                        ->disabled(! $this->canManageTranslations());

                    if ($component === Textarea::class) {
                        $arabic->rows(3);
                        $english->rows(3);
                    }

                    $fields[] = $arabic;
                    $fields[] = $english;
                }

                $sectionTabs[] = Tab::make($this->localized($tab['label']))
                    ->schema($fields)
                    ->columns(2);
            }

            $scopeTabs[] = Tab::make($this->localized($group['label']))
                ->icon($groupKey === 'home' ? 'heroicon-o-home' : 'heroicon-o-document-text')
                ->schema([
                    Tabs::make($groupKey.'-translation-sections')
                        ->tabs($sectionTabs),
                ]);
        }

        return $schema
            ->components([
                Tabs::make('translation-scope')
                    ->tabs($scopeTabs)
                    ->persistTabInQueryString('translation-scope'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless($this->canManageTranslations(), 403);

        $data = $this->form->getState();

        DB::transaction(function () use ($data): void {
            $records = Translation::query()
                ->withTrashed()
                ->whereIn('key', TranslationRegistry::keys())
                ->get()
                ->keyBy('key');

            foreach (TranslationRegistry::groups() as $groupKey => $group) {
                foreach ($group['tabs'] as $tabKey => $tab) {
                    foreach (array_keys($tab['fields']) as $translationKey) {
                        $stateKey = TranslationRegistry::stateKey($translationKey);
                        $values = $data[$groupKey][$tabKey][$stateKey] ?? [];
                        $record = $records->get($translationKey) ?? new Translation(['key' => $translationKey]);

                        if ($record->exists && $record->trashed()) {
                            $record->restore();
                        }

                        $record->value = [
                            'ar' => (string) ($values['ar'] ?? ''),
                            'en' => (string) ($values['en'] ?? ''),
                        ];
                        $record->is_active = true;
                        $record->save();
                    }
                }
            }
        });

        Studio::flush();

        Notification::make()
            ->title(app()->getLocale() === 'ar' ? 'تم حفظ الترجمات' : 'Translations saved')
            ->success()
            ->send();
    }

    public function canManageTranslations(): bool
    {
        return ModuleRegistry::permission('translations', 'update');
    }

    /** @param array{ar: string, en: string} $label */
    private function localized(array $label): string
    {
        return $label[app()->getLocale()] ?? $label['en'];
    }
}
