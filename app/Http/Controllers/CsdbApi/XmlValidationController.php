<?php

namespace App\Http\Controllers\CsdbApi;

use App\Events\Csdb\ValidatedByBrex;
use App\Http\Requests\Csdb\BrexValidation;
use App\Http\Requests\Csdb\XsiValidation;
use App\Models\Csdb;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Ptdi\Mpub\Validation\Validator\Xsi;

class XmlValidationController extends Controller
{
  public function xsi(XsiValidation $request)
  {
    $CSDBObjects = $request->validated('CSDBObjects');
    $l = count($CSDBObjects);
    $result = [
      'success' => [],
      'fail' => [],
    ];
    for ($i = 0; $i < $l; $i++) {
      $xsiValidation = new Xsi($CSDBObjects[$i]->document);
      $xsiValidation->validate();
      if ($xsiValidation->result()) {
        $result['fail'][] = $CSDBObjects[$i]->getFilename() . ", " . join(", ", $xsiValidation->errors->get('xsi_validation'));
      } else {
        $result['success'][] = $CSDBObjects[$i]->getFilename();
      }
    }

    $totalFail = count($result['fail']);
    $totalSuccess = count($result['success']);
    $infotype = $totalSuccess < 1 ? "warning" : ($totalFail > 0 ? 'caution' : 'note');
    $m = ($totalSuccess > 0 ? ("success: " . $totalSuccess . "/" . ($totalSuccess + $totalFail) . ", failure: " . $totalFail . "/" . ($totalSuccess + $totalFail)) : "fail: " . ($totalSuccess + $totalFail) . "/" . ($totalSuccess + $totalFail));
    $code = $totalSuccess && !$totalFail ? 200 : (!$totalSuccess ? 400 : 299);

    $responseContent = [
      'infotype' => $infotype,
      'message' => $m,
      'data' => [
        'success' => $result['success'],
      ]
    ];
    if ($code != 200) $responseContent['errors'] = [
      'failure' => $result['fail'],
    ];
    return Response::make($responseContent, $code, ['content-type' => 'application/json']);
  }

  public function brex(BrexValidation $request)
  {
    $message = '';
    $request->brexValidation->validate();
    $l = count($request->brexValidation->result());

    $message .= ($l) ? ($l . " item(s) match invalidate at for the structure object rule. ") : ('There is no invalidate object towards its brex file. ');
    $message .= 'Check your email for detail of results.';

    $code = $l ? 400 : 200;

    event(new ValidatedByBrex($request->user(), $request->brexValidation));

    return Response::make([
      'infotype' => ($l ? 'caution' : 'info'),
      'message' => $message,
    ], $code, ['content-type' => 'application/json']);
  }
}
