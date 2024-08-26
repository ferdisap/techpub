<?php

namespace App\Http\Controllers\Csdb;

use App\Http\Controllers\Controller;
use App\Models\Csdb;
use App\Models\Csdb\Dmc;
use Illuminate\Http\Request;
use Ptdi\Mpub\Main\Helper;

class DmcController extends Controller
{
  public function searchCsdbs(Request $request)
  {
    $CSDBModels = Csdb::getCsdbs(['exception' => ['CSDB-DELL', 'CSDB-PDEL']]);

    $CSDBModels = $CSDBModels->where('filename', 'like', 'DMC-%');

    $query = Helper::generateWhereRawQueryString($request->get('sc'),'csdb');
    $CSDBModels = $CSDBModels->whereRaw($query[0],$query[1]);

    if($request->limit){
      $CSDBModels = ($CSDBModels->limit($request->limit));
    }    
    return $this->ret2(200, ['result' => $CSDBModels->get()->toArray()]);
  }
}
