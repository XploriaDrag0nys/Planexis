@extends('template')

@section('title', 'Créer un Tableau')

@section('content')
    @stack('styles')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/createtable.css') }}">
    @endpush

    @if(auth()->user()->isAdmin())
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">🆕 Créer un nouveau tableau</h1>
            </div>

            <form action="{{ route('table.store') }}" method="POST" class="p-4 border rounded shadow-sm bg-white">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">📌 Nom du tableau</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Ex : Suivi 2025" required>
                </div>

                <div class="mb-3">
                    <select name="pattern_id" id="pattern_id" class="form-select" required hidden>
                        @foreach(\App\Models\TablePattern::all() as $pattern)
                            <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- === Choix du mode d’affectation du Chef de projet === --}}
                <div class="mb-3">
                    <label class="form-label d-block">Chef de projet</label>

                    <div class="btn-group mb-3" role="group" aria-label="Mode PM">
                        <input type="radio" class="btn-check" name="pm_mode" id="pm_mode_existing" value="existing"
                            autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="pm_mode_existing">Utilisateur existant</label>

                        <input type="radio" class="btn-check" name="pm_mode" id="pm_mode_new" value="new" autocomplete="off">
                        <label class="btn btn-outline-primary" for="pm_mode_new">Nouveau (nom + email)</label>
                    </div>
                    @error('pm_mode')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Bloc "utilisateur existant" --}}
                <div id="pm-existing-block" class="mb-3 position-relative">
                    <input type="text" id="pm-search" class="form-control" placeholder="Rechercher un utilisateur..."
                        autocomplete="off">
                    <div id="pm-suggestions" class="list-group position-absolute w-100" style="z-index:1000;"></div>
                    <input type="hidden" name="project_manager" id="project_manager">
                    @error('project_manager')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Bloc "nouvel utilisateur" --}}
                <div id="pm-new-block" class="mb-3" style="display:none;">
                    <div class="mb-2">
                        <label for="pm_name" class="form-label">Nom complet</label>
                        <input type="text" class="form-control" id="pm_name" name="pm_name" placeholder="Ex : Marie Dupont">
                        @error('pm_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="pm_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="pm_email" name="pm_email"
                            placeholder="marie.dupont@exemple.com">
                        @error('pm_email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ❌ On supprime la case "pm-later" --}}

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        ➕ Créer le tableau
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="alert alert-danger mt-4">
            ❌ Vous n’avez pas la permission de créer un nouveau tableau.
        </div>
    @endif

    @push('scripts')
        <script>
            // Données pour l'autocomplete (utilisateurs existants)
            const users = @json($users);

            const input = document.getElementById('pm-search');
            const suggestions = document.getElementById('pm-suggestions');
            const hidden = document.getElementById('project_manager');

            function renderSuggestions(q) {
                suggestions.innerHTML = '';
                if (q.length < 2) return;

                users
                    .filter(u =>
                        (u.name || '').toLowerCase().includes(q) ||
                        (u.trigramme || '').toLowerCase().includes(q)
                    )
                    .forEach(u => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action';
                        btn.textContent = `${u.name} ${u.trigramme ? '(' + u.trigramme + ')' : ''}`;
                        btn.addEventListener('click', () => {
                            input.value = `${u.name} ${u.trigramme ? '(' + u.trigramme + ')' : ''}`;
                            hidden.value = u.id;
                            suggestions.innerHTML = '';
                        });
                        suggestions.appendChild(btn);
                    });
            }

            input?.addEventListener('input', () => {
                const q = input.value.toLowerCase();
                renderSuggestions(q);
            });

            document.addEventListener('click', e => {
                if (!input || (e.target !== input && !suggestions.contains(e.target))) {
                    suggestions.innerHTML = '';
                }
            });

            // Gestion du switch de mode
            const modeExisting = document.getElementById('pm_mode_existing');
            const modeNew = document.getElementById('pm_mode_new');
            const blockExisting = document.getElementById('pm-existing-block');
            const blockNew = document.getElementById('pm-new-block');

            function updateBlocks() {
                if (modeExisting.checked) {
                    blockExisting.style.display = '';
                    blockNew.style.display = 'none';
                    // Nettoyage des champs "new"
                    document.getElementById('pm_name').value = '';
                    document.getElementById('pm_email').value = '';
                } else {
                    blockExisting.style.display = 'none';
                    blockNew.style.display = '';
                    // Nettoyage des champs "existing"
                    if (input) input.value = '';
                    hidden.value = '';
                    suggestions.innerHTML = '';
                }
            }

            modeExisting.addEventListener('change', updateBlocks);
            modeNew.addEventListener('change', updateBlocks);
            updateBlocks();
        </script>
    @endpush
@endsection