<?php

namespace App\Support;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Validation\Rule;

class ResourceSchema
{
    public static function make(string $module, array $exclude = []): array
    {
        $fields = ModuleRegistry::get($module)['fields'];
        $tabs = [];
        $shared = [];
        foreach (['ar' => 'العربية', 'en' => 'English'] as $locale => $label) {
            $items = [];
            foreach ($fields as $name => $d) {
                if (($d['translated'] ?? false) && ! in_array($name, $exclude)) {
                    $items[] = self::field($module, $name, $d, $locale);
                }
            }
            if ($items) {
                $tabs[] = Tab::make($label)->schema($items)->extraAttributes(['dir' => $locale === 'ar' ? 'rtl' : 'ltr']);
            }
        }
        foreach ($fields as $name => $d) {
            if (! ($d['translated'] ?? false) && ! in_array($name, [...$exclude, 'metadata', 'ids', 'columns', 'file_path', 'error'])) {
                $shared[] = self::field($module, $name, $d);
            }
        }
        $result = [];
        if ($tabs) {
            $result[] = Tabs::make('languages')->tabs($tabs)->columnSpanFull()->persistTabInQueryString();
        }
        if ($shared) {
            $result[] = Section::make(Studio::text('publishing_settings'))->schema($shared)->columns(2);
        }
        $children = match ($module) {
            'services' => [['features', 'service_features', 'service_id'], ['packages', 'service_packages', 'service_id']], 'projects' => [['gallery', 'project_media', 'project_id'], ['metrics', 'project_metrics', 'project_id']], 'pages' => [['gallery', 'page_media', 'page_id']], 'sliders' => [['slides', 'slides', 'slider_id']], default => []
        };
        foreach ($children as [$relation,$child,$foreign]) {
            $result[] = Repeater::make($relation)->label(Studio::text($relation))->relationship($relation)->orderColumn('sort_order')->schema(self::make($child, [$foreign, 'sort_order']))->collapsible()->defaultItems(0)->addActionLabel(Studio::text('add_item'))->columnSpanFull();
        }
        if ($module === 'projects') {
            $result[] = Select::make('services')->label(ModuleRegistry::label('services'))->relationship('services', 'name')->getOptionLabelFromRecordUsing(fn ($record) => $record->titleText())->multiple()->searchable()->preload();
        }
        if ($module === 'posts') {
            $result[] = Select::make('tags')->label(ModuleRegistry::label('tags'))->relationship('tags', 'name')->getOptionLabelFromRecordUsing(fn ($record) => $record->titleText())->multiple()->searchable()->preload();
        }

        return $result;
    }

    public static function field(string $module, string $name, array $d, ?string $locale = null): mixed
    {
        $path = $name.($locale ? '.'.$locale : '');
        $type = $d['type'];
        $field = match ($type) {
            'textarea' => Textarea::make($path)->rows(4)->maxLength(20000),
            'rich' => RichEditor::make($path)->toolbarButtons(['bold', 'italic', 'underline', 'link', 'h2', 'h3', 'bulletList', 'orderedList', 'blockquote', 'undo', 'redo'])->columnSpanFull(),
            'boolean' => Toggle::make($path),
            'select' => Select::make($path)->options(collect($d['options'])->mapWithKeys(fn ($v) => [$v => Studio::text($v)])->all())->live(),
            'relation' => Select::make($path)->options(fn () => ModuleRegistry::options($d['model']))->searchable()->preload(),
            'media' => MediaPicker::make($path, $d['types'] ?? 'image,video,file'),
            'datetime' => DateTimePicker::make($path)->seconds(false)->timezone('Africa/Cairo'),
            'color' => ColorPicker::make($path)->hex(),
            'upload' => MediaPicker::uploadField($path),
            'tags' => TagsInput::make($path),
            'route' => Select::make($path)->options(fn () => MenuResolver::routeOptions())->searchable(),
            'dynamic_items' => Select::make($path)->multiple()->searchable()->options(fn (Get $get) => ($source = $get('dynamic_source')) && isset(ModuleRegistry::all()[$source]) ? ModuleRegistry::options(ModuleRegistry::get($source)['model'], true) : []),
            default => TextInput::make($path)->maxLength(255),
        };
        $field->label(Studio::text('field_'.$name))->required($d['required'] ?? false);
        if (array_key_exists('default', $d)) {
            $field->default($d['default']);
        }
        if (in_array($type, ['integer', 'decimal'])) {
            $field->numeric()->minValue(0);
            if ($type === 'integer') {
                $field->integer();
            }
        }
        if ($type === 'email') {
            $field->email();
        }
        if ($type === 'url') {
            $field->url()->maxLength(2048);
        }
        if ($type === 'link') {
            $field->rules(['regex:~^(https?://|/(?!/))~i'])->maxLength(2048);
        }
        if ($type === 'slug') {
            $field->rules(['regex:/^[\pL\pN]+(?:[-_][\pL\pN]+)*$/u'])->unique(table: ModuleRegistry::model($module), column: 'slug_'.$locale, ignoreRecord: true)->helperText(Studio::text('slug_help'));
        }
        if ($type === 'key') {
            $field->rules(['regex:/^[a-z0-9_.-]+$/'])->unique(ignoreRecord: true);
        }
        if ($type === 'relation') {
            $field->rules([Rule::exists((new ('App\\Models\\'.$d['model']))->getTable(), 'id')->whereNull('deleted_at')]);
        }
        if ($name === 'rating') {
            $field->minValue(1)->maxValue(5);
        }
        if ($name === 'overlay_opacity') {
            $field->maxValue(80);
        }
        if ($name === 'autoplay_speed') {
            $field->minValue(2500)->maxValue(60000);
        }
        if ($name === 'dynamic_limit') {
            $field->minValue(1)->maxValue(50);
        }
        if ($name === 'visibility' && $module === 'assets') {
            $field->disabledOn('edit')->helperText(Studio::text('visibility_help'));
        }
        if ($name === 'upload_path') {
            $field->required(fn (string $operation) => $operation === 'create');
        }
        if ($module === 'menu_items') {
            $visible = match ($name) {
                'route_name' => ['route'],'url' => ['external'],'page_id' => ['page'],'dynamic_source','dynamic_mode','dynamic_items','dynamic_category_id','dynamic_featured','dynamic_limit' => ['dynamic_group'],default => null
            };
            if ($visible) {
                $field->visible(fn (Get $get) => in_array($get('type'), $visible));
            }
            if (in_array($name, ['route_name', 'url', 'page_id', 'dynamic_source'])) {
                $field->required();
            }
            if ($name === 'dynamic_items') {
                $field->visible(fn (Get $get) => $get('type') === 'dynamic_group' && $get('dynamic_mode') === 'selected')->required();
            }
            if ($name === 'dynamic_category_id') {
                $field->helperText(Studio::text('category_id_help'));
            }
            if ($name === 'dynamic_source') {
                $field->afterStateUpdated(fn (Set $set) => $set('dynamic_items', []));
            }
        }

        return $field;
    }
}
