<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Renvoyer un email de vérification.
     *
     * @group Authentification
     *
     * Cette route permet de renvoyer un email de vérification à l'utilisateur connecté
     * si son email n'est pas encore vérifié.
     *
     * @response 200 {
     *   "status": "verification-link-sent"
     * }
     * @response 302 "Redirection vers /dashboard si l'email est déjà vérifié"
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }
}
