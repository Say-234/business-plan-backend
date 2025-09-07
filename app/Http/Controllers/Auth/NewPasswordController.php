<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
     * Réinitialiser le mot de passe.
     *
     * @group Authentification
     *
     * Cette route permet de réinitialiser le mot de passe d'un utilisateur
     * après vérification du code de réinitialisation.
     *
     * @bodyParam email string required L'adresse email de l'utilisateur. Exemple: user@example.com
     * @bodyParam token string required Le token de réinitialisation obtenu après vérification du code. Exemple: abc123token
     * @bodyParam password string required Le nouveau mot de passe. Exemple: nouveaumotdepasse123
     * @bodyParam password_confirmation string required Confirmation du nouveau mot de passe. Exemple: nouveaumotdepasse123
     *
     * @response 200 {
     *   "status": "Votre mot de passe a été réinitialisé !"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["Ce token de réinitialisation de mot de passe est invalide"],
     *     "password": ["Le mot de passe doit contenir au moins 8 caractères"]
     *   }
     * }
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Vérifier d'abord si le token est valide
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !hash_equals($record->token, hash('sha256', $request->token))) {
            throw ValidationException::withMessages([
                'email' => [__('Ce token de réinitialisation est invalide.')],
            ]);
        }

        // Vérifier si le token a expiré (60 minutes)
        if (strtotime($record->created_at) < now()->subMinutes(60)->getTimestamp()) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            throw ValidationException::withMessages([
                'email' => [__('Le token de réinitialisation a expiré.')],
            ]);
        }

        // Mettre à jour le mot de passe de l'utilisateur
        $user = DB::table('users')
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => [__("Nous ne trouvons pas d'utilisateur avec cette adresse email.")],
            ]);
        }

        // Mettre à jour le mot de passe
        DB::table('users')
            ->where('email', $request->email)
            ->update([
                'password' => Hash::make($request->password),
                'remember_token' => Str::random(60),
            ]);

        // Supprimer le token de réinitialisation
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'status' => 'Votre mot de passe a été réinitialisé avec succès !',
        ]);
    }
}
