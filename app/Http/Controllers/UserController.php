<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ptdi\Mpub\Main\Helper;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
  /**
   * querykey = sc?string, limit?integer/10
   */
  public function searchModel(Request $request)
  {
    $USERModel = User::without('work_enterprise');
    if($request->sc){
      try {
        list($k, $v) = explode("::",$request->sc);
        $scValue = '%' . $v . '%';
      } catch (\Throwable $th) {
        $scValue = "%" . + $request->sc ? $request->sc + '%' : '';
      }
      $scKey = [
        'last_name',
        'first_name',
        'middle_name',
        'job_title',
        'email',
        'address',
      ];
  
      $queryWhereRaw = '';
      for ($i=0; $i < count($scKey); $i++) { 
        $queryWhereRaw .= " $scKey[$i] LIKE ? ";
        if(isset($scKey[$i+1])) $queryWhereRaw .= " OR ";
      }
      $USERModel = $USERModel->whereRaw($queryWhereRaw, [$scValue,$scValue,$scValue,$scValue,$scValue,$scValue]);
    }

    $USERModel = ($USERModel->limit($request->limit ?? 10))->get(['first_name', 'middle_name', 'last_name', 'email']);

    // return $this->ret2(200, ['result' => $USERModel]);
    return Response::make([
      "users" => $USERModel
    ]);
  }
}
