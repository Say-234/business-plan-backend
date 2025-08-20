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
     * Handle an incoming authentication request.
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
     * Destroy an authenticated session.
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
