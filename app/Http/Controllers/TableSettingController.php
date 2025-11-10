<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Table;
use App\Models\TableSetting;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TableSettingController extends Controller
{
    use AuthorizesRequests;
    public function edit(Table $table)
    {
        $this->authorize('update', $table);

        $defaultSettings = [
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
            'target_p0' => 90,
            'target_p1' => 80,
            'target_p2' => 70,
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
        ];

        $settings = array_replace_recursive($defaultSettings, $table->settings ?? []);

        return view('tables.settings.edit', compact('table', 'settings'));
    }


    public function update(Request $request, Table $table)
    {
        $priorities = array_values($request->input('priorities', []));
       


        $performanceTargets = [];

        $inputTargets = $request->input('performance_targets', []);
        $inputValues = $request->input('performance_values', []);

        foreach ($inputTargets as $priority => $label) {
            if (!empty($label) && isset($inputValues[$priority])) {
                $performanceTargets[$label] = intval($inputValues[$priority]);
            }
        }


        $table->settings = [
            'priorities' => $priorities,
            'approach_thresholds' => $request->input('approach_thresholds', []),
            'global_target' => intval($request->input('global_target')),
            'performance_targets' => $performanceTargets,
            'custom_statuses' => array_values($request->input('custom_statuses', [])),
            'sources' => array_values($request->input('sources', [])),
        ];

        $table->save();

        return redirect()->route('table.show', $table)->with('success', 'Paramètres enregistrés avec succès.');
    }
}
