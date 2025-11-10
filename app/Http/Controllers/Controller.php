<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        // middleware inline : exécuté avant toutes tes actions web
        $this->middleware(function (Request $request, $next) {
            // 1) Si pas connecté, on ne fait rien
            if (!Auth::check()) {
                return $next($request);
            }

            if ($request->routeIs('auth.logout')) {
                return $next($request);
            }

            if (Auth::user()?->isAdmin()) {
                return $next($request);
            }

            if (Setting::get('2fa_forced', '0') !== '1') {
                return $next($request);
            }

            // 3) Si l'utilisateur a déjà configuré la 2FA, on ne fait rien
            $user = Auth::user();
            if ($user->two_factor_secret) {
                return $next($request);
            }

            // 4) Exclure la page de setup (sinon boucle infinie)
            if ($request->routeIs('2fa.setup') || $request->routeIs('2fa.enable')) {
                return $next($request);
            }

            // 5) Sinon on le redirige vers la page d'activation 2FA
            return redirect()
                ->route('2fa.setup')
                ->with('info', 'L’administrateur a rendu la 2FA obligatoire. Merci de l’activer.');
        });
    }
}
