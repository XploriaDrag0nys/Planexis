<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Table;

class TablePolicy
{
    public function view(User $user, Table $table)
    { {
            if ($user->isAdmin()) {
                return true;
            }

            return $user->tableRoles()
                ->where('table_id', $table->id)
                ->exists();
        }
    }

    public function update(User $user, Table $table)
    {
        return $user->isAdmin() || $user->isProjectManagerOf($table);
    }

    public function delete(User $user, Table $table)
    {
        return $user->isAdmin();
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function createRow(User $user, Table $table)
    {
        return $user->isAdmin() || $user->isProjectManagerOf($table);
    }
    public function rename(User $user, Table $table): bool
    {
        return $user->isAdmin();
    }
    public function invite(User $user, Table $table)
    {

        return $user->isAdmin() || $user->isProjectManagerOf($table);
    }
    public function manageUsers(User $user, Table $table): bool
    {

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isProjectManagerOf($table);
    }
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isProjectManager();
    }
}
