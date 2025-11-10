<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyPerformance extends Model
{
    protected $table = 'monthly_performance';
    protected $fillable = ['table_id', 'month', 'rate'];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
