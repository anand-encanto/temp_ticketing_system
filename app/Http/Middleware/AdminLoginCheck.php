<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminLoginCheck
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
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'success' => false,
                    'message' => 'Unauthorized: Invalid or expired token.',
                ], Response::HTTP_UNAUTHORIZED); // 401
            }

            if (!$user->is_active) {
                return response()->json([
                    'status' => 403,
                    'success' => false,
                    'message' => 'Your account has been disabled. Please contact support.',
                ], Response::HTTP_FORBIDDEN); // 403
            }

            if ($user->role !== 'admin' && $user->role !== 'super_admin') {
                return response()->json([
                    'status' => 403,
                    'success' => false,
                    'message' => 'Forbidden: You do not have admin access.',
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
