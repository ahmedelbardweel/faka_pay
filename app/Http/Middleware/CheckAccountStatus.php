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

            // For web, if they are not approved, they shouldn't be able to access wallet etc.
            // We can redirect them back with a message or to a custom "pending" view
            return redirect()->route('dashboard')->with('error', 'Your account is pending admin approval.');
        }

        return $next($request);
    }
}
