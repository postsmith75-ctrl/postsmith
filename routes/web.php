<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\EmailAuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Postsmith\BillingController;
use App\Http\Controllers\Postsmith\DashboardController;
use App\Http\Controllers\Postsmith\GenerateController;
use App\Http\Controllers\Postsmith\PostController;
use App\Http\Controllers\Postsmith\RewriteController;
use App\Http\Controllers\Postsmith\ViralLabController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', DashboardController::class)->name('dashboard');
Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::post('/generate', [GenerateController::class, 'store'])->name('generate');
Route::post('/rewrite', [RewriteController::class, 'store'])->name('rewrite');
Route::post('/viral-lab', [ViralLabController::class, 'store'])->name('viral-lab.store');
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/login', [EmailAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [EmailAuthController::class, 'login'])->name('login.store');
    Route::get('/register', [EmailAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [EmailAuthController::class, 'register'])->name('register.store');
});

Route::get('/dev/admin-login', function () {
    abort_unless(app()->environment('local'), 404);

    $user = User::firstOrCreate(
        ['email' => 'admin@postsmith.local'],
        [
            'name' => 'PostSmith Admin',
            'role' => 'admin',
            'tier' => 'pro',
            'password' => Hash::make(Str::random(40)),
            'email_verified' => true,
            'generations_reset_at' => now(),
            'pro_expires_at' => now()->addYear(),
        ],
    );

    $user->forceFill([
        'role' => 'admin',
        'tier' => 'pro',
        'pro_expires_at' => $user->pro_expires_at ?: now()->addYear(),
    ])->save();

    Auth::login($user);

    return redirect()->route('admin.dashboard');
})->name('dev.admin-login');

Route::middleware('auth')->group(function () {
    Route::get('/billing/flutterwave/verify', [BillingController::class, 'verifyFlutterwave'])->name('billing.flutterwave.verify');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::patch('/posts/{post}/metrics', [PostController::class, 'updateMetrics'])->name('posts.metrics');
    Route::post('/posts/{post}/star', [PostController::class, 'toggleStar'])->name('posts.star');
});

Route::middleware(['auth', 'postsmith.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users/export', [AdminController::class, 'exportUsers'])->name('users.export');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::get('/drivers', [AdminController::class, 'drivers'])->name('drivers');
    Route::patch('/drivers/{driver}', [AdminController::class, 'updateDriver'])->name('drivers.update');
    Route::get('/viral-lab', [AdminController::class, 'viralLab'])->name('viral-lab');
});
