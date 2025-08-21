<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Vérifier l'adresse email de l'utilisateur.
     *
     * @group Authentification
     *
     * Cette route permet de vérifier l'adresse email de l'utilisateur à partir
     * du lien reçu par email. L'utilisateur est redirigé vers le dashboard.
     *
     * @urlParam id integer requis L'ID de l'utilisateur. Exemple: 1
     * @urlParam hash string requis Le hash de vérification. Exemple: abc123
     *
     * @response 302 "Redirection vers le dashboard avec paramètre verified=1"
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(
                config('app.frontend_url').'/dashboard?verified=1'
            );
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(
            config('app.frontend_url').'/dashboard?verified=1'
        );
    }
}
