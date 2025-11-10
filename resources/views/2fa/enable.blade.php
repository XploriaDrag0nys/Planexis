@extends('template')
@section('title', 'Activer 2FA')

@section('content')
<div class="container mt-5">
    <h2>Activer la double authentification</h2>
    <p>Scanne ce QR code avec ton app (Google Authenticator), puis entre le code :</p>
    <img src="{{ $qrImage }}" alt="QR Code">
    <form method="POST" action="{{ route('2fa.enable') }}" class="mt-3">
        @csrf
        <label>Code (6 chiffres)</label>
        <input type="text" name="code" class="form-control" required>
        <button class="btn btn-success mt-2">Activer</button>
    </form>
</div>
@endsection
