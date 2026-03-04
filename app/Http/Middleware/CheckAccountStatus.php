<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status !== 'approved' && !$user->is_admin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is pending admin approval. Current status: ' . $user->status
                ], 403);
            }

            // For web, we simply don't let them pass to sensitive routes.
            // If they are on a protected route, we redirect them to the dashboard where they see the status message.
            return redirect()->route('dashboard')->with('error', 'Access denied. Your account is pending admin approval.');
        }

        return $next($request);
    }
}
