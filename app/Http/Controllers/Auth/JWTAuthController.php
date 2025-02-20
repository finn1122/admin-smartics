<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    // User login
    public function login(Request $request)
    {
        Log::info('login');
        Log::debug($request);
        $credentials = $request->only('email', 'password');

        try {
            // Intentar autenticar al usuario con las credenciales proporcionadas.
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // Obtener el usuario autenticado.
            $user = auth()->user();

            // Verificar si el usuario ha verificado su correo electrónico.
            if (!$user->hasVerifiedEmail()) {
                return response()->json(['error' => 'Email not verified'], 403); // Código de estado 403 para prohibido.
            }

            // Verificar si el usuario está activo.
            if (!$user->active) {
                return response()->json(['error' => 'User account is inactive'], 403); // Usuario inactivo.
            }

            // (opcional) Adjuntar el rol al token.
            $token = JWTAuth::claims(['role' => $user->getRoleNames()])->fromUser($user);

            $userProfile = $this->userService->getUserProfile($user);

            Log::debug('login success');
            return response()->json([
                'token' => $token,
                'user' => $userProfile
            ]);
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
