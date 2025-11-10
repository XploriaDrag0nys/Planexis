<?php
// app/scripts/cleanup_invitations.php

use Illuminate\Support\Carbon;
use App\Models\User;

// on charge l’application Laravel
require __DIR__ . '/../../bootstrap/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// on calcule la limite d’une heure en arrière
$cutoff = Carbon::now()->subHour();

// on récupère tous les comptes non vérifiés créés il y a plus d’une heure
$stale = User::whereNull('email_verified_at')
             ->where('created_at', '<', $cutoff)
             ->get();

foreach ($stale as $user) {
    echo "[" . Carbon::now() . "] Suppression de l’invitation pour {$user->email}\n";
    $user->delete();
}
