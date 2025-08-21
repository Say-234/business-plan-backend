<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Connexion utilisateur.
     *
     * @group Authentification
     *
     * Cette route permet à un utilisateur de se connecter avec ses identifiants.
     * Une session authentifiée est créée en cas de succès.
     *
     * @bodyParam email string requis L'adresse email de l'utilisateur. Exemple: user@example.com
     * @bodyParam password string requis Le mot de passe de l'utilisateur. Exemple: motdepasse123
     *
     * @response 204 "Connexion réussie, session créée"
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["Ces identifiants ne correspondent pas à nos enregistrements"]
     *   }
     * }
     */
    public function store(LoginRequest $request): Response|JsonResponse
    {
        $request->authenticate();

        $user = $request->user();

        if ($request->wantsJson()) {
            $token = $user->createToken('mobile-token')->plainTextToken;
            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        }

        $request->session()->regenerate();

        return response()->noContent();
    }

    /**
     * Déconnexion utilisateur.
     *
     * @group Authentification
     *
     * Cette route permet à un utilisateur connecté de se déconnecter.
     * La session est invalidée et le token CSRF régénéré.
     *
     * @response 204 "Déconnexion réussie, session invalidée"
     */
    public function destroy(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
