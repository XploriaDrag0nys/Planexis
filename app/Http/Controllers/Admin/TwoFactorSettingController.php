<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class TwoFactorSettingController extends Controller
{
    public function edit()
    {
        $forced = Setting::get('2fa_forced', '0') === '1';
        return view('users.index', compact('forced'));
    }

    public function update(Request $request)
    {
        Setting::set('2fa_forced', $request->has('force') ? '1' : '0');
        return back()->with('success', 'Paramètre mis à jour.');
    }
}
