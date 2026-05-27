<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PasetoService;
use Illuminate\Support\Facades\Cache;

class PasetoAuth
{
    protected $pasetoService;

    public function __construct(PasetoService $pasetoService)
    {
        $this->pasetoService = $pasetoService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $parsed = $this->pasetoService->parseToken($token);
            $jti = $parsed->get('jti');

            if (Cache::has("blacklist:$jti")) {
                return response()->json(['message' => 'Token Revoked'], 401);
            }

            $request->attributes->set('auth_user_id', $parsed->get('user_id'));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
