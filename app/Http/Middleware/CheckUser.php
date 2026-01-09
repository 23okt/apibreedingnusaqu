<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], 200);
        }

        $user = auth()->user();

        // Cek apakah user login
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Cek apakah role user sesuai
        if ($user->role !== $role) {
            return response()->json([
                'message' => 'You cant reach this endpoint'
            ], 403);
        }

        return $next($request);
    }
}