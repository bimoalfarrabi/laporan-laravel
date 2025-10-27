<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForcePasswordChangeController extends Controller
{
    public function showChangeForm()
    {
        // Pastikan pengguna login dan memang perlu reset password
        if (!Auth::check() || !Auth::user()->must_reset_password) {
            return redirect()->route('dashboard');
        }
        return view('auth.force-password-change');
    }

    public function updatePassword(Request $request)
    {
        // Pastikan pengguna login dan memang perlu reset password
        if (!Auth::check() || !Auth::user()->must_reset_password) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->must_reset_password = false; // Reset flag setelah password diubah
        $user->save();

        Auth::logout(); // Logout pengguna setelah perubahan password

        return redirect()->route('login')->with('status', 'Password Anda berhasil diubah. Silakan login kembali.');
    }
}
