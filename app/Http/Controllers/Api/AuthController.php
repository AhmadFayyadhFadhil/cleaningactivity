<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login user dan kembalikan Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email atau password salah',
            ], 401);
        }

        /** @var User $user */
        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login berhasil',
            'data'    => [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                ],
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Logout user - hapus token saat ini.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        
if ($token) {
            // Sanctum token is a TransientToken in some contexts; fall back to delete if supported.
            if (method_exists($token, 'delete')) {
                $token->delete();
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil',
        ], 200);
    }

    /**
     * Tampilkan profil user yang sedang login.
     */
    public function profile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'roles'       => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ], 200);
    }
}