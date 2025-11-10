<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\TableRow;
use App\Models\UserTableRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class TableRowController extends Controller
{
    use AuthorizesRequests;
    public function create(Table $table)
    {
        $this->authorize('createRow', $table);
        // create form view
    }

    public function store(Request $request, Table $table)
    {
        $this->authorize('createRow', $table);

        // 1) on récupère et nettoie les data
        $data = array_map(fn($v) => is_null($v) ? '' : $v, $request->input('data', []));

        // 2) on crée la ligne
        $row = $table->rows()->create([
            'data' => json_encode($data),
        ]);

        // 3) on gère les "Responsable"
        $responsables = $data['Responsable'] ?? [];
        // si c'est une chaîne json, on la décode, sinon on s'assure que c'est un array
        if (is_string($responsables)) {
            $decoded = json_decode($responsables, true);
            $responsables = is_array($decoded) ? $decoded : explode(',', $responsables);
        }

        if (!empty($responsables)) {
            // 4) on récupère les user_ids
            $userIds = User::whereIn('trigramme', $responsables)->pluck('id');

            // 5) on lie la ligne à ses contributeurs
            $row->contributors()->sync($userIds);

            // 6) on s'assure que chacun est contributeur de la table
            $contribRoleId = DB::table('roles')->where('name', 'Contributeur')->value('id');
            foreach ($userIds as $uid) {
                UserTableRole::firstOrCreate([
                    'user_id' => $uid,
                    'table_id' => $table->id,
                ], [
                    'role_id' => $contribRoleId,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
    public function edit(TableRow $row)
    {
        $this->authorize('update', $row);
        // edit view
    }

    public function update(Request $request, TableRow $row)
    {
        $this->authorize('update', $row);

        // 0) Qui peut tout modifier ?
        $user   = $request->user();
        $isPm   = $user->isAdmin() || $row->table
            ->userTableRoles()
            ->where('user_id', $user->id)
            ->whereHas('role', fn($q) => $q->where('name', 'Chef de projet'))
            ->exists();

        // 1) Récupère l'état actuel (array) et les entrées reçues
        $current  = is_array($row->data) ? $row->data : (json_decode($row->data, true) ?: []);
        $incoming = $request->input('data', []);

        // 2) Si contributeur : ne garder que ces clés
        if (! $isPm) {
            $allowed = ['Commentaire suivi', 'Status', 'Avancement', 'Échéance relle', 'Échéance réelle'];
            $incoming = array_intersect_key($incoming, array_flip($allowed));
        }

        // 3) Merge sans perdre les autres champs (incoming écrase current sur mêmes clés)
        //    array_replace garde les clés numériques et associatives telles quelles
        $merged = array_replace($current, $incoming);

        // 4) Normaliser "Responsable" côté data (tableau propre)
        if (array_key_exists('Responsable', $merged)) {
            $responsables = $merged['Responsable'];

            if (is_string($responsables)) {
                $decoded = json_decode($responsables, true);
                $responsables = is_array($decoded)
                    ? $decoded
                    : array_filter(array_map('trim', explode(',', $responsables)));
            } elseif (!is_array($responsables)) {
                $responsables = [];
            }

            // On stocke dans data sous forme de tableau
            $merged['Responsable'] = array_values(array_unique($responsables));
        }

        // 5) Sauvegarde (garde ton json_encode si ton modèle n’a pas de cast)
        $row->data = json_encode($merged, JSON_UNESCAPED_UNICODE);
        $row->save();

        // 6) Synchroniser les contributeurs selon "Responsable"
        $responsables = $merged['Responsable'] ?? [];
        if (!empty($responsables)) {
            $userIds = User::whereIn('trigramme', $responsables)->pluck('id');
            $row->contributors()->sync($userIds);

            $contribRoleId = DB::table('roles')->where('name', 'Contributeur')->value('id');
            foreach ($userIds as $uid) {
                UserTableRole::firstOrCreate(
                    ['user_id' => $uid, 'table_id' => $row->table_id],
                    ['role_id' => $contribRoleId]
                );
            }
        } else {
            $row->contributors()->detach();
        }

        return response()->json(['success' => true]);
    }

    public function destroy(TableRow $row)
    {
        $this->authorize('delete', $row);

        $row->delete();

        return response()->json(['success' => true]);
    }
}
