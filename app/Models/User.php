<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\UserTableRole;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{

    use HasFactory, Notifiable, SoftDeletes;

    // ---------------------------------------------------------
    // 1. Attributs à rendre “fillable” ou “casts”
    // ---------------------------------------------------------
    protected $fillable = [
        'name',
        'email',
        'password',
        'trigramme',
        // ...
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'invited_at' => 'datetime',
    ];

    // ---------------------------------------------------------
    // 2. Relation vers les rôles contextuels sur les tableaux
    // ---------------------------------------------------------
    /**
     * Toutes les entrées (pivot) liant cet utilisateur à un rôle sur un tableau précis.
     * Il s’agit d’un “hasMany” vers la table user_table_roles.
     */
    public function userTableRoles()
    {
        return $this->hasMany(UserTableRole::class);
    }

    /**
     * Pour récupérer directement les tableaux (tables) auxquels l’utilisateur est lié,
     * avec en plus le rôle (via pivot.role_id).
     */
    public function tables()
    {
        // Modèle “Table” ↔ pivot user_table_roles (clé étrangère: user_id, table_id)
        return $this->belongsToMany(Table::class, 'user_table_roles', 'user_id', 'table_id')
            ->withPivot('role_id')
            ->withTimestamps();
    }



    public function isContributor(): bool
    {
        return $this->tableRoles()
            ->whereHas('role', fn($q) => $q->where('name', 'Contributeur'))
            ->exists();
    }
    /**
     * Les permissions sur des lignes (table_rows) : 
     * un User peut avoir plusieurs UserRowPermission.
     */
    public function rowPermissions()
    {
        return $this->hasMany(UserRowPermission::class, 'user_id');
    }

    // ---------------------------------------------------------
    // 3. Méthodes auxiliaires pour connaître le rôle d’un user sur un tableau
    // ---------------------------------------------------------
    /**
     * Indique si cet utilisateur est admin global.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
    public function isProjectManager(): bool
    {
        return $this->tableRoles()
            ->whereHas('role', function ($q) {
                $q->where('name', 'Chef de projet');
            })->exists();
    }

    /**
     * Récupère le rôle (Role) d’un utilisateur pour un tableau donné.
     * Retourne null si pas lié.
     */
    public function roleOnTable(int $tableId): ?Role
    {
        // On récupère la ligne pivot dans user_table_roles
        $utr = $this->userTableRoles()
            ->where('table_id', $tableId)
            ->first();

        if (!$utr) {
            return null;
        }

        return Role::where('id', $utr->role_id)->first();
    }

    /**
     * Indique si le user est chef de projet (project_manager) sur le tableau id = $tableId.
     */
    public function isProjectManagerOf($table): bool
    {
        $tableId = is_object($table) ? $table->id : $table;

        return $this->tableRoles()
            ->where('table_id', $tableId)
            ->whereHas('role', fn($q) => $q->where('name', 'Chef de projet'))
            ->exists();
    }
    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class);
    }

    public function tableRoles()
    {
        return $this->hasMany(\App\Models\UserTableRole::class);
    }

    /**
     * Indique si le user est contributeur (contributor) sur le tableau id = $tableId.
     */
    public function isContributorOf(int $tableId): bool
    {
        $role = $this->roleOnTable($tableId);
        return $role && $role->name === 'contributor';
    }

    /**
     * Indique si le contributeur a le droit d’éditer la ligne (row_id).
     * (Admin et Project Manager sont autorisés par ailleurs.)
     */
    public function canEditRow($row): bool
    {
        $rowId = is_object($row) ? $row->id : $row;

        return $this->tableRowPermissions()
            ->where('row_id', $rowId)
            ->exists();
    }
    public function tableRowPermissions()
    {
        return $this->belongsToMany(
            \App\Models\TableRow::class,
            'user_row_permissions', // nom de la table pivot
            'user_id',
            'row_id'
        );
    }
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->trigramme)) {
                $base = self::generateBaseTrigramme($user->name);
                $trigramme = $base;
                $i = 1;

                while (self::where('trigramme', $trigramme)->exists()) {
                    $trigramme = $base . $i++;
                }

                $user->trigramme = $trigramme;
            }
        });
        static::deleting(function (User $user) {
            $user->name  = 'Utilisateur supprimé, id : ' . $user->id; 
            $user->email = 'Utilisateur supprimé, id : ' . $user->id; 
          
            $user->save();
        });
    }

    protected static function generateBaseTrigramme($fullName)
    {
        $parts = preg_split('/\s+/', trim($fullName));
        $prenom = strtoupper(substr($parts[0] ?? '', 0, 1));
        $nom = strtoupper(substr($parts[1] ?? $parts[0] ?? '', 0, 2));
        return $prenom . $nom;
    }

}
