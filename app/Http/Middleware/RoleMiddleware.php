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
   * @param Closure(Request): (Response) $next
   */
  public function handle(Request $request, Closure $next, string ...$roles): Response
  {
    $user = auth('api')->user();

    if (!$user || !in_array($user->role, $roles)) {
      return response()->json([
        'message' => 'Forbidden. You do not have the required role to access this resource.',
      ], 403);
    }

    return $next($request);
  }
}
