<?php

namespace App\Http\Controllers\Csdb;

use App\Http\Controllers\Controller;
use App\Models\Csdb;
use App\Models\Csdb\History;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
  public function all(Request $request, string $filename)
  {    
    // 2x query, pertama query Csdb, kemudian query History.
    if($CSDBModel = Csdb::where('filename', $filename)->where('storage_id', $request->user()->id)->first()){
      $CSDBModel->attachHistories(50);
      $CSDBModel = $CSDBModel->toArray();
  
      return $this->ret2(200, ['csdb' => $CSDBModel]);
    }
    return abort(404);
  }
}
