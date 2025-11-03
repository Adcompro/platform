<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Super Admin can access everything
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Check if user has one of the required roles
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Check role hierarchy - higher roles can access lower role functions
        $roleHierarchy = [
            'super_admin' => 5,
            'admin' => 4,
            'project_manager' => 3,
            'user' => 2,
            'reader' => 1,
        ];

        $userLevel = $roleHierarchy[$user->role] ?? 0;
        $requiredLevel = 0;

        foreach ($roles as $role) {
            $requiredLevel = max($requiredLevel, $roleHierarchy[$role] ?? 0);
        }

        if ($userLevel >= $requiredLevel) {
            return $next($request);
        }

        // Access denied
        abort(403, 'Access denied. Insufficient permissions.');
    }
}