<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LoginCheck
{
    public function handle(Request $request, Closure $next)
    {
        try {
            if (!Auth::guard('api')->check()) {
                return response()->json([
                    'status' => 401,
                    'success' => false,
                    'message' => 'Unauthenticated! Invalid or expired token.',
                ], 401);
            }

            return $next($request);

        } catch (UnauthorizedHttpException $e) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Unauthenticated! Token is missing or unauthorized.',
            ], 401);
        } catch (\Exception $e) {
            \Log::error('LoginCheck Middleware Error: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Internal server error during authentication.',
            ], 500);
        }
    }
}
