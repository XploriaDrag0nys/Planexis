<?php

use App\Models\Table;
use App\Models\MonthlyPerformance;
use Illuminate\Support\Carbon;

require __DIR__ . '/../../vendor/autoload.php';

// Initialisation de Laravel
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Récupère toutes les tables
$tables = Table::all();

foreach ($tables as $table) {
    // Récupère la performance actuelle de la table
    $rate = $table->performance_rate ?? null;

    // Enregistre le mois courant
    $month = now()->startOfMonth();

    // Vérifier si l'enregistrement du mois existe déjà
    $exists = MonthlyPerformance::where('table_id', $table->id)
        ->where('month', $month)
        ->exists();

    if (!$exists) {
        // alors calculer et enregistrer
        MonthlyPerformance::updateOrCreate(
            ['table_id' => $table->id, 'month' => $month],
            ['rate' => $rate]
        );
    }

    // Supprimer les mois trop anciens (garder les 12 derniers)
    //MonthlyPerformance::where('table_id', $table->id)
    //  ->orderBy('month', 'desc')
    //->skip(12)
    //->take(PHP_INT_MAX)
    //->delete();
}
