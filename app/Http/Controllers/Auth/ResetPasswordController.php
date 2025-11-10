<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rules\Password;      
use Illuminate\Auth\Events\PasswordReset;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/';

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')
            ->with(['token' => $token, 'email' => $request->email]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)  
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->email_verified_at = now();
                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === PasswordBroker::PASSWORD_RESET
            ? redirect()->route('auth.login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function messages()
    {
        return [
            'password.min'   => 'Le mot de passe doit comporter au moins :min caractères.',
            'password.regex' => 'Le mot de passe doit contenir 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.',
        ];
    }
}
