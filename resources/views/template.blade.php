<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/template.css') }}">
    @endpush
    @stack('styles')

</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="{{ route('table.index') }}">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo NeoCyb" width="30" height="30"
                        class="me-2 align-text-top">
                    PLANEXIS – suivi action
                </a>

                @auth
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <span class="text-success">{{ Auth::user()->name }}</span>
                        <span class="text-muted">|</span>
                        @php
                            $role = Auth::user()->isAdmin() ? 'Administrateur'
                                : (Auth::user()->isProjectManager() ? 'Chef de projet'
                                    : 'Contributeur');
                        @endphp
                        <span class="text-secondary">{{ $role }}</span>
                    </div>
                @endauth

            </div>
        </nav>
    </header>

    <div class="d-flex" id="layout">

        {{-- Sidebar gauche --}}
        <aside class="sidebar bg-light border-end p-3" id="sidebar">
            <button id="toggleSidebar" class="btn btn-sm btn-outline-secondary mb-3">
                ⬅️
            </button>

            <ul class="nav flex-column gap-2">


                @auth
                    <li>
                        <a href="{{ route('table.index') }}" class="nav-link">
                            <span class="icon">📌</span>
                            <span class="label">Plans d'actions</span>
                        </a>
                    </li>

                @endauth


                @if(auth()->user()?->isAdmin() || auth()->user()?->isProjectManager())
                    <li>
                        <a href="{{ route('projects.index') }}" class="nav-link">
                            <span class="icon">⚙️</span>
                            <span class="label">Administration</span>
                        </a>
                    </li>

                @endif

                @auth
                    <li>
                        <a href="{{ route('auth.compte') }}" class="nav-link">
                            <span class="icon">📒</span>
                            <span class="label">Mon compte</span>
                        </a>
                    </li>

                @endauth

                @auth
                    <li>
                        <form action="{{ route('auth.logout') }}" method="post">
                            @csrf @method('delete')
                            <button class="btn btn-outline-secondary w-100">
                                <span class="icon">🔓</span>
                                <span class="label">Déconnexion</span>
                            </button>
                        </form>
                    </li>

                @endauth

                @guest
                    <li>
                        <a href="{{ route('auth.login') }}" class="nav-link">
                            <span class="icon">🔐</span>
                            <span class="label">Connexion</span>
                        </a>
                    </li>

                @endguest
            </ul>
        </aside>

        {{-- Contenu principal --}}
        <main id="mainContent">
            @yield('content')
            @stack('scripts')
        </main>
    </div>
    <script>

        const toggleBtn = document.getElementById('toggleSidebar');
        const layout = document.getElementById('layout');

        if (!toggleBtn || !layout) {
            console.warn("toggleSidebar ou layout introuvable");
        } else {
            toggleBtn.addEventListener('click', () => {
                layout.classList.toggle('sidebar-collapsed');
                toggleBtn.textContent = layout.classList.contains('sidebar-collapsed') ? '➡️' : '⬅️';
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    
</body>

</html>