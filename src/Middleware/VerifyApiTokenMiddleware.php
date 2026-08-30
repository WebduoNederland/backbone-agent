<?php

namespace WebduoNederland\BackboneAgent\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (blank($bearerToken)) {
            return response()->json([
                'success' => false,
                'message' => 'No bearer token given!',
            ], 401);
        }

        $apiKey = config()->string('backbone-agent.api_key', '');

        if (blank($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'No API key configured in the env!',
            ], 500);
        }

        if ($bearerToken !== $apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid bearer token given!',
            ], 401);
        }

        return $next($request);
    }
}
