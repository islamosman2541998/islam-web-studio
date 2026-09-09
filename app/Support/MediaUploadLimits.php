<?php

namespace App\Support;

class MediaUploadLimits
{
    public const IMAGE_MAX_KILOBYTES = 5 * 1024;

    public const VIDEO_MAX_KILOBYTES = 60 * 1024;

    public const FILE_MAX_KILOBYTES = 50 * 1024;

    public const UPLOAD_MAX_KILOBYTES = self::VIDEO_MAX_KILOBYTES;

    public static function maxKilobytesForMime(string $mime): int
    {
        return match (true) {
            str_starts_with($mime, 'image/') => self::IMAGE_MAX_KILOBYTES,
            str_starts_with($mime, 'video/') => self::VIDEO_MAX_KILOBYTES,
            default => self::FILE_MAX_KILOBYTES,
        };
    }

    public static function exceedsLimit(string $mime, int $bytes): bool
    {
        return $bytes > self::maxKilobytesForMime($mime) * 1024;
    }

    public static function messageForMime(string $mime): string
    {
        return match (true) {
            str_starts_with($mime, 'image/') => Studio::text('media_image_too_large'),
            str_starts_with($mime, 'video/') => Studio::text('media_video_too_large'),
            default => Studio::text('media_file_too_large'),
        };
    }
}
