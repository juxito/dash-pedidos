<?php
declare(strict_types=1);
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard.index');
    }
    return view('welcome');
})->name('login');

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::get('github/redirect', [SocialiteController::class, 'redirect'])->name('github.redirect');
    Route::get('github/callback', [SocialiteController::class, 'callback'])->name('github.callback');
});

Route::post('logout', [SocialiteController::class, 'logout'])->name('logout');

Route::prefix('dashboard')->middleware('auth')->name('dashboard.')->group(function (): void {
    Route::get('/', [DashboardController::class, 'porEnviar'])->name('index');
    Route::get('retrasados', [DashboardController::class, 'retrasados'])->name('retrasados');
    Route::get('entregados', [DashboardController::class, 'entregados'])->name('entregados');
    Route::get('cancelados', [DashboardController::class, 'cancelados'])->name('cancelados');
});