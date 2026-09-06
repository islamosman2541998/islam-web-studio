<?php

namespace App\Models;

use App\StudioRecord;

class Redirect extends StudioRecord
{
    protected $table = 'redirects';

    public array $translatable = [];
}
