<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Rediriger vers Google pour l'authentification.
     *
     * @group Authentification
     *
     * Cette route redirige l'utilisateur vers la page d'authentification Google
     * pour autoriser l'accès à l'application.
     *
     * @response 302 "Redirection vers la page d'authentification Google"
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Traiter le retour de l'authentification Google.
     *
     * @group Authentification
     *
     * Cette route traite le retour de Google après authentification.
     * Elle crée ou connecte l'utilisateur selon son statut.
     *
     * @response 200 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@gmail.com",
     *     "google_id": "123456789"
     *   },
     *   "message": "Authentification Google réussie"
     * }
     * @response 500 {
     *   "error": true,
     *   "message": "Erreur lors de l'authentification Google"
     * }
     */
    public function callbackGoogle()
    {
        try {
            $google_user = Socialite::driver('google')->stateless()->user();
            $user = User::where('google_id', $google_user->getId())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    'google_id' => $google_user->getId(),
                ]);
            }

            // Connexion de l'utilisateur
            Auth::login($user);

            // Création d'un token API (ici Sanctum)
            // $token = $user->createToken('google_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                // 'token' => $token,
                'message' => 'Authentification Google réussie'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function signInWithGoogle(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $google_user = Socialite::driver('google')->userFromToken($request->token);

            $user = User::where('google_id', $google_user->getId())->first();

            if (!$user) {
                $user = User::where('email', $google_user->getEmail())->first();
                if ($user) {
                    $user->google_id = $google_user->getId();
                    $user->save();
                } else {
                    $user = User::create([
                        'name' => $google_user->getName(),
                        'email' => $google_user->getEmail(),
                        'google_id' => $google_user->getId(),
                        'email_verified_at' => now(),
                    ]);
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'token' => $token,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed',
                'error' => $th->getMessage(),
            ], 401);
        }
    }
}
