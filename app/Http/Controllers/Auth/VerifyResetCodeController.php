<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerifyResetCodeController extends Controller
{
    /**
     * Vérifier le code de réinitialisation.
     *
     * @group Authentification
     *
     * Cette route permet de vérifier si le code de réinitialisation est valide.
     *
     * @bodyParam email string required L'adresse email de l'utilisateur. Exemple: user@example.com
     * @bodyParam token string required Le code de réinitialisation reçu par email. Exemple: 123456
     *
     * @response 200 {
     *   "status": "Code valide",
     *   "token": "reset_token_here"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "token": ["Ce code de réinitialisation est invalide ou a expiré"]
     *   }
     * }
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string', 'size:6'], // Code à 6 chiffres
        ]);

        // Vérifier si un token de réinitialisation existe pour cet email
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'token' => [__('Aucune demande de réinitialisation trouvée pour cet email.')],
            ]);
        }

        // Vérifier si le code correspond
        if (!hash_equals($record->token, hash('sha256', $request->token))) {
            throw ValidationException::withMessages([
                'token' => [__('Le code de réinitialisation est invalide.')],
            ]);
        }

        // Vérifier si le token a expiré (60 minutes par défaut)
        $isExpired = strtotime($record->created_at) < now()->subMinutes(60)->getTimestamp();
        if ($isExpired) {
            // Supprimer le token expiré
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            throw ValidationException::withMessages([
                'token' => [__('Le code de réinitialisation a expiré.')],
            ]);
        }

        // Générer un token sécurisé pour la réinitialisation
        $resetToken = Str::random(60);

        // Mettre à jour le token dans la base de données
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->update([
                'token' => hash('sha256', $resetToken),
                'created_at' => now(),
            ]);

        return response()->json([
            'status' => 'Code valide',
            'token' => $resetToken,
        ]);
    }
}
