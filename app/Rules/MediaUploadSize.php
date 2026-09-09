<?php

namespace App\Rules;

use App\Support\MediaUploadLimits;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class MediaUploadSize implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $mime = (string) $value->getMimeType();
        $clientMime = (string) $value->getClientMimeType();
        if (! str_starts_with($mime, 'image/') && ! str_starts_with($mime, 'video/')) {
            $mime = $clientMime ?: $mime;
        }
        $bytes = (int) ($value->getSize() ?: 0);

        if (MediaUploadLimits::exceedsLimit($mime, $bytes)) {
            $fail(MediaUploadLimits::messageForMime($mime));
        }
    }
}
