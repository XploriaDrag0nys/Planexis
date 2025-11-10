@extends('template')

@section('title', 'Mon compte')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Mon Compte</h1>

        {{-- Informations utilisateur --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                Mes informations
            </div>
            <div class="card-body">
                <p><strong>Nom :</strong> {{ Auth::user()->name }}</p>
                <p><strong>Email :</strong> {{ Auth::user()->email }}</p>
                <p><strong>Trigramme :</strong> {{ Auth::user()->trigramme }}</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                Changer de mot de passe
            </div>
            <div class="card-body">
                <form action="{{ route('auth.password.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mot de passe actuel</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>

                        <ul id="passwordRules" class="list-unstyled small mt-2">
                            <li id="rule-length" class="text-danger">❌ Minimum 12 caractères</li>
                            <li id="rule-uppercase" class="text-danger">❌ Au moins 1 majuscule</li>
                            <li id="rule-lowercase" class="text-danger">❌ Au moins 1 minuscule</li>
                            <li id="rule-special" class="text-danger">❌ Au moins 1 caractère spécial</li>
                            <li id="rule-number" class="text-danger">❌ Au moins 1 chiffre</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" id="new_password_confirmation"
                            name="new_password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-success">Mettre à jour le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
    <script>
    const passwordInput = document.getElementById('new_password');

    passwordInput.addEventListener('input', function () {
        const val = passwordInput.value;

        // Règles
        document.getElementById('rule-length').className = val.length >= 12 ? 'text-success' : 'text-danger';
        document.getElementById('rule-length').textContent = (val.length >= 12 ? '✅' : '❌') + " Minimum 12 caractères";

        document.getElementById('rule-uppercase').className = /[A-Z]/.test(val) ? 'text-success' : 'text-danger';
        document.getElementById('rule-uppercase').textContent = (/[A-Z]/.test(val) ? '✅' : '❌') + " Au moins 1 majuscule";

        document.getElementById('rule-lowercase').className = /[a-z]/.test(val) ? 'text-success' : 'text-danger';
        document.getElementById('rule-lowercase').textContent = (/[a-z]/.test(val) ? '✅' : '❌') + " Au moins 1 minuscule";

        document.getElementById('rule-special').className = /[^A-Za-z0-9]/.test(val) ? 'text-success' : 'text-danger';
        document.getElementById('rule-special').textContent = (/[^A-Za-z0-9]/.test(val) ? '✅' : '❌') + " Au moins 1 caractère spécial";

        document.getElementById('rule-number').className = /[0-9]/.test(val) ? 'text-success' : 'text-danger';
        document.getElementById('rule-number').textContent = (/[0-9]/.test(val) ? '✅' : '❌') + " Au moins 1 chiffre";
    });
</script>
@endsection