<?php

namespace App\Support;

use App\Models\Asset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaPipeline
{
    public function process(Asset $asset): void
    {
        if ($asset->upload_path) {
            $this->ingest($asset);
            $asset->refresh();
        }

        $this->generateConversions($asset);
    }

    /** Move a validated upload into the library before expensive optimization runs. */
    public function ingest(Asset $asset): void
    {
        $source = $asset->upload_path;
        if (! $source || ! str_starts_with($source, 'incoming/') || str_contains($source, '..') || ! Storage::disk('local')->exists($source)) {
            throw new \RuntimeException('The uploaded media file is missing.');
        }

        $file = Storage::disk('local')->path($source);
        $mime = (string) mime_content_type($file);
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'video/mp4', 'video/webm', 'video/quicktime', 'application/pdf', 'application/zip', 'application/x-zip-compressed'];
        if (! in_array($mime, $allowed, true)) {
            throw new \RuntimeException('Unsupported media type or file size.');
        }

        $kind = str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'video' : 'file');
        if (MediaUploadLimits::exceedsLimit($mime, (int) filesize($file))) {
            throw new \RuntimeException('The uploaded media exceeds its allowed size.');
        }

        $dimensions = null;
        if ($kind === 'image') {
            $dimensions = getimagesize($file);
            if (! $dimensions || $dimensions[0] * $dimensions[1] > 30_000_000) {
                throw new \RuntimeException('Image dimensions exceed the safe processing limit.');
            }
        }

        $this->deleteConversions($asset);
        $disk = $asset->visibility === 'public' ? 'public' : 'local';
        $media = $asset->addMedia($file)
            ->preservingOriginal()
            ->usingFileName(Str::uuid().'.'.match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/avif' => 'avif',
                'video/mp4' => 'mp4',
                'video/webm' => 'webm',
                'video/quicktime' => 'mov',
                'application/pdf' => 'pdf',
                default => 'zip',
            })
            ->toMediaCollection('original', $disk);

        $metadata = [
            ...Arr::except($asset->metadata ?? [], ['webp', 'avif', 'lqip', 'error']),
            'mime' => $mime,
            'bytes' => $media->size,
            'processed' => true,
            'optimized' => $kind !== 'image',
        ];
        if ($dimensions) {
            $metadata['width'] = $dimensions[0];
            $metadata['height'] = $dimensions[1];
        }

        $asset->forceFill(['kind' => $kind, 'metadata' => $metadata, 'upload_path' => null])->saveQuietly();
        Storage::disk('local')->delete($source);
        Studio::flush();
    }

    public function generateConversions(Asset $asset): void
    {
        if ($asset->kind !== 'image' || ($asset->metadata['optimized'] ?? false)) {
            return;
        }

        $media = $asset->original();
        if (! $media || ! is_file($media->getPath())) {
            throw new \RuntimeException('The original image is unavailable.');
        }

        $disk = $asset->visibility === 'public' ? 'public' : 'local';
        $manager = new ImageManager(new Driver);
        $image = $manager->decodePath($media->getPath());
        $this->deleteConversions($asset);
        $folder = 'responsive/'.$asset->id.'/'.Str::random(12);
        Storage::disk($disk)->makeDirectory($folder);
        $conversions = ['webp' => [], 'avif' => []];

        foreach (array_unique([min(320, $image->width()), min(640, $image->width()), min(960, $image->width()), min(1440, $image->width()), min(1920, $image->width())]) as $width) {
            $scaled = (clone $image)->scaleDown(width: $width);
            foreach (['webp', 'avif'] as $format) {
                if ($format === 'avif' && ! function_exists('imageavif')) {
                    continue;
                }
                $relative = $folder.'/'.$width.'.'.$format;
                $scaled->encodeUsingFileExtension($format, quality: $format === 'avif' ? 62 : 80)->save(Storage::disk($disk)->path($relative));
                $conversions[$format][(string) $width] = $relative;
            }
        }

        $small = (clone $image)->scaleDown(width: 24);
        $metadata = [
            ...Arr::except($asset->metadata ?? [], ['webp', 'avif', 'lqip', 'error']),
            'webp' => $conversions['webp'],
            'avif' => $conversions['avif'],
            'lqip' => 'data:image/webp;base64,'.base64_encode((string) $small->encodeUsingFileExtension('webp', quality: 30)),
            'optimized' => true,
        ];
        $asset->forceFill(['metadata' => $metadata])->saveQuietly();
        Studio::flush();
    }

    private function deleteConversions(Asset $asset): void
    {
        $disk = $asset->visibility === 'public' ? 'public' : 'local';
        foreach (['webp', 'avif'] as $format) {
            $paths = array_values($asset->metadata[$format] ?? []);
            if ($paths) {
                Storage::disk($disk)->delete($paths);
            }
        }
    }
}
