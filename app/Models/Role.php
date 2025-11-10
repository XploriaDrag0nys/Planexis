<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Si vous n’avez pas besoin des timestamps, vous pouvez mettre public $timestamps = false;
    protected $fillable = ['name'];

    /**
     * Les utilisateurs liés à ce rôle, mais via la pivot user_table_roles.
     */
    public function users()
    {
        // On ne relie pas directement ici, on privilégiera le binding inverse dans User
        return $this->belongsToMany(User::class, 'user_table_roles', 'role_id', 'user_id')
                    ->withPivot('table_id')
                    ->withTimestamps();
    }
}
