<?php
use App\Models\MonthlyPerformance;
use App\Models\Table;
use Illuminate\Support\Carbon;

require __DIR__ . '/../../vendor/autoload.php';
date_default_timezone_set('Europe/Paris');
Carbon::setLocale('fr');
setlocale(LC_TIME, 'fr_FR.UTF-8');
file_put_contents('/var/log/perf_cron_debug.log', date('Y-m-d H:i:s') . " - Script lancé\n", FILE_APPEND);

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$now = Carbon::now();
$cutoff = $now->copy()->subMonths(12);

$tables = Table::all();

foreach ($tables as $table) {
    $settings = $table->settings ?? [];
    $rows = $table->rows;

    $delays = collect($settings['priorities'] ?? [])->pluck('delay', 'label');
    $priorityLabels = array_keys($delays->toArray());
    $bestEffortLabel = end($priorityLabels);

    $globalTarget = $settings['global_target'] ?? 80;

    $validRows = $rows->filter(function ($row) use ($cutoff, $bestEffortLabel) {
        $data = is_string($row->data) ? json_decode($row->data, true) : $row->data;
        $priority = trim($data['Priorité'] ?? '');
        $status = strtolower(trim($data['Status'] ?? ''));
        $createdAt = $row->created_at;

        if ($priority === $bestEffortLabel) return false;

        return $createdAt >= $cutoff || $status !== 'terminé';
    });

    $total = $validRows->count();
    $onTime = 0;

    foreach ($validRows as $row) {
        $data = is_string($row->data) ? json_decode($row->data, true) : $row->data;
        $priority = $data['Priorité'] ?? null;
        $deadline = $data['Échéance planifié'] ?? null;
        $completedAt = $row->updated_at ?? now();

        if (!$deadline || !isset($delays[$priority])) continue;

        $maxDelay = $delays[$priority];
        $lateDays = Carbon::parse($deadline)->diffInDays($completedAt, false);

        if ($lateDays <= $maxDelay) {
            $onTime++;
        }
    }

    $rate = $total > 0 ? round(($onTime / $total) * 100) : null;

    $table->performance_rate = $rate;
    $table->performance_color = $rate >= $globalTarget ? 'green' : 'red';
    $table->last_performance_update = now();
    $table->save();

}
