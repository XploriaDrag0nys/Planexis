<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\MonthlyPerformance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PerformanceController extends Controller
{
    use AuthorizesRequests;

    public function show(Table $table)
    {
        $this->authorize('view', $table);

        \Carbon\Carbon::setLocale('fr');
        setlocale(LC_TIME, 'fr_FR.UTF-8');

        $performances = MonthlyPerformance::where('table_id', $table->id)
            ->orderBy('month', 'desc')
            ->take(12)
            ->get()
            ->sortBy('month')
            ->values();

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
                'month' => $month->translatedFormat('F Y'),
                'rate' => $match?->rate,
            ];
        });

        $target = $table->settings['global_target'] ?? 80;

        return view('tables.performance.graphique', [
            'table' => $table,
            'chartData' => $chartData,
            'target' => $target,
        ]);
    }
    public function performance(Table $table)
    {
        $performances = $table->monthlyPerformances()->orderBy('month')->take(12)->get();
        $target = $table->settings['global_target'] ?? 80;

        return view('tables.performance.graphique', compact('table', 'performances', 'target'));
    }
}
