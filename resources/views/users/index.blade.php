@extends('template')
@section('title', 'Mes projets & Utilisateurs')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="{{ asset('css/users_index.css') }}">
    @endpush

    @php
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Détection "Project Manager"
        $isPM = method_exists($user, 'isProjectManager')
            ? $user->isProjectManager()
            : (method_exists($user, 'projectManagedTables') ? $user->projectManagedTables()->exists() : false);

        // Respecte ?tab=... si présent ; sinon priorité aux PM sur "plans"
        $requestedTab = request('tab');
        $active = $requestedTab
            ?? ($isPM ? 'plans' : ($isAdmin ? 'users' : 'plans'));
    @endphp

    <ul class="nav nav-pills mb-3" id="catsTab" role="tablist">
        @if($isAdmin)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $active === 'users' ? 'active' : '' }}" id="cat1-tab"
                        data-bs-toggle="tab" data-bs-target="#cat1" type="button" role="tab"
                        aria-selected="{{ $active === 'users' ? 'true' : 'false' }}">
                    👥 Gestion des utilisateurs
                </button>
            </li>
        @endif

        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $active === 'plans' ? 'active' : '' }}" id="cat2-tab"
                    data-bs-toggle="tab" data-bs-target="#cat2" type="button" role="tab"
                    aria-selected="{{ $active === 'plans' ? 'true' : 'false' }}">
                📋 Mes plans d'actions
            </button>
        </li>

        @if($isAdmin)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $active === 'security' ? 'active' : '' }}" id="cat3-tab"
                        data-bs-toggle="tab" data-bs-target="#cat3" type="button" role="tab"
                        aria-selected="{{ $active === 'security' ? 'true' : 'false' }}">
                    🦺 Paramètres Sécurité
                </button>
            </li>
        @endif
    </ul>

    {{-- Un SEUL conteneur tab-content qui englobe tous les panneaux --}}
    <div class="tab-content" id="catsTabContent">

        @if($isAdmin)
            <div class="tab-pane fade {{ $active === 'users' ? 'show active' : '' }}" id="cat1" role="tabpanel" aria-labelledby="cat1-tab" tabindex="0">
                <div class="card">
                    <div class="card-body">
                        <div class="container mt-4">
                            <form method="GET" action="{{ route('projects.index') }}" class="mb-3">
                                <input type="hidden" name="tab" value="users">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Rechercher par nom, email ou trigramme…" value="{{ $search }}">
                                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                                </div>
                            </form>

                            @if($users->isEmpty())
                                <div class="alert alert-warning">Aucun utilisateur trouvé.</div>
                            @else
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Trigramme</th>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $u)
                                            <tr>
                                                <td>{{ $u->trigramme }}</td>
                                                <td>{{ $u->name }}</td>
                                                <td>{{ $u->email }}</td>
                                                <td class="text-end">
                                                    @if ($u->isAdmin())
                                                        <span class="badge bg-success">Admin</span>
                                                    @else
                                                        <form action="{{ route('admin.users.delete', $u) }}" method="POST"
                                                              onsubmit="return confirm('Supprimer cet utilisateur ?');" class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" title="Supprimer cet utilisateur">🗑️</button>
                                                        </form>
                                                        <form action="{{ route('admin.users.promote', $u) }}" method="POST"
                                                              onsubmit="return confirm('Promouvoir cet utilisateur au rang d\'admin (action irreversible) ?');"
                                                              class="d-inline">
                                                            @csrf @method('POST')
                                                            <button class="btn btn-sm btn-outline-success" title="Promouvoir cet utilisateur au rang d'admin">👑</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                {{ $users->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="tab-pane fade {{ $active === 'plans' ? 'show active' : '' }}" id="cat2" role="tabpanel" aria-labelledby="cat2-tab" tabindex="0">
            <div class="card">
                <div class="card-body">
                    <div class="container py-4">
                        <form method="GET" action="{{ route('projects.index') }}" class="mb-3">
                            <input type="hidden" name="tab" value="plans">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control"
                                       placeholder="Rechercher un plan, un CP ou un contributeur…"
                                       value="{{ $qPlans }}">
                                <button class="btn btn-outline-secondary" type="submit">🔍</button>
                                @if($qPlans)
                                    <a class="btn btn-outline-dark" href="{{ route('projects.index', ['tab' => 'plans']) }}">✖</a>
                                @endif
                            </div>
                        </form>

                        @forelse($tables as $table)
                            <div class="card mb-3 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $table->name }}</h5>

                                    @if ($isAdmin)
                                        <p class="mb-1"><strong>Chef de projet :</strong></p>

                                        @foreach($table->projectManagers as $pm)
                                            <span class="badge bg-info text-white d-inline-flex align-items-center me-1">
                                                {{ $pm->name }} ({{ $pm->trigramme }})
                                                <form action="{{ route('projects.pm.destroy', [$table, $pm]) }}" method="POST" class="ms-2"
                                                      onsubmit="return confirm('Retirer {{ $pm->name }} ?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn-close btn-close-white btn-sm" aria-label="Retirer"></button>
                                                </form>
                                            </span>
                                        @endforeach

                                        <form action="{{ route('projects.pm.store', $table) }}" method="POST"
                                              class="mt-2 d-flex align-items-center">
                                            @csrf

                                            <input type="text" class="form-control form-control-sm me-2" placeholder="Rechercher un CP…"
                                                   id="pm_input_{{ $table->id }}" list="users_{{ $table->id }}">

                                            <datalist id="users_{{ $table->id }}">
                                                @foreach($allUsers as $u)
                                                    <option data-id="{{ $u->id }}" value="{{ $u->name }} ({{ $u->trigramme }})"></option>
                                                @endforeach
                                            </datalist>

                                            <input type="hidden" name="user_id" id="pm_hidden_{{ $table->id }}">

                                            <button type="submit" class="btn btn-outline-primary btn-sm">➕</button>
                                        </form>
                                        <hr>
                                    @endif

                                    <p class="mb-1"><strong>Contributeurs :</strong></p>
                                    <div class="d-flex flex-wrap gap-2">
                                        @forelse($table->contributors as $u)
                                            <span class="badge bg-secondary d-inline-flex align-items-center">
                                                {{ $u->name }} ({{ $u->trigramme }})
                                                @if (auth()->user()->isProjectManagerOf($table) || $isAdmin)
                                                    <form action="{{ route('projects.users.destroy', [$table, $u]) }}" method="POST" class="ms-2 p-0"
                                                          onsubmit="return confirm('Retirer {{ $u->name }} ?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn-close btn-close-white btn-sm"></button>
                                                    </form>
                                                @endif
                                            </span>
                                        @empty
                                            <span class="text-muted">Aucun contributeur.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">Aucun plan d’actions trouvé.</div>
                        @endforelse

                        {{ $tables->appends(['tab' => 'plans', 'q' => $qPlans])->links() }}
                    </div>
                </div>
            </div>
        </div>

        @if ($isAdmin)
            <div class="tab-pane fade {{ $active === 'security' ? 'show active' : '' }}" id="cat3" role="tabpanel" aria-labelledby="cat3-tab" tabindex="0">
                <div class="card">
                    <div class="card-body">
                        <div class="container py-4">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            <form method="POST" action="{{ route('settings.2fa.update') }}">
                                @csrf
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="force" id="force2fa" {{ $forced ? 'checked' : '' }}>
                                    <label class="form-check-label" for="force2fa">
                                        Forcer la 2FA pour tous les utilisateurs
                                    </label>
                                </div>
                                <button class="btn btn-primary mt-3">Enregistrer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div> {{-- /tab-content --}}

    <script>
        document.querySelectorAll('input[id^="pm_input_"]').forEach(input => {
            const tableId = input.id.replace('pm_input_', '');
            const hidden = document.getElementById(`pm_hidden_${tableId}`);
            const list = document.getElementById(`users_${tableId}`);

            input.addEventListener('input', function () {
                const val = this.value, opts = list.options;
                hidden.value = '';
                for (let i = 0; i < opts.length; i++) {
                    if (opts[i].value === val) {
                        hidden.value = opts[i].dataset.id;
                        break;
                    }
                }
            });
        });
    </script>
@endsection
