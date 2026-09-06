<?php

namespace App\Models;

use App\StudioRecord;

class Translation extends StudioRecord
{
    protected $table = 'translations';

    public array $translatable = ['value'];
}
