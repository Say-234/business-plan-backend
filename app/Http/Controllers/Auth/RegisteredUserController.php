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

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
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
