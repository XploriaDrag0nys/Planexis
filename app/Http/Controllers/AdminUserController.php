<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Table;
use App\Models\TableRow;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'users');
        $search    = $request->input('search', '');
        $qPlans    = $request->input('q', '');
        $user      = auth()->user();
        $isAdmin   = $user->isAdmin();

        $users = User::when($search, function ($q) use ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('trigramme', 'like', "%{$search}%");
            });
        })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $tablesQuery = Table::with(['projectManagers', 'contributors']);

        if ($isAdmin) {
        } else {
            $tablesQuery->whereHas('projectManagers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });

        }

        $tablesQuery->when($qPlans, function ($query) use ($qPlans) {
            $query->where(function ($qq) use ($qPlans) {
                $qq->where('name', 'like', "%{$qPlans}%")
                    ->orWhereHas('projectManagers', function ($q2) use ($qPlans) {
                        $q2->where('name', 'like', "%{$qPlans}%")
                            ->orWhere('trigramme', 'like', "%{$qPlans}%");
                    })
                    ->orWhereHas('contributors', function ($q3) use ($qPlans) {
                        $q3->where('name', 'like', "%{$qPlans}%")
                            ->orWhere('trigramme', 'like', "%{$qPlans}%");
                    });
            });
        });

        $tables = $tablesQuery
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $allUsers = User::select('id', 'name', 'trigramme')->orderBy('name')->get();

        return view('users.index', [
            'users'     => $users,
            'search'    => $search,
            'tables'    => $tables,
            'qPlans'    => $qPlans,
            'activeTab' => $activeTab ?: ($isAdmin ? 'users' : 'plans'),
            'allUsers'  => $allUsers,
        ]);
    }

    public function promote(User $user)
    {
        $user->is_admin = '1';
        $user->save();
        return back()->with('success', "L’utilisateur « {$user->trigramme} » a été promu administrateur.");
    }

    public function destroy(Table $table, User $user)
    {

        // 1) On détache le user du projet

        $table->users()->detach($user->id);

        $table->removeResponsibleFromAllRows($user->trigramme);

        return back()->with('success', "L’utilisateur {$user->name} a été retiré du projet et de toutes les lignes.");
    }
    public function delete(User $user)
    {

        $user->delete();
        return back()->with('success', "L’utilisateur « {$user->trigramme} » a été supprimé et anonymisé.");
    }
    public function addPm(Request $request, Table $table)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $pmRoleId = Role::where('name', 'Chef de projet')->value('id');

        // évite lepliage en double
        $table->projectManagers()->syncWithoutDetaching([
            $request->user_id => ['role_id' => $pmRoleId]
        ]);

        return back()->with('success', 'Chef·fe de projet ajouté·e.');
    }

    public function removePm(Table $table, User $user)
    {

        $table->projectManagers()->detach($user->id);

        return back()->with('success', 'Chef·fe de projet retiré·e.');
    }
}
