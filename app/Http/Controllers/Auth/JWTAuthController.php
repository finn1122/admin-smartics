<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cookie;

class JWTAuthController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function login(Request $request)
    {
        Log::info('login');
        Log::debug($request);
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = auth()->user();
            Log::debug($user);

            if (!$user->hasVerifiedEmail()) {
                return response()->json(['error' => 'Email not verified'], 403);
            }

            if (!$user->active) {
                return response()->json(['error' => 'User account is inactive'], 403);
            }

            // Guardar el token en una cookie HTTP segura
            $cookie = Cookie::make('jwt_token', $token, 60, '/', null, false, true);

            Log::debug('login success');
            return response()->json([
                'message' => 'Login successful',
                'user' => $user
            ])->withCookie($cookie); // Adjuntar cookie a la respuesta

        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    // Get authenticated user
    public function getUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        return response()->json(compact('user'));
    }

    // User logout
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }
}
