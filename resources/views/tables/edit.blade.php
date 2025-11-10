@extends('template')

@section('title', 'Modifier le Tableau')

@section('content')
    {{-- On ne montre ce contenu qu’aux Admins ou Project Managers sur ce tableau --}}
    @can('update', $table)
        <div class="container mt-4">
            <a href="{{ route('table.show', $table->id) }}" class="btn btn-warning btn-sm">Revenir au tableau</a>

            <h1>Modifier le Tableau : {{ $table->name }}</h1>

            {{-- Formulaire de modification globale (titre, description, etc.) --}}
            <form action="{{ route('table.update', $table->id) }}" method="POST" class="mb-4">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Nom du Tableau :</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $table->name }}" required>
                </div>
                {{-- Ajoutez ici d’autres champs si nécessaire --}}
                <button type="submit" class="btn btn-success">Enregistrer</button>
            </form>

            {{-- Section de gestion des colonnes (add/update/delete) --}}
            <h2>Colonnes :</h2>
            @foreach($table->columns as $index => $column)
                <div class="d-flex align-items-center mb-2">
                    {{-- Afficher le nom de la colonne --}}
                    <span class="me-3">{{ $column }}</span>

                    {{-- Formulaire pour renommer cette colonne --}}
                    <form action="{{ route('table.updateColumn', ['tableId' => $table->id, 'index' => $index]) }}" method="POST" class="me-2">
                        @csrf
                        @method('PUT')
                        <input type="text" name="new_name" value="{{ $column }}" class="form-control form-control-sm d-inline-block" style="width: auto;">
                        <button class="btn btn-success btn-sm" type="submit">✅</button>
                    </form>

                    {{-- Formulaire pour supprimer cette colonne --}}
                    <form action="{{ route('table.deleteColumn', ['tableId' => $table->id, 'index' => $index]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit">🗑️</button>
                    </form>
                </div>
            @endforeach

            {{-- Formulaire pour ajouter une nouvelle colonne --}}
            <form action="{{ route('table.addColumn', ['table' => $table->id]) }}" method="POST" class="mt-3">
                @csrf
                <div class="input-group">
                    <input type="text" name="new_column" class="form-control" placeholder="Nouveau nom de colonne" required>
                    <button class="btn btn-primary" type="submit">Ajouter une colonne</button>
                </div>
            </form>
        </div>
    @else
        <div class="alert alert-danger">
            Vous n’avez pas la permission de modifier la structure de ce tableau.
        </div>
    @endcan
@endsection
