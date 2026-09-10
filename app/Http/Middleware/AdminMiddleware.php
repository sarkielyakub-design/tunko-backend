<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() instanceof Admin) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator authentication required.',
            ], 403);
        }

        if (!$request->user()->status) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator account is inactive.',
            ], 403);
        }

        return $next($request);
    }
}
