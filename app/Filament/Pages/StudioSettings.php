<?php

namespace App\Filament\Pages;

use App\Support\FontRegistry;
use App\Support\MediaPicker;
use App\Support\SettingsRegistry;
use App\Support\Studio;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class StudioSettings extends Page
{
    protected string $view = 'filament.pages.settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?int $navigationSort = 80;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('settings.update');
    }

    public static function getNavigationLabel(): string
    {
        return Studio::text('settings');
    }

    public function getTitle(): string
    {
        return self::getNavigationLabel();
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return Studio::text('settings');
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);
        $data = [];
        foreach (SettingsRegistry::groups() as $group => $fields) {
            if ($group === 'scripts' && ! auth()->user()->hasRole('Owner')) {
                continue;
            }foreach ($fields as $key => $d) {
                $data[$group][$key] = Studio::setting($group.'.'.$key, $d[3]);
            }
        }$this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        $tabs = [];
        foreach (SettingsRegistry::groups() as $group => $fields) {
            if ($group === 'scripts' && ! auth()->user()->hasRole('Owner')) {
                continue;
            }$items = [];
            foreach ($fields as $key => [$type,$ar,$en,$default]) {
                $name = $group.'.'.$key;
                $label = app()->getLocale() === 'ar' ? $ar : $en;
                if ($type === 'translated') {
                    $items[] = Tabs::make($name)->label($label)->tabs([Tab::make('العربية')->schema([Textarea::make($name.'.ar')->label($ar)->maxLength(5000)->rows(3)->extraInputAttributes(['dir' => 'rtl'])]), Tab::make('English')->schema([Textarea::make($name.'.en')->label($en)->maxLength(5000)->rows(3)->extraInputAttributes(['dir' => 'ltr'])])])->columnSpanFull();

                    continue;
                }
                $field = match ($type) {
                    'bool' => Toggle::make($name),'color' => ColorPicker::make($name)->hex()->required(),'opacity' => Slider::make($name)->range(0, 100)->step(1)->tooltips()->fillTrack(),'asset' => MediaPicker::make($name, ['image']),'media_fit' => Select::make($name)->options(['cover' => app()->getLocale() === 'ar' ? 'ملء المساحة' : 'Cover', 'contain' => app()->getLocale() === 'ar' ? 'إظهار الصورة كاملة' : 'Contain'])->native(false)->required(),'theme' => Select::make($name)->options(['light' => Studio::text('light'), 'dark' => Studio::text('dark')]),'animation' => Select::make($name)->options(['fade' => 'Fade', 'pulse' => 'Pulse', 'draw' => 'Draw']),'arabic_font' => Select::make($name)->options(FontRegistry::arabicOptions())->native(false)->searchable(),'english_font' => Select::make($name)->options(FontRegistry::englishOptions())->native(false)->searchable(),'font_weight' => Select::make($name)->options(['500' => app()->getLocale() === 'ar' ? 'متوسط' : 'Medium', '600' => app()->getLocale() === 'ar' ? 'شبه عريض' : 'Semi bold', '700' => app()->getLocale() === 'ar' ? 'عريض' : 'Bold'])->native(false)->required(),'font_size' => Select::make($name)->options(['12' => '12 px', '13' => '13 px', '14' => '14 px'])->native(false)->required(),'code' => Textarea::make($name)->rows(8)->maxLength(20000)->helperText(Studio::text('scripts_warning'))->columnSpanFull(),'socials' => Repeater::make($name)->schema([TextInput::make('label')->required()->maxLength(40), TextInput::make('url')->url()->required()->maxLength(2048)])->columns(2)->defaultItems(0)->columnSpanFull(),default => TextInput::make($name)->maxLength(255)
                };
                if ($type === 'email') {
                    $field->email();
                }if ($type === 'duration') {
                    $field->integer()->minValue(100)->maxValue(1200);
                }if ($type === 'opacity') {
                    $field->helperText(Studio::text('opacity_help'));
                }if (in_array($type, ['phone', 'digits'])) {
                    $field->rules(['nullable', 'regex:/^[0-9]{5,20}$/']);
                }if ($type === 'ga') {
                    $field->rules(['nullable', 'regex:/^G-[A-Z0-9]+$/']);
                }if ($type === 'pixel') {
                    $field->rules(['nullable', 'regex:/^[A-Za-z0-9_-]+$/']);
                }if ($type === 'url') {
                    $field->maxLength(2048)->helperText(app()->getLocale() === 'ar' ? 'استخدم {locale} داخل الرابط لاستبدالها بلغة الصفحة، مثال: /{locale}/about' : 'Use {locale} in the URL to insert the current language, for example: /{locale}/about');
                }$items[] = $field->label($label);
            }$tabs[] = Tab::make(Studio::text($group))->schema($items)->columns(2);
        }

        return $schema->components([Tabs::make('settings')->tabs($tabs)->persistTabInQueryString()])->statePath('data');
    }

    public function save(): void
    {
        abort_unless(self::canAccess(), 403);
        $data = $this->form->getState();
        DB::transaction(function () use ($data) {
            foreach (SettingsRegistry::groups() as $group => $fields) {
                if ($group === 'scripts' && ! auth()->user()->hasRole('Owner')) {
                    continue;
                }foreach ($fields as $key => $d) {
                    if (array_key_exists($key, $data[$group] ?? [])) {
                        Studio::put($group.'.'.$key, $data[$group][$key], $group);
                    }
                }
            }
        });
        Studio::flush();
        Notification::make()->title(Studio::text('saved'))->success()->send();
        $this->js('setTimeout(() => window.location.reload(), 500)');
    }
}
