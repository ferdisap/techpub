<?php

namespace App\Http\Controllers;

use App\Models\Enterprise;
use Illuminate\Http\Request;
use Ptdi\Mpub\Main\Helper;

class EnterpriseController extends Controller
{
  public function get_enterprises(Request $request)
  {
    $EnterpriseModel = Enterprise::with('code');
    $query = Helper::generateWhereRawQueryString($request->get('sc'), 'enterprises');
    if ($query[0]) {
      $EnterpriseModel = $EnterpriseModel->whereRaw($query[0], $query[1]);
    }
    $EnterpriseModel = $EnterpriseModel->orderBy('name')->get();
    return $this->ret2(200, ['enterprises' => $EnterpriseModel]);
  }
}
