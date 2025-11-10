<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\User;
use App\Notifications\InvitedUserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InviteController extends Controller
{
    public function create(Table $table)
    {
        $this->authorize('invite', $table);

        $existing = User::select('id', 'name', 'email', 'trigramme')
            ->where('is_admin', false)
            ->get();
        return view('tables.invite', compact('table', 'existing'));
    }

    public function store(Request $request, Table $table)
    {
        $this->authorize('invite', $table);

        // Valide selon le mode choisi
        if ($request->input('mode') === 'existing') {
            $data = $request->validate([
                'mode'    => 'required|in:existing,email',
                'user_id' => 'required|exists:users,id',
            ]);
            // Lier en tant que contributeur
            $contribRole = \DB::table('roles')->where('name', 'Contributeur')->value('id');
            \App\Models\UserTableRole::create([
                'user_id'  => $data['user_id'],
                'table_id' => $table->id,
                'role_id'  => $contribRole,
            ]);

            return back()->with('success', 'Utilisateur existant ajouté comme contributeur.');
        }
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        // 1) création du user avec un mot de passe temporaire
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make(Str::random(12)),
            'invited_at'  => now(),
        ]);

        $roleId = \DB::table('roles')->where('name', 'Contributeur')->value('id');
        $table->users()->attach($user->id, ['role_id' => $roleId]);

        // 3) générer le token de reset (expire dans 60 minutes)
        $token = Password::broker()->createToken($user);

        // 4) envoyer la notification
        $user->notify(new InvitedUserNotification($token));

        return redirect()
            ->route('table.show', $table)
            ->with('success', 'Invitation envoyée à ' . $user->email);
    }
}
