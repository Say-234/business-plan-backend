<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PartnersController extends Controller
{
    /**
     * Afficher la page des partenaires.
     *
     * @group Partenaires
     *
     * Cette route affiche la page des partenaires avec le nombre
     * de notifications non lues de l'utilisateur connecté.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "notificationcount": 3
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Utilisateur non authentifié"
     * }
     */
    public function index(){
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        $user = Auth::user();
        $notificationcount = Notification::where('user_id', $user->id)->where('read', 0)->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'notificationcount' => $notificationcount
            ]
        ]);
    }
}
