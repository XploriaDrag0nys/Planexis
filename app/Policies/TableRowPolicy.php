<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TableRow;

class TableRowPolicy
{
    public function update(User $user, TableRow $row)
    {
        return $user->isAdmin() || $user->isProjectManagerOf($row->table_id) || $user->canEditRow($row);
    }

    public function delete(User $user, TableRow $row)
    {
        return $user->isAdmin() || $user->isProjectManagerOf($row->table_id);
    }

}