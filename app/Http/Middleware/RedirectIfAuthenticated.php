<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (Auth::check()) {
            return redirect()->route('table.index'); // ou n'importe quelle page après login
        }

        return $next($request);
    }
}
