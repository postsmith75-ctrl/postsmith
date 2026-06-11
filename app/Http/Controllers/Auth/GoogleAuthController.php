<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['auth' => 'Google sign-in is not configured yet. Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to .env.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['auth' => 'Google sign-in is not configured yet.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['auth' => 'Could not verify your Google account. Please try again.']);
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));

        if ($email === '') {
            return redirect()
                ->route('dashboard')
                ->withErrors(['auth' => 'Google did not return an email address for this account.']);
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->fill([
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: $user->name ?: Str::before($email, '@'),
            'google_id' => $googleUser->getId(),
            'email_verified_at' => $user->email_verified_at ?: now(),
            'email_verified' => true,
        ]);

        if (! $user->exists) {
            $user->password = Str::password(48);
            $user->generations_reset_at = now();
        }

        $user->save();

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('status', 'Signed in with Google.');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('dashboard');
    }
}
