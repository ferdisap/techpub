<?php

namespace App\Models\Csdb;

use App\Jobs\Csdb\DmcTableFiller;
use App\Models\Csdb;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;

class Dmc extends Csdb
{
  use HasFactory;

  protected $fillable = [
    'csdb_id', 

    'modelIdentCode',
    'systemDiffCode',
    'systemCode',
    'subSystemCode',
    'subSubSystemCode',
    'assyCode',
    'disassyCode',
    'disassyCodeVariant',
    'infoCode',
    'infoCodeVariant',
    'itemLocationCode',
    'languageIsoCode',
    'countryIsoCode',
    'issueNumber',
    'inWork',
    'year',
    'month',
    'day',
    'techName',
    'infoName',
    'infoNameVariant',

    'securityClassification',
    'responsiblePartnerCompany',
    'originator',
    'applicability',
    'brexDmRef',
    'qa',
    'remarks',

    'json',
    'xml'
  ];

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'dmc';

  /**
   * The model's default values for attributes.
   *
   * @var array
   */
  protected $attributes = [];

  /**
   * Indicates if the modul should be timestamped
   * 
   * @var bool
   */
  public $timestamps = false;

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = ['id', 'csdb_id', 'json', 'xml'];

  public static function fillTable($csdb_id, CSDBObject $CSDBObject){

    $filename = $CSDBObject->filename;
    $domXpath = new \DOMXpath($CSDBObject->document);

    $modelIdentCode = $domXpath->evaluate("string(//dmAddress/dmIdent/dmCode/@modelIdentCode)");
    $systemDiffCode = $domXpath->evaluate("string(//dmAddress/dmIdent/dmCode/@systemDiffCode)");
    $systemCode = $domXpath->evaluate("string(//dmAddress/dmIdent/dmCode/@systemCode)");
    $subSystemCode = $domXpath->evaluate("string(//dmAddress/dmIdent/dmCode/@subSystemCode)");
    $subSubSystemCode = $domXpath->evaluate("string(//dmAddress/dmIdent/dmCode/@subSubSystemCode)");
    $assyCode = $domXpath->evaluate("string(//dmAddress/dmIdent/dmCode/@assyCode)");
    $disassyCode = $domXpath->evaluate("string(//dmAddress/dmIdent/dmCode/@disassyCode)");
    $disassyCodeVariant = $domXpath->evaluate("string(//dmAddress/dmIdent/dmCode/@disassyCodeVariant)");
    $infoCode = $domXpath->evaluate("string(//dmAddress/dmIdent/dmCode/@infoCode)");
    $infoCodeVariant = $domXpath->evaluate("string(//dmAddress/dmIdent/dmCode/@infoCodeVariant)");
    $itemLocationCode = $domXpath->evaluate("string(//dmAddress/dmIdent/dmCode/@itemLocationCode)");
    $languageIsoCode = $domXpath->evaluate("string(//dmAddress/dmIdent/language/@languageIsoCode)");
    $countryIsoCode = $domXpath->evaluate("string(//dmAddress/dmIdent/language/@countryIsoCode)");

    $issueNumber = $domXpath->evaluate("string(//dmAddress/dmIdent/issueInfo/@issueNumber)");
    $inWork = $domXpath->evaluate("string(//dmAddress/dmIdent/issueInfo/@inWork)");

    $year = $domXpath->evaluate("string(//dmAddress/dmAddressItems/issueDate/@year)");
    $month = $domXpath->evaluate("string(//dmAddress/dmAddressItems/issueDate/@month)");
    $day = $domXpath->evaluate("string(//dmAddress/dmAddressItems/issueDate/@day)");

    $techName = $domXpath->evaluate("string(//dmAddress/dmAddressItems/dmTitle/techName)");
    $infoName = $domXpath->evaluate("string(//dmAddress/dmAddressItems/dmTitle/infoName)");
    $infoNameVariant = $domXpath->evaluate("string(//dmAddress/dmAddressItems/dmTitle/infoNameVariant)");

    $securityClassification = $domXpath->evaluate("string(//dmStatus/security/@securityClassification)");
    $brexElement = $domXpath->evaluate("//identAndStatusSection/descendant::brexDmRef/dmRef/dmRefIdent")[0];
    $brexDmRef = CSDBStatic::resolve_dmIdent($brexElement);
    $rsp = $domXpath->evaluate("string(//identAndStatusSection/descendant::responsiblePartnerCompany/enterpriseName)");
    $originator = $domXpath->evaluate("string(//identAndStatusSection/descendant::originator/enterpriseName)");
    $applicEl = $domXpath->evaluate("//identAndStatusSection/descendant::applic")[0];
    $applic = $CSDBObject->getApplicability($applicEl);

    $QA = $domXpath->evaluate("//identAndStatusSection/descendant::qualityAssurance/*[last()]")[0];
    $QAtext = $CSDBObject->getQA(null, $QA);
    $remarks = $CSDBObject->getRemarks($domXpath->evaluate("//identAndStatusSection/descendant::remarks")[0]);

    $arr = [
      "csdb_id" => $csdb_id,

      "modelIdentCode" => $modelIdentCode,
      "systemDiffCode" => $systemDiffCode,
      "systemCode" => $systemCode,
      "subSystemCode" => $subSystemCode,
      "subSubSystemCode" => $subSubSystemCode,
      "assyCode" => $assyCode,
      'disassyCode' => $disassyCode,
      'disassyCodeVariant' => $disassyCodeVariant,
      'infoCode' => $infoCode,
      'infoCodeVariant' => $infoCodeVariant,
      'itemLocationCode' => $itemLocationCode,
      'languageIsoCode' => $languageIsoCode,
      'countryIsoCode' => $countryIsoCode,
      
      'issueNumber' => $issueNumber,
      'inWork' => $inWork,

      'year' => $year,
      'month' => $month,
      'day' => $day,

      "techName" => $techName,
      "infoName" => $infoName,
      "infoNameVariant" => $infoNameVariant,

      'securityClassification' => $securityClassification,
      'responsiblePartnerCompany' => $rsp,
      'originator' => $originator,
      'applicability' => $applic,
      'brexDmRef' => $brexDmRef,
      'qa' => $QAtext,
      'remarks' => $remarks,

      'json' => CSDBStatic::xml_to_json($CSDBObject->document),
      'xml' => $CSDBObject->document->C14N() // ga bisa pakai saveXML karena menghasilkan doctype, sementara SQL XML belum tahu caranya render xml yang ada dtd
    ];

    $dmc = Csdb::getObject($filename)->first() ?? Csdb::getModelClass('Dmc');
    $dmc->timestamps = false;
    foreach($arr as $prop => $v){
      $dmc->$prop = $v;
    }
    return $dmc->save() ? $dmc : false;
  }
}
