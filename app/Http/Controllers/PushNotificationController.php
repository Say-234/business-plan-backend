<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Minishlink\WebPush\WebPush;
use App\Models\PushSubscription;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Minishlink\WebPush\Subscription;
use App\Services\FirebaseService;

class PushNotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Envoyer une notification push.
     *
     * @group Notifications Push
     *
     * Cette route permet d'envoyer une notification push à l'utilisateur connecté
     * via Firebase Cloud Messaging et de l'enregistrer en base de données.
     *
     * @bodyParam title string requis Le titre de la notification. Exemple: Nouvelle mise à jour
     * @bodyParam body string requis Le contenu de la notification. Exemple: Votre business plan a été mis à jour
     * @bodyParam data array optionnel Données additionnelles. Exemple: {"action":"open_document","document_id":1}
     *
     * @response 200 {
     *   "success": true
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "title": ["Le champ title est requis"]
     *   }
     * }
     */
    public function sendPushNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $token = Auth::user()->fcm_token;
        $title = $request->input('title');
        $body = $request->input('body');
        $data = $request->input('data', []);

        $this->firebaseService->sendNotification($token, $title, $body, $data);

        $notif = new Notification([
            'user_id' => Auth::user()->id,
            'title' => $title,
            'body' => $body,
        ]);
        $notif->save();

        return response()->json(['success' => true]);
    }

    /**
     * Enregistrer le token FCM de l'utilisateur.
     *
     * @group Notifications Push
     *
     * Cette route permet d'enregistrer le token Firebase Cloud Messaging
     * de l'utilisateur pour recevoir des notifications push.
     *
     * @bodyParam fcm_token string requis Le token FCM de l'appareil. Exemple: eXample_token_123
     *
     * @response 200 {
     *   "message": "Token enregistré"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "fcm_token": ["Le champ fcm_token est requis"]
     *   }
     * }
     */
    public function getToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $token = $request->fcm_token;
        $title = "Subscription";
        $body = "Vous avez autorisé les notif push";
        $data = [];

        $this->firebaseService->sendNotification($token, $title, $body, $data);
        $user = User::where('id', Auth::user()->id)->first();
        if ($user) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }
        $notif = new Notification([
            'user_id' => Auth::user()->id,
            'title' => $title,
            'body' => $body,
        ]);
        $notif->save();

        return response()->json(['message' => 'Token enregistré']);
    }

    /**
     * Marquer une notification comme lue.
     *
     * @group Notifications Push
     *
     * Cette route permet de marquer une notification spécifique comme lue
     * pour l'utilisateur connecté.
     *
     * @bodyParam notification_id integer requis L'ID de la notification. Exemple: 1
     *
     * @response 200 {
     *   "message": "Notification marquée comme lue"
     * }
     * @response 404 {
     *   "message": "Notification introuvable"
     * }
     */
    public function markNotificationAsRead(Request $request)
    {
        $notification_id = $request->notification_id;
        $notification = Notification::find($notification_id);
        
        if ($notification && $notification->user_id == Auth::user()->id) {
            $notification->read = 1;
            $notification->save();
            
            return response()->json(['message' => 'Notification marquée comme lue']);
        }
        
        return response()->json(['message' => 'Notification introuvable'], 404);
    }
}
