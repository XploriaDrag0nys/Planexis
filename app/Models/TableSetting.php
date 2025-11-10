<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableSetting extends Model
{
    protected $fillable = [
        'table_id', 'priorities', 'approach_thresholds',
        'global_target', 'target_p0', 'target_p1', 'target_p2',
        'custom_statuses', 'sources'
    ];

    protected $casts = [
        'priorities' => 'array',
        'approach_thresholds' => 'array',
        'custom_statuses' => 'array',
        'sources' => 'array',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
