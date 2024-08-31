<?php

namespace App\Http\Controllers\Csdb;

use App\Http\Controllers\Controller;
use App\Http\Requests\Csdb\BrexValidation;
use App\Models\Csdb;
use Illuminate\Http\Request;

class BrController extends Controller
{
  public function json_file(Request $request, string $filename)
  {
    $BRObjectModel = Csdb::getObject($filename, ['exception' => ['CSDB-DELL', 'CSDB-PDEL']])->first();
    if(($BRObjectModel->infoCode != '024') || ($BRObjectModel->infoCode != '022')) return $this->ret2(400, ['The information code is not kind of business rule.']);
    return $this->ret2(400, ['json' => $BRObjectModel->json]);
  }

  public function validate_brex(BrexValidation $request)
  {
    dd($request);
  }
}
