<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExecutiveLoginCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Unauthorized: Token is missing.',
            ], Response::HTTP_UNAUTHORIZED); // 401
        }

        try {
            // This assumes you're using Laravel Passport or Sanctum
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'success' => false,
                    'message' => 'Unauthorized: Invalid or expired token.',
                ], Response::HTTP_UNAUTHORIZED); // 401
            }

            if ($user->role !== 'executive') {
                return response()->json([
                    'status' => 403,
                    'success' => false,
                    'message' => 'Forbidden: You do not have executive access.',
                ], Response::HTTP_FORBIDDEN); // 403
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Internal Server Error: Authentication failed.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
        }

        return $next($request);
    }
}
