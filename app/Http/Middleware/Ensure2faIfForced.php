<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Ensure2faIfForced
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $next($request);
        }

        // Si l’admin gère le réglage, on le laisse passer
        if ($request->is('settings/2fa')) {
            return $next($request);
        }

        // Si la 2FA forcée n’est pas activée, on passe
        if (Setting::get('2fa_forced', '0') !== '1') {
            return $next($request);
        }

        // Si l’utilisateur a déjà configuré la 2FA, on passe
        if (Auth::user()->two_factor_secret) {
            return $next($request);
        }

        // Si on est déjà sur le setup ou la vérification, on passe
        if ($request->routeIs('2fa.setup') || $request->routeIs('2fa.verify')) {
            return $next($request);
        }

        // Sinon, on redirige vers le setup 2FA
        return redirect()->route('2fa.setup')
                         ->with('info', 'La 2FA est maintenant obligatoire : merci de l’activer.');
    }
}
