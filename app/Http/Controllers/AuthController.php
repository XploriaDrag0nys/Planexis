<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function index()
    {
        return view('tables.index');
    }

    public function loginIndex()
    {
        return view('login.index');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->google2fa_secret) {
                // Déconnexion temporaire et stockage de l’ID
                Auth::logout();
                session(['2fa:user:id' => $user->id]);

                return redirect()->route('2fa.verify');
            }

            session()->regenerate();
            return redirect()->intended(route('table.index'));
        }

        throw ValidationException::withMessages([
            'email' => 'Email ou mot de passe invalide.',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return to_route('auth.login');
    }

    public function registerIndex()
    {
        return view('register.index');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return to_route('table.index');
    }
    public function search(Request $request)
    {
        $query = $request->input('q');

        return User::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('trigramme', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'trigramme', 'name', 'email']);
    }
    public function compte()
    {
        $user = Auth::user();
        return view('users.compte', compact('user'));
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }
        if ($request->input('new_password') === $request->input('new_password_confirmation')) {
            $user->password = Hash::make($request->input('new_password'));
            $user->save();

            return back()->with('success', 'Mot de passe mis à jour avec succès.');
        }

        return back()->withErrors(['new_password_confirmation' => 'La confirmation du nouveau mot de passe ne correspond pas.']);
    }
}
