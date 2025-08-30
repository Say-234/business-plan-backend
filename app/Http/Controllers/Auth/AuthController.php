<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Auth as FirebaseAuth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function exchangeFirebaseToken(Request $request, FirebaseAuth $firebaseAuth)
    {
        $validator = Validator::make($request->all(), [
            'firebase_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $verifiedIdToken = $firebaseAuth->verifyIdToken($request->firebase_token);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid Firebase token.'], 401);
        }

        $uid = $verifiedIdToken->claims()->get('sub');
        $email = $verifiedIdToken->claims()->get('email');

        $user = User::firstOrCreate(
            ['google_id' => $uid],
            ['email' => $email, 'password' => Hash::make(uniqid())] // Create a random password
        );

        $sanctumToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $sanctumToken,
            'user' => $user,
        ]);
    }

    /**
     * Login the user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Ces identifiants ne correspondent pas à nos enregistrements.'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]
        ]);
    }

    /**
     * Logout the user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Invalider le token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
