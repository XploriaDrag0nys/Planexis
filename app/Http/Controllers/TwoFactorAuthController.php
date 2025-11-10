<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class TwoFactorAuthController extends Controller
{
    public function showEnableForm()
    {
        $user = Auth::user();

        if (!$user->google2fa_secret) {
            $google2fa = new Google2FA();
            $secret = $google2fa->generateSecretKey();

            session(['2fa_secret_temp' => $secret]);

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            );

            $qrImage = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($qrCodeUrl) . '&size=200x200';

            return view('2fa.enable', compact('qrImage', 'secret'));
        }

        return view('2fa.disable');
    }

    public function enable(Request $request)
    {
        $user = Auth::user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(session('2fa_secret_temp'), $request->input('code'));

        if ($valid) {
            $user->google2fa_secret = session('2fa_secret_temp');
            $user->save();
            session()->forget('2fa_secret_temp');

            return redirect()->route('main.index')->with('success', '2FA activée.');
        }

        return back()->withErrors(['code' => 'Code invalide.']);
    }

    public function disable()
    {
        $user = Auth::user();
        $user->google2fa_secret = null;
        $user->save();

        return redirect()->route('main.index')->with('success', '2FA désactivée.');
    }
    public function showVerifyForm()
    {
        if (!session()->has('2fa:user:id')) {
            return redirect()->route('auth.login');
        }

        return view('2fa.verify');
    }

    public function verifyCode(Request $request)
    {
        $userId = session('2fa:user:id');
        $user = User::find($userId); 

        if (!$user || !$user->google2fa_secret) {
            return redirect()->route('auth.login');
        }

        $google2fa = new Google2FA();

        if ($google2fa->verifyKey($user->google2fa_secret, $request->input('code'))) {
            Auth::login($user);
            session()->forget('2fa:user:id');

            return redirect()->intended(route('main.index'));
        }

        return back()->withErrors(['code' => 'Code invalide.']);
    }
}
