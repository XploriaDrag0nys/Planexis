@extends('template')

@section('title', 'Paramètres du tableau')

@section('content')
    <div class="container">
        <a href="{{ route('table.show', $table->id) }}" class="btn btn-outline-secondary mb-3">
            ⬅ Retour au tableau
        </a>
        <h2 class="mb-4">🔧 Paramétrage du projet : {{ $table->name }}</h2>

        @php
            // onglet actif: ?tab=priorities|statuses|sources|performance
            $requestedTab = request('tab');
            $active = in_array($requestedTab, ['priorities','statuses','sources','performance'])
                ? $requestedTab
                : 'priorities';
        @endphp

        {{-- Onglets --}}
        <ul class="nav nav-pills mb-3" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $active === 'priorities' ? 'active' : '' }}"
                        id="priorities-tab" data-bs-toggle="tab" data-bs-target="#priorities"
                        type="button" role="tab" aria-selected="{{ $active === 'priorities' ? 'true' : 'false' }}">
                    📆 Priorités & délais
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $active === 'statuses' ? 'active' : '' }}"
                        id="statuses-tab" data-bs-toggle="tab" data-bs-target="#statuses"
                        type="button" role="tab" aria-selected="{{ $active === 'statuses' ? 'true' : 'false' }}">
                    🔹 Statuts
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $active === 'sources' ? 'active' : '' }}"
                        id="sources-tab" data-bs-toggle="tab" data-bs-target="#sources"
                        type="button" role="tab" aria-selected="{{ $active === 'sources' ? 'true' : 'false' }}">
                    🔍 Sources d’action
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $active === 'performance' ? 'active' : '' }}"
                        id="performance-tab" data-bs-toggle="tab" data-bs-target="#performance"
                        type="button" role="tab" aria-selected="{{ $active === 'performance' ? 'true' : 'false' }}">
                    🎯 Objectifs de performance
                </button>
            </li>
        </ul>

        <form action="{{ route('table.settings.update', $table->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- utile pour conserver l’onglet ouvert après submit --}}
            <input type="hidden" name="tab" id="activeTabInput" value="{{ $active }}">

            <div class="tab-content" id="settingsTabContent">

                {{-- Onglet PRIORITIES --}}
                <div class="tab-pane fade {{ $active === 'priorities' ? 'show active' : '' }}"
                     id="priorities" role="tabpanel" aria-labelledby="priorities-tab" tabindex="0">

                    <h5 class="mt-2">📆 Priorités et délais de retard</h5>
                    <div id="priority-section" class="mt-3">
                        @foreach($settings['priorities'] ?? [] as $i => $priority)
                            <div class="d-flex align-items-center mb-2 priority-row">
                                <input type="text" name="priorities[{{ $i }}][label]" class="form-control me-2"
                                       value="{{ $priority['label'] }}" placeholder="Label">
                                <input type="number" name="priorities[{{ $i }}][delay]" class="form-control me-2"
                                       value="{{ $priority['delay'] }}" placeholder="Délai">
                                <button type="button" class="btn btn-danger btn-sm remove-priority">❌</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-priority" class="btn btn-outline-primary btn-sm mt-2">
                        ➕ Ajouter une priorité
                    </button>
                </div>

                {{-- Onglet STATUSES --}}
                <div class="tab-pane fade {{ $active === 'statuses' ? 'show active' : '' }}"
                     id="statuses" role="tabpanel" aria-labelledby="statuses-tab" tabindex="0">

                    <h5 class="mt-2">🔹 Statuts disponibles</h5>
                    <ul id="status-section" class="list-group mt-3">
                        <li class="list-group-item">Non commencé (non modifiable)</li>
                        <li class="list-group-item">Terminé (non modifiable)</li>
                        @foreach($settings['custom_statuses'] ?? [] as $i => $status)
                            <li class="list-group-item d-flex align-items-center status-row">
                                <input type="text" name="custom_statuses[{{ $i }}]" class="form-control me-2"
                                       value="{{ $status }}">
                                <button type="button" class="btn btn-danger btn-sm remove-status">❌</button>
                            </li>
                        @endforeach
                    </ul>
                    <button type="button" id="add-status" class="btn btn-outline-primary btn-sm mt-2">
                        ➕ Ajouter un statut
                    </button>
                </div>

                {{-- Onglet SOURCES --}}
                <div class="tab-pane fade {{ $active === 'sources' ? 'show active' : '' }}"
                     id="sources" role="tabpanel" aria-labelledby="sources-tab" tabindex="0">

                    <h5 class="mt-2">🔍 Sources d'action</h5>
                    <div id="sources-section" class="mt-3">
                        @foreach($settings['sources'] ?? [] as $i => $source)
                            <div class="mb-2 d-flex align-items-center source-row">
                                <input type="text" name="sources[{{ $i }}]" class="form-control me-2"
                                       value="{{ $source }}">
                                <button type="button" class="btn btn-danger btn-sm remove-source">❌</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-source" class="btn btn-outline-primary btn-sm mt-2">
                        ➕ Ajouter une source
                    </button>
                </div>

                {{-- Onglet PERFORMANCE --}}
                <div class="tab-pane fade {{ $active === 'performance' ? 'show active' : '' }}"
                     id="performance" role="tabpanel" aria-labelledby="performance-tab" tabindex="0">

                    <h5 class="mt-2">🎯 Objectifs de performance (%)</h5>

                    <div class="mb-3 mt-3">
                        <label>Global</label>
                        <input type="number" name="global_target" class="form-control w-25"
                               value="{{ $settings['global_target'] ?? 80 }}">
                    </div>

                    <div id="performance-section">
                        @foreach(($settings['performance_targets'] ?? []) as $priority => $value)
                            <div class="d-flex mb-2 performance-row">
                                <select name="performance_targets[{{ $priority }}]"
                                        class="form-select me-2 priority-select">
                                    @foreach(array_column($settings['priorities'] ?? [], 'label') as $label)
                                        <option value="{{ $label }}" {{ $label == $priority ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="performance_values[{{ $priority }}]"
                                       class="form-control me-2" value="{{ $value }}" placeholder="% Objectif">
                                <button type="button" class="btn btn-danger remove-performance">🗑️</button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-outline-secondary" id="add-performance">
                        ➕ Ajouter un objectif
                    </button>
                </div>

            </div> {{-- /tab-content --}}

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-success">📅 Enregistrer les paramètres</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    // ——— Conserver l’onglet actif au submit & si on change d’onglet
    document.addEventListener('DOMContentLoaded', function () {
        const activeInput = document.getElementById('activeTabInput');

        document.querySelectorAll('#settingsTab [data-bs-toggle="tab"]').forEach(btn => {
            btn.addEventListener('shown.bs.tab', function (e) {
                const id = e.target.getAttribute('data-bs-target'); // ex: #priorities
                const key = (id || '').replace('#','');
                if (activeInput) activeInput.value = key;
                // optionnel: maj du query param ?tab=...
                const url = new URL(window.location);
                url.searchParams.set('tab', key);
                window.history.replaceState({}, '', url);
            });
        });
    });
    </script>

    {{-- TON JS EXISTANT (adapté tel quel) --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const priorities = @json(array_column($settings['priorities'] ?? [], 'label'));
        const performanceSection = document.getElementById('performance-section');

        function getUsedPriorities() {
            return Array.from(performanceSection.querySelectorAll('select')).map(sel => sel.value);
        }
        function updatePerformanceOptions() {
            const used = getUsedPriorities();
            performanceSection.querySelectorAll('.performance-row').forEach(row => {
                const select = row.querySelector('select');
                const current = select.value;
                select.innerHTML = '';
                priorities.forEach(p => {
                    const alreadyUsed = used.includes(p) && p !== current;
                    if (!alreadyUsed) {
                        const option = document.createElement('option');
                        option.value = p;
                        option.textContent = p;
                        if (p === current) option.selected = true;
                        select.appendChild(option);
                    }
                });
                const input = row.querySelector('input[type="number"]');
                select.name = `performance_targets[${select.value}]`;
                input.name = `performance_values[${select.value}]`;
            });
        }

        document.getElementById('add-performance')?.addEventListener('click', function () {
            const used = getUsedPriorities();
            const available = priorities.find(p => !used.includes(p));
            if (!available) return alert('Toutes les priorités sont déjà utilisées.');

            const div = document.createElement('div');
            div.className = 'd-flex mb-2 performance-row';
            div.innerHTML = `
                <select class="form-select me-2"></select>
                <input type="number" class="form-control me-2" placeholder="% Objectif">
                <button type="button" class="btn btn-danger remove-performance">🗑️</button>
            `;
            performanceSection.appendChild(div);
            updatePerformanceOptions();
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-performance')) {
                e.target.closest('.performance-row')?.remove();
                updatePerformanceOptions();
            }
            if (e.target.classList.contains('remove-priority')) {
                e.target.closest('.priority-row')?.remove();
            }
            if (e.target.classList.contains('remove-status')) {
                e.target.closest('.status-row')?.remove();
            }
            if (e.target.classList.contains('remove-source')) {
                e.target.closest('.source-row')?.remove();
            }
        });

        // Priorités
        let priorityCount = {{ count($settings['priorities'] ?? []) }};
        document.getElementById('add-priority')?.addEventListener('click', function () {
            const section = document.getElementById('priority-section');
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center mb-2 priority-row';
            div.innerHTML = `
                <input type="text" name="priorities[${priorityCount}][label]" class="form-control me-2" placeholder="Label">
                <input type="number" name="priorities[${priorityCount}][delay]" class="form-control me-2" placeholder="Délai">
                <button type="button" class="btn btn-danger btn-sm remove-priority">❌</button>
            `;
            section.appendChild(div);
            priorityCount++;
        });

        // Statuts
        let statusCount = {{ count($settings['custom_statuses'] ?? []) }};
        document.getElementById('add-status')?.addEventListener('click', function () {
            const section = document.getElementById('status-section');
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex align-items-center status-row';
            li.innerHTML = `
                <input type="text" name="custom_statuses[${statusCount}]" class="form-control me-2">
                <button type="button" class="btn btn-danger btn-sm remove-status">❌</button>
            `;
            section.appendChild(li);
            statusCount++;
        });

        // Sources
        let sourceCount = {{ count($settings['sources'] ?? []) }};
        document.getElementById('add-source')?.addEventListener('click', function () {
            const section = document.getElementById('sources-section');
            const div = document.createElement('div');
            div.className = 'mb-2 d-flex align-items-center source-row';
            div.innerHTML = `
                <input type="text" name="sources[${sourceCount}]" class="form-control me-2">
                <button type="button" class="btn btn-danger btn-sm remove-source">❌</button>
            `;
            section.appendChild(div);
            sourceCount++;
        });

        updatePerformanceOptions();
    });
    </script>
    @endpush
@endsection

{{-- libs si pas déjà chargées dans le layout --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
