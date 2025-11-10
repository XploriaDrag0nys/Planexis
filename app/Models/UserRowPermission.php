<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRowPermission extends Model
{
    protected $table = 'user_row_permissions';

    protected $fillable = [
        'user_id',
        'row_id',
        'can_edit',
    ];

    /**
     * L’utilisateur (contributeur) concerné.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * La ligne (table_rows) concernée.
     */
    public function row()
    {
        return $this->belongsTo(TableRow::class, 'row_id');
    }
}
