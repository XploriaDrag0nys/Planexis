@extends('template')

@section('title', 'Register Page')

@section('content')
<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow" style="width: 400px;">
        <h1 class="text-center mb-4">Création du compte</h1>
        <form action="{{ route('auth.register') }}" method="post">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Son Prénom :</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Son Email :</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Son Mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password" required>
                @error('password')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirmation du mot de passe :</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                @error('password_confirmation')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid">
                <button class="btn btn-primary">Terminer</button>
            </div>
        </form>
    </div>
</div>
@endsection
