<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ptdi\Mpub\Main\Helper;

class UserController extends Controller
{
  public function searchModel(Request $request)
  {
    $USERModel = new User();
    $sc = '%' . explode("::",$request->get('sc'))[1] . '%';
    $column = [
      'last_name',
      'first_name',
      'middle_name',
      'job_title',
      'email',
      'address',
    ];

    $queryWhereRaw = '';
    for ($i=0; $i < count($column); $i++) { 
      $queryWhereRaw .= " $column[$i] LIKE ? ";
      if(isset($column[$i+1])) $queryWhereRaw .= " OR ";
    }
    $USERModel = $USERModel->whereRaw($queryWhereRaw, [$sc,$sc,$sc,$sc,$sc,$sc]);

    if($request->limit){
      $USERModel = ($USERModel->limit($request->limit));
    }

    return $this->ret2(200, ['result' => $USERModel->get()->toArray()]);
  }
}
