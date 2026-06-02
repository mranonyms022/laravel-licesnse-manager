<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('admin_authed')) {
            return redirect()->route('licenses.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (
            $request->email === config('admin.email') &&
            Hash::check($request->password, config('admin.password'))
        ) {
            $request->session()->put('admin_authed', true);
            $request->session()->put('admin_authed_at', time());
            $request->session()->regenerate();
            return redirect()->route('licenses.index');
        }

        return back()->withInput($request->only('email'))
                     ->withErrors(['email' => 'Invalid email or password.']);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_authed', 'admin_authed_at']);
        $request->session()->regenerate();
        return redirect()->route('login');
    }
}
