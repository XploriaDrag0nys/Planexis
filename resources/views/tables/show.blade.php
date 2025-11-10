@extends('template')

@section('title', 'Gestion du Tableau')

@section('content')
    
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/2.3.1/css/dataTables.dataTables.css" />
        <link rel="stylesheet" href="{{ asset('css/show.css') }}">
    @endpush
    @if(isset($chartData) && count($chartData))
        <div class="container-fluid mt-4">
            <div class="card p-4">
                <canvas id="performanceChart" height="200"></canvas>
                @if(!is_null($table->performance_rate))
                    <div
                        class="alert alert-{{ $table->performance_color === 'green' ? 'success' : 'danger' }} d-flex align-items-center justify-content-between flex-wrap gap-3">


                        <div class="flex-grow-1 text-center">
                            🎯 Performance globale :
                            <strong>{{ $table->performance_rate }}%</strong>
                        </div>


                        <form action="{{ route('table.refreshPerformance', $table->id) }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-dark">
                                🔄
                            </button>
                        </form>
                    </div>


                @else
                    <div class="alert alert-secondary">
                        ⚠️ Pas assez d’actions disponible pour évaluer la performance.
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="container-fluid mt-4">


        <div class="d-flex align-items-center mb-4">
            {{-- partie gauche : titre ou rename --}}
            <div class="flex-grow-1">
                @can('rename', $table)
                    <form action="{{ route('table.rename', $table) }}"
                        method="POST"
                        class="input-group input-group-lg"
                        style="max-width:400px;">
                        @csrf
                        @method('PATCH')
                        <input type="text"
                            name="name"
                            value="{{ $table->name }}"
                            class="form-control"
                            aria-label="Nom du projet">
                        <button class="btn btn-outline-primary btn-sm align-self-center mb-0"
                                type="submit"
                                title="Renommer">
                            ✏️
                        </button>
                    </form>
                @else
                    <h1 class="fs-2 mb-0">
                        🗂️ Plan d’action : <strong>{{ $table->name }}</strong>
                    </h1>
                @endcan
            </div>

           
            <div class="d-flex align-items-center">
                @can('delete', $table)
                <form action="{{ route('table.destroy', $table) }}"
                        method="POST"
                        onsubmit="return confirm('Vraiment supprimer ce projet ?');"
                        class="d-inline-flex align-items-center me-2 mb-0">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="btn btn-outline-danger btn-sm align-self-center mb-0">
                    🗑️
                    </button>
                </form>
                @endcan
                @can('invite', $table)
                    <a href="{{ route('tables.invite', $table) }}"
                    class="btn btn-outline-secondary btn-sm me-2 align-self-center mb-0">
                        📧 Inviter
                    </a>
                @endcan
                @can('update', $table)
                <a href="{{ route('table.settings.edit', $table->id) }}"
                    class="btn btn-primary btn-sm align-self-center mb-0">
                    ⚙️
                </a>
                @endcan
            </div>
        </div>



        @php
            $settings = $table->settings ?? [];

            // ✅ Statuts : on s'assure d'avoir un tableau
            $customStatuses = is_array($settings['custom_statuses'] ?? null) ? $settings['custom_statuses'] : ['En pause', 'En cours'];
            $statusOptions = array_unique(array_merge(['Non commencé'], $customStatuses, ['Terminé']));

            // ✅ Priorités : on filtre pour ne garder que celles avec un label
            $priorityOptions = collect($settings['priorities'] ?? [])
                ->filter(fn($p) => isset($p['label']))
                ->pluck('label')
                ->values()
                ->all();
            $sources = $settings['sources'] ?? [];
            // Valeurs par défaut si aucune priorité valide
            if (empty($priorityOptions)) {
                $priorityOptions = ['P1', 'P2', 'P3'];
            }

            $important = ['Description', 'Responsable', 'Status', 'Priorité', 'Avancement', 'Atteinte objectif'];
            $multiLine = ['Description', 'Commentaire suivi'];
            $allCols = collect($table->columns)->pluck('name')->merge(['Commentaire suivi', 'Source'])->unique()->values()->all();


        @endphp

        <table id="actions-table" class="table table-striped table-hover align-middle table-custom" style="width:100%;">

            <thead class="table-light">

                <tr>
                    @foreach($important as $col)

                        @php
                            $colName = is_array($col) ? $col['name'] ?? '' : $col;

                        @endphp
                            <th>{{ $colName }}</th>
                        

                    @endforeach
                    <th>Actions</th>
                </tr>
            </thead>


            <tbody id="table-body">
                @foreach($table->rows as $row)

                    @php
                 
                        $data = $row->data ?? [];

                        $data = is_array($row->data) ? $row->data : (json_decode($row->data, true) ?: []);
                    @endphp


                    @php
                        foreach ($allCols as $key) {
                            if (!array_key_exists($key, $data)) {
                                $data[$key] = '';
                            }
                        }
                    @endphp
                    @php

                        // Ajouter la valeur formatée d'atteinte objectif pour JS
                        $priority = $data['Priorité'] ?? null;
                        $deadline = $data['Échéance planifié'] ?? null;
                        $formattedDeadline = $deadline ? \Carbon\Carbon::parse($deadline)->format('d/m/Y') : '-';
                        $delays = collect($settings['priorities'] ?? [])->pluck('delay', 'label');
                        $maxDelay = $delays[$priority] ?? null;
                        $overdue = null;

                        if ($deadline && $maxDelay !== null) {
                            $diff = \Carbon\Carbon::parse($deadline)->diffInDays(now(), false);
                            $overdue = $diff > $maxDelay;
                        }
                        $data['__formatted_objectif'] = match (true) {
                            $overdue === null => $formattedDeadline,
                            $overdue => $formattedDeadline . ' 🚨',
                            default => $formattedDeadline . ' ✅'
                        }; 



                    @endphp
                    <tr data-row='@json($data)' data-id='{{ $row->id }}'>

                        @foreach($important as $col)
                            @php
                                $colName = is_array($col) ? $col['name'] ?? '' : $col;
                                $matchedKey = collect(array_keys($data))->first(fn($k) => trim($k) === trim($colName));
                                $value = $matchedKey ? $data[$matchedKey] : null;
                            @endphp

                            <td>
                                @if($colName === 'Avancement')
                                    @php
                                        $v = intval($value);
                                        $r = max(0, min(255, 255 - 2.55 * $v));
                                        $g = max(0, min(255, 2.55 * $v));
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-fill" style="height:1rem;">
                                            <div class="progress-bar" role="progressbar"
                                                style="width:{{ $v }}%;background-color:rgb({{ $r }},{{ $g }},0)"
                                                aria-valuenow="{{ $v }}">
                                            </div>
                                        </div>
                                        
                                    </div>
                                @elseif($colName === 'Source')
                                    <span class="badge bg-secondary">{{ $value }}</span>
                                @elseif($colName === 'Responsable')
                                    @php
                                        $resps = $value
                                            ? (is_array($value) ? $value : explode(',', $value))
                                            : [];
                                    @endphp
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($resps as $tri)
                                            <span class="badge bg-primary">{{ trim($tri) }}</span>
                                        @endforeach
                                    </div>
                                @elseif($colName === 'Status')
                                    <span class="badge bg-info text-dark">{{ $value }}</span>
                                @elseif($colName === 'Priorité')
                                        @php                                         
                                            $bestEffortLabel = end($priorityOptions);
                                            $badgeClass = $value === $bestEffortLabel
                                                ? 'bg-success text-white'
                                                : 'bg-warning text-dark';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ strtoupper($value) }}
                                        </span>
                                @elseif($colName === 'Description')
                                    <div class="text-truncate-popover" tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus"
                                        title="Description" data-bs-content="{!! nl2br(e($value)) !!}">
                                        {{ Str::limit($value, 30) }}
                                    </div>
                                @elseif($colName === 'Atteinte objectif')

                                    @php
                                        $priority = $data['Priorité'] ?? null;
                                        $deadline = $data['Échéance planifié'] ?? null;
                                        $formattedDeadline = $deadline ? \Carbon\Carbon::parse($deadline)->format('d/m/Y') : '-';
                                        $delays = collect($settings['priorities'] ?? [])->pluck('delay', 'label');
                                        $maxDelay = $delays[$priority] ?? null;
                                        $overdue = null;

                                        if ($deadline && $maxDelay !== null) {
                                            $diff = \Carbon\Carbon::parse($deadline)->diffInDays(now(), false);
                                            $overdue = $diff > $maxDelay;
                                        }
                                    @endphp

                                    @if ($overdue === null)
                                        <span class="text-muted">{{ $formattedDeadline }}</span>
                                    @elseif ($overdue)
                                        <span class="badge bg-danger">{{ $formattedDeadline }} 🚨</span>
                                    @else
                                        <span class="badge bg-success">{{ $formattedDeadline }} ✅</span>
                                    @endif

                                @endif
                            </td>
                        @endforeach

                            <td>
                                @can('update', $row)
                                    <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary detail-btn"
                                    data-id='{{ $row->id }}'
                                    data-row='@json($data)'
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#detailCanvas"
                                    >🔍</button>
                                @else
                                    <span class="text-muted">Lecture seule</span>
                                @endcan

                                @can('delete', $row)
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                            data-id='{{ $row->id }}'>🗑️</button>
                                @endcan
                            </td>
                    </tr>
                @endforeach
            </tbody>
            @can('createRow', $table)
                <tfoot>
                <tr>
                    <th colspan="{{ count($important) + 1 }}" class="text-center">
                    <button id="add-btn" type="button" class="btn btn-outline-primary">➕ Ajouter une action</button>
                    </th>
                </tr>
                </tfoot>
            @endcan
        </table>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="detailCanvas">
        <div class="offcanvas-header">
            <h5>Détails & Édition</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <h6 id="detail-title" class="detail-title"></h6>
            <form id="detail-form">
                @csrf @method('PUT')
                <div id="detail-fields"></div>
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/2.3.1/js/dataTables.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const labels = {!! json_encode($chartData->pluck('month')->values()->all()) !!};

            const dataPoints = {!! json_encode($chartData->pluck('rate')) !!};
            const target = {{ $target }};

            // Calcul des couleurs selon performance
            const barColors = dataPoints.map(rate => {
                if (rate === null) return 'rgba(200,200,200,0.3)'; // gris si vide
                return rate < target ? 'rgba(255, 99, 132, 0.8)' : 'rgba(54, 162, 235, 0.8)';
            });

            const ctx = document.getElementById('performanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Performance (%)',
                        data: dataPoints,
                        backgroundColor: barColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            display: false
                        },
                        x: {
                            ticks: {
                                autoSkip: false
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            window.tableConfig = {
                
                users: @json($users),
                important: @json($important),
                multiLine: @json($multiLine),
                statusOptions: @json($statusOptions),
                sources: @json($sources),
                priorityOptions: @json($priorityOptions),
                allCols: @json($allCols),
                baseUrl: "{{ url('rows') }}/",
                addUrl: "{{ route('rows.store', $table->id) }}",
                csrfToken: document.querySelector('meta[name="csrf-token"]').content,
                canModifyAll: @json(Auth::user()->can('update', $table)),
            };
            
        </script>
        <script src="{{ asset('js/colResizable-1.6.min.js') }}"></script>
        <script src="{{ asset('js/show.js') }}"></script>
    @endpush
@endsection

