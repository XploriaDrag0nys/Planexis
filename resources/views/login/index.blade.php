@extends('template')

@section('title', 'Login Page')

@section('content')
<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow" style="width: 400px;">
        <h1 class="text-center mb-4">Se connecter</h1>
        <form action="{{ route('auth.login') }}" method="post"> 
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email :</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password" required>
                @error('password')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-grid">
                <button class="btn btn-primary">Se connecter</button>
            </div>
        </form>
    </div>
</div>
@endsection
