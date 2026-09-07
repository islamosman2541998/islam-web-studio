<?php

namespace App\Support;

use App\Filament\Forms\Components\MediaLibraryPicker;
use App\Jobs\GenerateAssetConversions;
use App\Models\Asset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class MediaPicker
{
    /** @param array<int, string>|string $types */
    public static function make(string $name, array|string $types = ['image', 'video', 'file']): MediaLibraryPicker
    {
        $types = self::normalizeTypes($types);

        return MediaLibraryPicker::make($name)
            ->mediaTypes($types)
            ->helperText(Studio::text('media_help'))
            ->rules([
                Rule::exists('assets', 'id')
                    ->whereNull('deleted_at')
                    ->where('visibility', 'public')
                    ->where('is_active', true)
                    ->whereIn('kind', $types),
            ]);
    }

    /** @param array<int, string> $types */
    public static function createAsset(array $data, array $types): Asset
    {
        $path = (string) ($data['upload_path'] ?? '');
        if ($path === '' || ! Storage::disk('local')->exists($path)) {
            throw ValidationException::withMessages(['upload_path' => Studio::text('media_file_required')]);
        }

        $mime = (string) (Storage::disk('local')->mimeType($path) ?: 'application/octet-stream');
        $kind = str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'video' : 'file');
        if (! in_array($kind, self::normalizeTypes($types), true)) {
            Storage::disk('local')->delete($path);
            throw ValidationException::withMessages(['upload_path' => Studio::text('media_type_not_allowed')]);
        }

        $nameAr = trim((string) ($data['name_ar'] ?? ''));
        $nameEn = trim((string) ($data['name_en'] ?? '')) ?: $nameAr;

        $asset = Asset::createQuietly([
            'name' => ['ar' => $nameAr, 'en' => $nameEn],
            'alt' => [
                'ar' => trim((string) ($data['alt_ar'] ?? '')) ?: $nameAr,
                'en' => trim((string) ($data['alt_en'] ?? '')) ?: $nameEn,
            ],
            'caption' => ['ar' => '', 'en' => ''],
            'kind' => $kind,
            'visibility' => 'public',
            'upload_path' => $path,
            'metadata' => ['processed' => false, 'mime' => $mime],
            'is_active' => true,
            'sort_order' => 0,
        ]);

        try {
            app(MediaPipeline::class)->ingest($asset);
            if ($asset->kind === 'image') {
                GenerateAssetConversions::dispatch($asset->id)->afterResponse();
            }
        } catch (Throwable $error) {
            report($error);
            Storage::disk('local')->delete($path);
            Asset::withoutEvents(fn () => $asset->forceDelete());

            throw ValidationException::withMessages(['upload_path' => Studio::text('media_processing_failed')]);
        }

        return $asset->refresh();
    }

    public static function optionHtml(?Asset $asset): ?string
    {
        if (! $asset) {
            return null;
        }

        $url = $asset->kind === 'image' ? $asset->imageUrl(320) : $asset->publicUrl();
        $preview = match (true) {
            $asset->kind === 'image' && filled($url) => '<img class="iws-media-option__preview" src="'.e($url).'" alt="" loading="lazy">',
            $asset->kind === 'video' && filled($url) => '<video class="iws-media-option__preview" src="'.e($url).'" muted playsinline preload="metadata" tabindex="-1"></video>',
            $asset->upload_path !== null => '<span class="iws-media-option__placeholder iws-media-option__placeholder--processing" aria-hidden="true">⟳</span>',
            $asset->kind === 'video' => '<span class="iws-media-option__placeholder" aria-hidden="true">▶</span>',
            default => '<span class="iws-media-option__placeholder" aria-hidden="true">⌁</span>',
        };
        $state = $asset->upload_path ? Studio::text('processing') : Studio::text($asset->kind);

        return '<span class="iws-media-option">'.$preview.'<span class="iws-media-option__copy"><strong>'.e($asset->titleText()).'</strong><small>'.e($state).' · #'.$asset->getKey().'</small></span></span>';
    }

    /** @param array<int, string> $types */
    public static function uploadForm(array $types): array
    {
        return [
            FileUpload::make('upload_path')
                ->label(Studio::text('media_file'))
                ->disk('local')
                ->directory('incoming')
                ->visibility('private')
                ->acceptedFileTypes(self::acceptedMimeTypes($types))
                ->maxSize(51200)
                ->previewable()
                ->openable(false)
                ->downloadable(false)
                ->required()
                ->columnSpanFull(),
            TextInput::make('name_ar')->label(Studio::text('media_name_ar'))->required()->maxLength(255),
            TextInput::make('name_en')->label(Studio::text('media_name_en'))->maxLength(255),
            Textarea::make('alt_ar')->label(Studio::text('media_alt_ar'))->rows(2)->maxLength(500),
            Textarea::make('alt_en')->label(Studio::text('media_alt_en'))->rows(2)->maxLength(500),
        ];
    }

    /** @param array<int, string> $types */
    public static function acceptedMimeTypes(array $types): array
    {
        $mimes = [];
        if (in_array('image', $types, true)) {
            $mimes = [...$mimes, 'image/jpeg', 'image/png', 'image/webp', 'image/avif'];
        }
        if (in_array('video', $types, true)) {
            $mimes = [...$mimes, 'video/mp4', 'video/webm', 'video/quicktime'];
        }
        if (in_array('file', $types, true)) {
            $mimes = [...$mimes, 'application/pdf', 'application/zip'];
        }

        return array_values(array_unique($mimes));
    }

    /** @return array<int, string> */
    public static function normalizeTypes(array|string $types): array
    {
        $types = is_string($types) ? explode(',', $types) : $types;

        return array_values(array_intersect(['image', 'video', 'file'], array_map('trim', $types))) ?: ['image', 'video', 'file'];
    }
}
