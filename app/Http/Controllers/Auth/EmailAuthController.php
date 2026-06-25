<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class EmailAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.access', ['mode' => 'login']);
    }

    public function showRegister(): View
    {
        return view('auth.access', ['mode' => 'register']);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([
            'email' => strtolower(trim($credentials['email'])),
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('status', 'Signed in.');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'],
            'email_verified' => false,
            'email_verified_at' => null,
            'generations_reset_at' => now(),
        ]);

        $this->sendVerificationCode($user);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')->with('status', 'Account created. Check your email for the verification code.');
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        if ($request->user()->email_verified) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $freshCode = $user->verification_sent_at && $user->verification_sent_at->gt(now()->subMinutes(30));

        if (! $freshCode || ! $user->verification_code || ! Hash::check($data['code'], $user->verification_code)) {
            return back()->withErrors(['code' => 'The verification code is invalid or expired.']);
        }

        $user->forceFill([
            'email_verified' => true,
            'email_verified_at' => now(),
            'verification_code' => null,
            'verification_sent_at' => null,
        ])->save();

        return redirect()->route('dashboard')->with('status', 'Email verified.');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->email_verified) {
            return redirect()->route('dashboard');
        }

        if ($user->verification_sent_at && $user->verification_sent_at->gt(now()->subMinute())) {
            return back()->withErrors(['email' => 'Please wait a minute before requesting another code.']);
        }

        $this->sendVerificationCode($user);

        return back()->with('status', 'A new verification code has been sent.');
    }

    private function sendVerificationCode(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'verification_code' => Hash::make($code),
            'verification_sent_at' => now(),
        ])->save();

        try {
            Mail::html(
                view('emails.verify-email', ['user' => $user, 'code' => $code])->render(),
                fn ($message) => $message
                    ->to($user->email, $user->name)
                    ->subject('Verify your PostSmith email')
            );
        } catch (\Throwable) {
            report('Could not send verification email to '.$user->email);
        }
    }
}
