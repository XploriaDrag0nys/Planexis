@extends('template')

@section('content')
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-primary text-white text-center py-3 rounded-top-4">
                <h4 class="mb-0">🔑 Réinitialiser votre mot de passe</h4>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Adresse email') }}</label>
                        <input id="email" type="email"
                               class="form-control form-control-lg @error('email') is-invalid @enderror"
                               name="email"
                               value="{{ $email ?? old('email') }}"
                               required autocomplete="email" autofocus
                               placeholder="exemple@mail.com">
                        @error('email')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Nouveau mot de passe') }}</label>
                        <input id="password" type="password"
                               class="form-control form-control-lg @error('password') is-invalid @enderror"
                               name="password" required autocomplete="new-password"
                               placeholder="••••••••">
                        <div class="form-text text-muted">
                            🔒 Votre mot de passe doit contenir au minimum <strong>12 caractères</strong>, 
                            avec au moins <strong>1 majuscule</strong>, <strong>1 minuscule</strong> et 
                            <strong>1 caractère spécial</strong>.
                        </div>
                        @error('password')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    {{-- Password Confirmation --}}
                    <div class="mb-4">
                        <label for="password-confirm" class="form-label">{{ __('Confirmer le mot de passe') }}</label>
                        <input id="password-confirm" type="password"
                               class="form-control form-control-lg"
                               name="password_confirmation"
                               required autocomplete="new-password"
                               placeholder="••••••••">
                    </div>

                    {{-- Submit --}}
                    <div class="d-grid">
                        <button type="submit" class="btn btn-lg btn-primary shadow-sm">
                            🔄 {{ __('Réinitialiser le mot de passe') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
