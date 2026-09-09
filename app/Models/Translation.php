<?php

namespace App\Models;

use App\StudioRecord;

class Translation extends StudioRecord
{
    protected $table = 'translations';

    public array $translatable = ['value'];

    /** @return array<string, string> */
    public function storedValues(): array
    {
        $raw = $this->getRawOriginal('value');
        $values = is_string($raw) ? json_decode($raw, true) : $raw;

        if (! is_array($values)) {
            return [];
        }

        return collect($values)
            ->mapWithKeys(fn (mixed $value, mixed $locale): array => [(string) $locale => (string) ($value ?? '')])
            ->all();
    }
}
