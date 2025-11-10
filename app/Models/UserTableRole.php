<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTableRole extends Model
{
    protected $table = 'user_table_roles';

    protected $fillable = [
        'user_id',
        'table_id',
        'role_id',
    ];

    /**
     * L’utilisateur concerné par ce pivot.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Le tableau concerné.
     */
    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    /**
     * Le rôle (Role) associé.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
