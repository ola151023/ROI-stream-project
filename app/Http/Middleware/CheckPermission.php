<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permissions): Response
    {
        if (!Auth()->check()) {
            return response()->json(['message' => 'You are not authenticated'], 401);
        }

        //Checking if the user has permission: 
        //     foreach ($permissions as $permission) {
        //         if (auth()->user()->hasPermission($permission)) {
        //             return $next($request);
        //         }
        //     }
        //     return response()->json(['message' => 'Permission denied'], 403);
        // }
    }
}
