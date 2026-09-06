<?php

namespace App\Models;

use App\StudioRecord;

class MethodologyStep extends StudioRecord
{
    protected $table = 'methodology_steps';

    public array $translatable = ['title', 'description', 'deliverable'];
}
