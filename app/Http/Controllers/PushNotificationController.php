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
