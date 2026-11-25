<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use Illuminate\View\View;

class ItFlowSessionController extends Controller
{
    public function create(): View
    {
        return view('itflow.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'nip' => ['required', 'string', 'regex:/\A[0-9]{10}\z/'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'nip.required' => 'Masukkan NIP.',
            'nip.regex' => 'NIP harus terdiri dari 10 angka.',
            'password.required' => 'Masukkan password.',
        ]);

        $key = 'itflow-login:' . hash('sha256', $credentials['nip'] . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withInput($request->only('nip'))
                ->withErrors(['nip' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."]);
        }

        if (! Auth::attempt(['nip' => $credentials['nip'], 'password' => $credentials['password'], 'is_active' => true])) {
            RateLimiter::hit($key, 60);

            return back()->withInput($request->only('nip'))
                ->withErrors(['nip' => 'NIP atau password salah.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
