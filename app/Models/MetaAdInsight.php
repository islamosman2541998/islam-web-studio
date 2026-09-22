<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaAdInsight extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'impressions' => 'integer',
            'reach' => 'integer',
            'clicks' => 'integer',
            'link_clicks' => 'integer',
            'leads' => 'integer',
            'spend' => 'decimal:2',
            'cpc' => 'decimal:4',
            'cpm' => 'decimal:4',
            'ctr' => 'decimal:4',
            'cost_per_lead' => 'decimal:4',
        ];
    }
}
