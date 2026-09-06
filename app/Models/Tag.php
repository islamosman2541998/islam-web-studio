<?php

namespace App\Models;

use App\StudioRecord;

class Tag extends StudioRecord
{
    protected $table = 'tags';

    public array $translatable = ['name', 'slug'];
}
