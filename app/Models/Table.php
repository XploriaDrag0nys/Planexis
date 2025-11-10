<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    public function projectManagers()
    {
        $chefRoleId = DB::table('roles')->where('name', 'Chef de projet')->value('id');

        return $this
            ->belongsToMany(User::class, 'user_table_roles', 'table_id', 'user_id')
            ->wherePivot('role_id', $chefRoleId);
    }
    public function contributors()
    {
        $cRoleId = DB::table('roles')
            ->where('name', 'Contributeur')
            ->value('id');

        return $this->belongsToMany(User::class, 'user_table_roles')
            ->wherePivot('role_id', $cRoleId)
            ->withPivot('role_id');
    }
    protected $fillable = [
        'name',
        'user_id', // L'utilisateur qui a créé le tableau
        'columns', // Les colonnes du tableau, stockées en JSON
        'settings', // Les paramètres du tableau, stockés en JSON
    ];
    protected $casts = [
        'settings' => 'array',
        'columns' => 'array',
        // si vous avez d’autres champs JSON, ajoutez-les ici
    ];

    /**
     * Les utilisateurs liés à ce tableau (via pivot user_table_roles),
     * avec leur rôle (via pivot.role_id).
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_table_roles', 'table_id', 'user_id')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * L’ensemble des “pivots” UserTableRole pour ce tableau.
     */
    public function userTableRoles()
    {
        return $this->hasMany(UserTableRole::class, 'table_id');
    }

    /**
     * Toutes les lignes (actions) de ce tableau.
     */
    public function rows()
    {
        return $this->hasMany(TableRow::class, 'table_id');
    }
    public function removeResponsibleFromAllRows(string $trigramme)
    {
        $this->rows->each(function (TableRow $row) use ($trigramme) {
            // décode le JSON des données

            $raw = $row->data;
            if (is_string($raw)) {
                $data = json_decode($raw, true) ?: [];
            } elseif (is_array($raw)) {
                $data = $raw;
            } else {
                $data = [];
            }


            // normalise le champ Responsable en tableau
            $responsables = [];
            if (!empty($data['Responsable'])) {
                if (is_array($data['Responsable'])) {
                    $responsables = $data['Responsable'];
                } elseif (is_string($data['Responsable'])) {
                    $responsables = array_filter(array_map('trim', explode(',', $data['Responsable'])));
                }
            }

            // filtre pour enlever celui qu'on a détaché
            $filtered = array_values(array_filter($responsables, fn($tri) => $tri !== $trigramme));

            // remet à jour le JSON de la ligne
            $data['Responsable'] = $filtered;
            $row->data = json_encode($data);
            $row->save();
        });
    }
    public function projectManager()
    {
        return $this->projectManagers()->limit(1);
    }
}
