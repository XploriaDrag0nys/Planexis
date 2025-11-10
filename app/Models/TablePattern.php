<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TablePattern extends Model
{
    protected $table = 'table_patterns'; // ← Spécifie le nom réel de ta table

    protected $fillable = ['name', 'columns'];

    protected $casts = [
        'columns' => 'array',
    ];
}
