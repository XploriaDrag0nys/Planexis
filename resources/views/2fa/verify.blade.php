@extends('template')
@section('title', 'Vérification 2FA')

@section('content')
<div class="container mt-5">
    <h2>Vérification en deux étapes</h2>
    <form method="POST" action="{{ route('2fa.verify.code') }}">
        @csrf
        <label for="code">Code 2FA :</label>
        <input type="text" name="code" class="form-control" required autofocus>
        @error('code')
            <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
        <button class="btn btn-primary mt-3">Valider</button>
    </form>
</div>
@endsection
