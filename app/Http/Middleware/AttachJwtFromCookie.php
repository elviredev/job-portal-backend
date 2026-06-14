<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttachJwtFromCookie
{
  /**
   * Handle an incoming request.
   *
   * @param Closure(Request): (Response) $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if(!$request->headers->has('Authorization') && $request->hasCookie('auth_token')) {
      $token = $request->cookie('auth_token');

      if(!empty($token)) {
        $request->headers->set('Authorization', 'Bearer ' . $token);
      }
    }
    return $next($request);
  }
}
