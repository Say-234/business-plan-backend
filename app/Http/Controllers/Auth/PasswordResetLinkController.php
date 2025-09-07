<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Mail\PasswordResetCodeMail;

class PasswordResetLinkController extends Controller
{
    /**
     * Envoyer un code de réinitialisation de mot de passe.
     *
     * @group Authentification
     *
     * Cette route permet d'envoyer un code de réinitialisation de mot de passe
     * à l'adresse email spécifiée si elle existe en base de données.
     *
     * @bodyParam email string required L'adresse email de l'utilisateur. Exemple: user@example.com
     *
     * @response 200 {
     *   "status": "Nous avons envoyé votre code de réinitialisation par email !"
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

        // Vérifier d'abord si l'utilisateur existe
        $user = DB::table('users')->where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => [__("Nous ne trouvons pas d'utilisateur avec cette adresse email.")],
            ]);
        }

        // Générer un code à 6 chiffres
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = hash('sha256', $code);

        // Stocker ou mettre à jour le token dans la base de données
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );

        // Envoyer l'email avec le code
        try {
            Mail::to($request->email)->send(new PasswordResetCodeMail($code));

            return response()->json([
                'status' => 'Nous avons envoyé votre code de réinitialisation par email !',
            ]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'email' => ["Une erreur est survenue lors de l'envoi de l'email. Veuillez réessayer plus tard."],
            ]);
        }
    }
}
