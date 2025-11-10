@extends('template')
@section('title', 'Désactiver 2FA')

@section('content')
<div class="container mt-5">
    <h2>La double authentification est activée</h2>
    <form action="{{ route('2fa.disable') }}" method="POST">
        @csrf
        <button class="btn btn-danger">Désactiver 2FA</button>
    </form>
</div>
@endsection
