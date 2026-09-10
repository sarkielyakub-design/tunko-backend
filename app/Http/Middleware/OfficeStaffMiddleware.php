<?php

namespace App\Http\Middleware;

use App\Models\OfficeStaff;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OfficeStaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user instanceof OfficeStaff) {
            return response()->json(['success' => false, 'message' => 'Office staff authentication required.'], 403);
        }
        $user->loadMissing('office');
        if (!$user->is_active || !$user->office || !$user->office->is_active) {
            return response()->json(['success' => false, 'message' => 'Office staff account or office is inactive.'], 403);
        }
        return $next($request);
    }
}
