<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * à l'aide du token reçu par email.
     *
     * @bodyParam token string requis Le token de réinitialisation reçu par email. Exemple: abc123token
     * @bodyParam email string requis L'adresse email de l'utilisateur. Exemple: user@example.com
     * @bodyParam password string requis Le nouveau mot de passe. Exemple: nouveaumotdepasse123
     * @bodyParam password_confirmation string requis Confirmation du nouveau mot de passe. Exemple: nouveaumotdepasse123
     *
     * @response 200 {
     *   "status": "Votre mot de passe a été réinitialisé !"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["Ce token de réinitialisation de mot de passe est invalide"]
     *   }
     * }
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => __($status)]);
    }
}
