@extends('template')

@section('title', 'Gestion des Tableaux')

@section('content')
    @stack('styles')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/indextable.css') }}">
    @endpush
    
    <div class="container mt-4">
        <h1 class="mb-4">📋 Liste des Plans d’actions</h1>
        <form method="GET" action="{{ route('table.index') }}" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Rechercher projet ou chef…"
                    value="{{ $search ?? '' }}">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </div>
        </form>
        @if($tables->isEmpty())
            <div class="alert alert-warning">Aucun tableau disponible pour l’instant.</div>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                @foreach($tables as $table)
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <h5 class="card-title">{{ $table->name }}</h5>

                                @if($manager = $table->projectManagers->first())
                                    <p class="text-muted small mb-2">
                                        Chef de projet : {{ $manager->name }}
                                    </p>
                                @endif

                                <a href="{{ route('table.show', $table) }}" class="btn btn-outline-primary mt-auto">
                                    📂 Ouvrir
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if(auth()->user()->isAdmin())
            <div class="mt-4 text-center">
                <a href="{{ route('table.create') }}" class="btn btn-success btn-lg">
                    ➕ Nouveau plan d'actions
                </a>
            </div>
        @endif

    </div>
@endsection