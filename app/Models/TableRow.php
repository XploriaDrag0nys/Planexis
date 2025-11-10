<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableRow extends Model
{
    protected $table = 'table_rows'; // si votre table s’appelle bien “table_rows”
    protected $fillable = [
        'table_id',
        'data',       // ou tout autre champ qui contient vos informations
        // etc.
    ];
    protected $casts = [
        'data' => 'array',
    ];


    /**
     * Le tableau parent de cette ligne.
     */
    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    /**
     * Les permissions accordées aux contributeurs sur cette ligne.
     */
    public function userPermissions()
    {
        return $this->hasMany(UserRowPermission::class, 'row_id');
    }
    public function contributors()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'user_row_permissions', // nom de la table pivot
            'row_id',
            'user_id'
        );
    }
}
