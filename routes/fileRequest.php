<?php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get("requestFile/{aircraft}/{filename?}", function(Request $request, string $aircraft, string $filename){
  $available_dataType = [
    'csdb-brdp' => DIRECTORY_SEPARATOR.'csdb'.DIRECTORY_SEPARATOR.'business_rule'.DIRECTORY_SEPARATOR.'brdp',
    'csdb-brex' => DIRECTORY_SEPARATOR.'csdb'.DIRECTORY_SEPARATOR.'business_rule'.DIRECTORY_SEPARATOR.'brex',
    'brdp-decision' => DIRECTORY_SEPARATOR.'csdb'.DIRECTORY_SEPARATOR.'business_rule'.DIRECTORY_SEPARATOR.'decision',
    
    'csdb-csl' => DIRECTORY_SEPARATOR.'csdb'.DIRECTORY_SEPARATOR.'data_management_list'.DIRECTORY_SEPARATOR.'csl',
    'csdb-dmrl' => DIRECTORY_SEPARATOR.'csdb'.DIRECTORY_SEPARATOR.'data_management_list'.DIRECTORY_SEPARATOR.'dmrl',
    
    'view-brdp' => DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'business_rule'.DIRECTORY_SEPARATOR.'brdp',
    'view-brex' => DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'business_rule'.DIRECTORY_SEPARATOR.'brex',

    'general-xsl' => DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR. 'general'. DIRECTORY_SEPARATOR. 'xsl',

    'dump' =>  DIRECTORY_SEPARATOR.'dump',
    'multimedia' => DIRECTORY_SEPARATOR. 'csdb'. DIRECTORY_SEPARATOR. 'multimedia',

    'tesxsl' => DIRECTORY_SEPARATOR.'view/general/test',
  ];
  $dataType = $request->dataType;
  $path = base_path().DIRECTORY_SEPARATOR.'ietp_'.$aircraft.(isset($available_dataType[$dataType]) ? $available_dataType[$dataType] : '');

  $file = Controller::get_file($path, $filename);
  return response($file[0], 200, [
    'Content-Type' => $file[1]
  ]);
});

Route::get("generateAllStyle/{aircraft}", function (Request $request, string $aircraft){
  $path = base_path().DIRECTORY_SEPARATOR.'ietp_'.$aircraft. DIRECTORY_SEPARATOR. 'view'. DIRECTORY_SEPARATOR. 'general'. DIRECTORY_SEPARATOR. 'xsl';
  
  $list = array_diff(scandir($path), array('.','..'));
  return response()->json($list);
});