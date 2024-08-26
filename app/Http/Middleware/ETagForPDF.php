<?php

namespace App\Http\Middleware;

use App\Models\Csdb;
use App\Models\Csdb\History;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as FacadesResponse;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * masih belum mengakomodir header If-Match
 */
class ETagForPDF
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    // handle request
    $method = $request->getMethod();

    // support using HEAD method for checking IF-None-Match
    if($request->isMethod('HEAD')) $request->setMethod('GET');
    
    // Find CSDBModel by filename
    $filename = $request->route()->parameter('filename');
    $CSDBModel = Csdb::getCsdb($filename,['exception' => ['CSDB-DELL', 'CSDB-PDEL']]);
    $CSDBModel = $CSDBModel->with(['lastHistory'])->first(); // return null if match history exception;
    if(!$CSDBModel) abort(410,$filename . " not found or has been deleted.");
    
    // set ETag
    $etag = '"'.md5($CSDBModel->filename."___".$CSDBModel->lastHistory->created_at).'"';

    // check If-None-Match
    $noneMatch = $request->getEtags();
    if(in_array($etag, $noneMatch)) return FacadesResponse::make()->setNotModified();

    // change prevouse route param filename into CSDBModel
    $request->route()->setParameter('filename', $CSDBModel);

    // handling response
    $response = $next($request);

    // set ETag to response
    $response->setEtag($etag);

    $request->setMethod($method);

    return $response;
  }
}
