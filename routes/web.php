<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Livewire\Public\RosterConfirmation;
use App\Livewire\Stage\StageView;
use App\Models\Organization;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/app');
    }

    return view('welcome');
})->name('home');

Route::get('/login', function () {
    if (auth()->check()) {
        return redirect('/app');
    }

    return redirect()->route('filament.app.auth.login');
})->name('login');

Route::get('/r/{token}', RosterConfirmation::class)
    ->middleware('throttle:60,1')
    ->name('roster.confirm');

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/app/{organization:slug}/events/{event}/stage', StageView::class)->name('events.stage');
});

Route::get('/join/{code}', function (string $code) {
    $code = strtoupper(trim($code));
    $organization = Organization::where('invite_code', $code)->first();

    if (! $organization) {
        abort(404, 'Código de convite inválido ou expirado.');
    }

    if (auth()->check()) {
        $organization->users()->syncWithoutDetaching([
            auth()->id() => ['role' => Organization::ROLE_MEMBER],
        ]);

        return redirect("/app/{$organization->slug}");
    }

    session()->put('pending_invite_code', $code);

    return redirect()->route('filament.app.auth.login');
})->middleware('throttle:30,1')->name('organization.join');

Route::middleware('web')->group(function (): void {
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});
