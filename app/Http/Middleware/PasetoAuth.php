<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PasetoService;
use Illuminate\Support\Facades\Cache;

class PasetoAuth
{
    protected PasetoService $pasetoService;

    public function __construct(PasetoService $pasetoService)
    {
        $this->pasetoService = $pasetoService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['code' => 401, 'message' => 'Unauthorized'], 401);
        }

        try {
            $parsed = $this->pasetoService->parseToken($token);
            $jti = $parsed->get('jti');

            if (Cache::has("blacklist:$jti")) {
                return response()->json(['code' => 401, 'message' => 'Token Revoked'], 401);
            }

            $request->attributes->set('auth_user_id', $parsed->get('user_id'));

            $request->setUserResolver(function () use ($parsed) {
                return \App\Models\SubUser::find($parsed->get('user_id'));
            });
        } catch (\Exception $e) {
            return response()->json(['code' => 401, 'message' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
