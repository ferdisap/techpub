<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddHeaderToResponse
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $reqeustID = $request->headers->get('x-request-id');
    return $reqeustID ? $next($request)->header('X-RESPONSE-ID', $reqeustID) : $next($request);
  }
}
