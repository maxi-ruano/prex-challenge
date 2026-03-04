<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User; 

class AuthenticateWithToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token no proporcionado'
            ], 401);
        }

        $hashed = hash('sha256', $token);
        $user = User::where('api_token', $hashed)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido'
            ], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
