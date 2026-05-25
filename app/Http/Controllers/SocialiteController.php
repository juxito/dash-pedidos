<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirige al usuario a GitHub para autenticarse.
     *
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse
    {
        // return Socialite::driver('github')->redirect();
        return Socialite::driver('github')
        ->with(['prompt' => 'consent'])
        ->redirect();
    }

    /**
     * Callback de GitHub: crea o recupera el usuario y lo autentica.
     *
     * @return RedirectResponse
     */
    public function callback(): RedirectResponse
    {
        try {
            $githubUser = Socialite::driver('github')->user();

            $user = User::firstOrCreate(
                ['github_id' => $githubUser->getId()],
                [
                    'name'  => $githubUser->getName() ?? $githubUser->getNickname(),
                    'email' => $githubUser->getEmail(),
                    'avatar' => $githubUser->getAvatar(),
                ]
            );

            auth()->login($user, remember: false);

            return redirect('/dashboard');
        } catch (\Exception $e) {
            return redirect('/')->withErrors('Error durante la autenticación con GitHub.');
        }
    }

    /**
     * Desautentica al usuario y lo redirige al inicio.
     *
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        auth()->logoutCurrentDevice(); // cierra solo este dispositivo, respeta otros
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    }
}