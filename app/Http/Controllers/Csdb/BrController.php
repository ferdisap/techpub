<?php

namespace App\Http\Controllers\Csdb;

use App\Events\Csdb\ValidatedByBrex;
use App\Http\Controllers\Controller;
use App\Http\Requests\Csdb\BrexValidation;
use App\Mail\Csdb\BrexValidationResult;
use App\Models\Csdb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

class BrController extends Controller
{
  public function json_file(Request $request, string $filename)
  {
    $BRObjectModel = Csdb::getObject($filename, ['exception' => ['CSDB-DELL', 'CSDB-PDEL']])->first();
    if(($BRObjectModel->infoCode != '024') || ($BRObjectModel->infoCode != '022')) return $this->ret2(400, ['The information code is not kind of business rule.']);
    return $this->ret2(400, ['json' => $BRObjectModel->json]);
  }

  public function validate(BrexValidation $request)
  {
    
    $message = '';
    $request->brexValidation->validate();

    // $mail = new BrexValidationResult(Auth::user(), $request->brexValidation->result());
    // $mail = Mail::to(new BrexValidationResult($event->initiator, $event->validator->result()));
    // $mail = Mail::to(Auth::user()->email)
    //   ->send($mail);
    // dd($mail);
    // event();
    
    $l = count($request->brexValidation->result());

    // $l_s = count($request->brexValidation->result()['contextRules']['structureObjectRuleGroup']);
    // $l_n = count($request->brexValidation->result()['contextRules']['notationRuleList']);
    // $message .= ($l_s || $l_n) ? ($l_s + $l_n + " item(s) match invalidated at for the structure object rule. ") : ('There is no invalidated object towards its brex file. ');
    $message .= ($l) ? ($l . " item(s) match invalidated at for the structure object rule. ") : ('There is no invalidated object towards its brex file. ');
    $message .= 'Check your email for detail of results.';

    event(new ValidatedByBrex(Auth::user(), $request->brexValidation));

    return $this->ret2(200,['infotype' => ($l ? 'caution' : 'info'),'message' => $message]);
  }
}
