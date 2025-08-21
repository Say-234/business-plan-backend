<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterMailable;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterMailable;

class RegisteredUserController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur.
     *
     * @group Authentification
     *
     * Cette route permet d'inscrire un nouvel utilisateur avec son email.
     * Un mot de passe aléatoire est généré et envoyé par email.
     *
     * @bodyParam email string requis L'adresse email de l'utilisateur. Exemple: user@example.com
     *
     * @response 204 "Inscription réussie, utilisateur connecté automatiquement"
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["Cette adresse email est déjà utilisée"]
     *   }
     * }
     * @response 500 {
     *   "message": "Erreur lors de l'inscription"
     * }
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response|JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
        ]);

        DB::beginTransaction();

        do {
            $password = $this->generateUniquePassword();
        } while (User::where('password', $password)->exists());

        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($password),
            ]);

            DB::commit();

            Mail::to($request->email)->send(new RegisterMailable($request->email, $password));
            event(new Registered($user));

            if ($request->wantsJson()) {
                $token = $user->createToken('mobile-token')->plainTextToken;
                return response()->json([
                    'user' => $user,
                    'token' => $token,
                ]);
            }

            Auth::login($user);

            return response()->noContent();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    private function generateUniquePassword($length = 10)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_!@#$%^&*()<>?';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
