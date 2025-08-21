<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    /**
     * Afficher les informations du profil utilisateur.
     *
     * @group Profil Utilisateur
     *
     * Cette route permet de récupérer les informations du profil de l'utilisateur connecté.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Jean Dupont",
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Utilisateur non authentifié"
     * }
     */
    public function edit(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()
            ]
        ]);
    }

    /**
     * Mettre à jour le profil utilisateur.
     *
     * @group Profil Utilisateur
     *
     * Cette route permet de mettre à jour les informations du profil utilisateur.
     * Si l'email est modifié, la vérification email est réinitialisée.
     *
     * @bodyParam name string requis Le nom de l'utilisateur. Exemple: John Doe
     * @bodyParam email string requis L'adresse email. Exemple: john@example.com
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Profil mis à jour avec succès",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "email_verified_at": null
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Utilisateur non authentifié"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Erreur de validation",
     *   "errors": {
     *     "email": ["Cette adresse email est déjà utilisée"]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Erreur lors de la mise à jour du profil"
     * }
     */
    public function update(ProfileUpdateRequest $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'data' => [
                'user' => $request->user()
            ]
        ]);
    }

    /**
     * Supprimer le compte utilisateur.
     *
     * @group Profil Utilisateur
     *
     * Cette route permet de supprimer définitivement le compte utilisateur.
     * Le mot de passe actuel est requis pour confirmer la suppression.
     *
     * @bodyParam password string requis Le mot de passe actuel pour confirmation. Exemple: motdepasse123
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Compte supprimé avec succès"
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Utilisateur non authentifié"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Mot de passe incorrect"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Erreur lors de la suppression du compte"
     * }
     */
    public function destroy(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        try {
            $request->validate([
                'password' => ['required', 'current_password'],
            ]);
    
            $user = $request->user();
    
            Auth::logout();
    
            $user->delete();
    
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Compte supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du compte',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
