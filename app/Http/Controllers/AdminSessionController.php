<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        $credentials['email'] = mb_strtolower($credentials['email']);
        $credentials['is_admin'] = true;

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match an administrator account.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
