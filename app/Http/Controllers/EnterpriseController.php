<?php

namespace App\Http\Controllers;

use App\Models\Enterprise;
use Illuminate\Http\Request;
use Ptdi\Mpub\Main\Helper;
use Illuminate\Support\Facades\Response;

class EnterpriseController extends Controller
{
  /**
   * @deprecated
   */
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

  public function lists(Request $request)
  {
    $EnterpriseModel = Enterprise::with('code');

    // handle sc 
    $query = Helper::generateWhereRawQueryString($request->get('sc'), 'enterprises');
    if ($query[0]) {
      $EnterpriseModel = $EnterpriseModel->whereRaw($query[0], $query[1]);
    }

    // handle limit
    if ($request->limit) {
      $EnterpriseModel->limit($request->limit);
    } else {
      $EnterpriseModel->limit(10);
    }

    return Response::make([
      "enterprises" => $EnterpriseModel->orderBy('name')->get(),
    ], 200, ["content-type" => 'application/json']);
  }
}
