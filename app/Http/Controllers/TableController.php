<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; 
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\MonthlyPerformance;

class TableController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $search = $request->input('search');

        // on part toujours d’un Builder Eloquent « Table »
        $query = Table::with('projectManagers');

        if (! auth()->user()->isAdmin()) {
            // si pas admin, on filtre sur les tables où j’ai un pivot (chef ou contributeur)
            $query->whereHas(
                'userTableRoles',
                fn($q) =>
                $q->where('user_id', auth()->id())
            );
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas(
                        'projectManagers',
                        fn($q2) =>
                        $q2->where('name', 'like', "%{$search}%")
                    );
            });
        }

        $tables = $query->get();

        return view('tables.index', compact('tables', 'search'));
    }

    public function show(Table $table)
    {
        $this->authorize('view', $table);
        \Carbon\Carbon::setLocale('fr');
        setlocale(LC_TIME, 'fr_FR.UTF-8');
        $users = User::select('id', 'name', 'trigramme')->get();

        $performances = MonthlyPerformance::where('table_id', $table->id)
            ->orderBy('month', 'desc')
            ->take(12)
            ->get()
            ->sortBy('month')
            ->values();
        $isPm = auth()->user()->isAdmin()
            || $table->userTableRoles()
            ->whereHas('role', fn($q) => $q->where('name', 'Chef de projet'))
            ->where('user_id', auth()->id())
            ->exists();
        // Génère les 12 derniers mois (mois débutant le 1er de chaque mois)
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->copy()->subMonths($i)->startOfMonth());
        }

        // Associe chaque mois à sa performance, en comparant correctement avec isSameMonth
        $chartData = $months->map(function ($month) use ($performances) {
            $match = $performances->first(function ($perf) use ($month) {
                return \Carbon\Carbon::parse($perf->month)->isSameMonth($month);
            });

            return [
                'month' => ucfirst($month->isoFormat('MMM YY')),
                'rate' => $match?->rate,
            ];
        });

        $target = $table->settings['global_target'] ?? 80;

        $settings = $table->settings ?? [];
        $rows = $table->rows;
        $delays = collect($settings['priorities'] ?? [])->pluck('delay', 'label'); // ex: ['P0' => 0, 'P1' => 30]
        $globalTarget = $settings['global_target'] ?? 80;

        $now = Carbon::now();
        $twelveMonthsAgo = $now->copy()->subMonths(12);

        $filtered = $rows->filter(function ($row) use ($delays, $twelveMonthsAgo) {
            $data = is_string($row->data) ? json_decode($row->data, true) : $row->data;
            $priority = $data['Priorité'] ?? null;
            $status = $data['Status'] ?? null;
            $echeance = $data['Échéance planifié'] ?? null;

            // Exclure Best Effort (priorité sans délai)
            if (!isset($delays[$priority]))
                return false;

            // Si action est Terminée, on la garde (même au-delà de 12 mois)
            if ($status === 'Terminé')
                return true;

            // Sinon, uniquement si elle est dans les 12 derniers mois
            return $echeance && Carbon::parse($echeance)->gte($twelveMonthsAgo);
        });

        $conformes = $filtered->filter(function ($row) use ($delays) {
            $data = is_string($row->data) ? json_decode($row->data, true) : $row->data;
            $priority = $data['Priorité'] ?? null;
            $echeance = $data['Échéance planifié'] ?? null;

            if (!isset($delays[$priority]) || !$echeance)
                return false;

            $delay = $delays[$priority];
            $retard = Carbon::parse($echeance)->diffInDays(now(), false);

            return $retard <= $delay;
        });

        $performanceRate = $filtered->count() > 0
            ? round($conformes->count() / $filtered->count() * 100)
            : null;

        return view('tables.show', compact('chartData', 'target', 'table', 'performanceRate', 'globalTarget', 'users', 'isPm'));
    }

    public function create()
    {
        $this->authorize('create', Table::class);
        $users = \App\Models\User::select('id', 'name', 'trigramme')->get();
        return view('tables.create', compact('users'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Table::class);

        // ✅ Validation conditionnelle : pm_mode = existing | new
        $baseRules = [
            'name'       => 'required|string|max:255',
            'pattern_id' => 'required|exists:table_patterns,id',
            'pm_mode'    => 'required|in:existing,new',
        ];

        $existingRules = [
            'project_manager' => 'required|exists:users,id',
        ];

        $newRules = [
            'pm_name'  => 'required|string|max:255',
            'pm_email' => 'required|email:rfc,dns|unique:users,email',
        ];

        $rules = $baseRules + ($request->pm_mode === 'existing' ? $existingRules : $newRules);
        $data  = $request->validate($rules);

        // On va tout faire dans une transaction pour garder la DB cohérente
        $table = DB::transaction(function () use ($data, $request) {

            $pattern = \App\Models\TablePattern::findOrFail($request->pattern_id);

            // 1) Création du tableau
            $table = Table::create([
                'name'     => $data['name'],
                'user_id'  => auth()->id(),
                'columns'  => $pattern->columns,
                'settings' => [
                    'priorities' => [
                        ['label' => 'P0', 'delay' => 0],
                        ['label' => 'P1', 'delay' => 30],
                        ['label' => 'P2', 'delay' => 60],
                        ['label' => 'P3', 'delay' => null],
                    ],
                    'approach_thresholds' => [
                        'P0' => 30,
                        'P1' => 20,
                        'P2' => 10,
                    ],
                    'global_target' => 80,
                    'performance_targets' => [
                        'P0' => 90,
                        'P1' => 80,
                        'P2' => 70,
                    ],
                    'custom_statuses' => ['En pause', 'En cours'],
                    'sources' => [
                        'Analyse de risques',
                        'Audit de certification',
                        'Audit interne',
                        'Contrôle interne',
                        'Audit technique',
                        'Incident de sécurité',
                        'Opportunité d\'amélioration',
                    ],
                ],
            ]);

            // 2) Récup du role « Chef de projet »
            $pmRoleId = \DB::table('roles')->where('name', 'Chef de projet')->value('id');

            // 3) Selon le mode, déterminer l’utilisateur PM
            if ($data['pm_mode'] === 'existing') {
                $pmUserId = (int) $data['project_manager'];
            } else {
                // ➕ Création du nouvel utilisateur
                $tempPassword = Str::random(12);
                $user = User::create([
                    'name'       => $data['pm_name'],
                    'email'      => $data['pm_email'],
                    'password'   => Hash::make($tempPassword),
                    'invited_at' => now(),
                ]);

                // Génère un token de reset et envoie la notif d’invitation
                $token = Password::broker()->createToken($user);
                // Réutilise ta notif existante
                $user->notify(new \App\Notifications\InvitedUserNotification($token));

                $pmUserId = $user->id;
            }

            // 4) Attacher le PM au pivot avec le rôle Chef de projet
            \App\Models\UserTableRole::create([
                'user_id'  => $pmUserId,
                'table_id' => $table->id,
                'role_id'  => $pmRoleId,
            ]);

            return $table;
        });

        return redirect()->route('table.show', $table->id)
            ->with('success', 'Tableau créé avec succès');
    }

    public function edit(Table $table)
    {
        $this->authorize('update', $table);
        return view('tables.edit', compact('table'));
    }

    public function update(Request $request, Table $table)
    {
        $this->authorize('update', $table);
        // Update logic
    }

    public function destroy(Table $table)
    {
        $this->authorize('delete', $table);
        // Delete logic
    }
    public function refreshPerformance(Table $table)
    {
        $this->authorize('update', $table);

        $output = [];
        $returnVar = 0;

        exec('php ' . base_path('app/scripts/calculate_performance.php') . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            return back()->with('error', 'Échec de l’exécution du script : ' . implode("\n", $output));
        }

        return back()->with('success', 'Performance mise à jour manuellement.');
    }
    public function rename(Request $request, Table $table)
    {
        $this->authorize('rename', $table);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $table->name = $request->input('name');
        $table->save();

        return redirect()->route('table.show', $table)->with('success', 'Nom du projet mis à jour.');
    }
    public function destroyTable(Table $table)
    {
        $this->authorize('delete', $table);

        $table->delete();

        return redirect()->route('table.index')->with('success', 'Projet supprimé.');
    }
}
