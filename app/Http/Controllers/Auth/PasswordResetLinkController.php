<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Envoyer un lien de réinitialisation de mot de passe.
     *
     * @group Authentification
     *
     * Cette route permet d'envoyer un lien de réinitialisation de mot de passe
     * à l'adresse email spécifiée si elle existe en base de données.
     *
     * @bodyParam email string requis L'adresse email de l'utilisateur. Exemple: user@example.com
     *
     * @response 200 {
     *   "status": "Nous avons envoyé votre lien de réinitialisation de mot de passe par email !"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["Nous ne trouvons pas d'utilisateur avec cette adresse email"]
     *   }
     * }
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => __($status)]);
    }
}
