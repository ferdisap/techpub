<?php

namespace App\Http\Middleware\Csdb;

use App\Models\Csdb;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ViaDDN
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $ddnFilename = $request->get('ddn');
    if(!$ddnFilename || (substr($ddnFilename, 0,3) !== 'DDN')) return $next($request);

    // get ddn object
    Csdb::$storage_user_id = null;
    $DDNModel = Csdb::getObject($ddnFilename)->first(['id','dispatchTo_id','dispatchFrom_id']);    
    if(!$DDNModel) {
      Csdb::$storage_user_id = 0; // dibalikin lagi
      return $next($request);
    }

    // set storage_user id as per ddn dispatchFrom (owner)
    if($DDNModel->dispatchTo_id != $request->user()->id){
      Csdb::$storage_user_id = (int) $DDNModel->dispatchFrom_id;
    }

    return $next($request);
  }
}
