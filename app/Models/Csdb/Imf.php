<?php

namespace App\Models\Csdb;

use App\Models\Csdb;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;

class Imf extends Csdb
{
  use HasFactory;

  protected $fillable = [
    'csdb_id',
      
    'icnTitle',
    'imfIdentIcn',
    'legacyIdents',
    'icnKeywords',

    'year',
    'month',
    'day',
    
    'securityClassification',
    'responsiblePartnerCompany',
    'brexDmRef',
    'remarks',
    'qa',
    'remarks',

    'json',
    'xml',
  ];

  protected $table = 'imf';

  protected $hidden = ['id', 'csdb_id', 'json', 'xml'];

  public $timestamps = false;
  
  public static function fillTable($csdb_id, CSDBObject $CSDBObject, int $storage_id)
  {
    $filename = $CSDBObject->filename;
    $domXpath = new \DOMXpath($CSDBObject->document);

    $legacyIdents = array_map(function(\DOMElement $legacyIdent) {
      CSDBStatic::simple_decode_element($legacyIdent, $v);
      return $v;
    },[...$domXpath->evaluate("//imfAddressItems/legacyIdentGroup/legacyIdent")]);

    $icnKeywords = array_map(function(\DOMElement $icnKeyword){
      return $icnKeyword->textContent;
    },[...$domXpath->evaluate("//imfAddressItems/icnKeywordGroup/icnKeyword")]);

    $brexElement = $domXpath->evaluate("//imfIdentAndStatusSection/descendant::brexDmRef/dmRef/dmRefIdent");
    $brexDmRef = isset($brexElement[0]) ? CSDBStatic::resolve_dmIdent($brexElement[0]) : null;

    $QA = $domXpath->evaluate("//identAndStatusSection/descendant::qualityAssurance/*[last()]");
    $QAtext = isset($QA[0]) ? $CSDBObject->getQA(null, $QA[0]) : '';

    $arr = [
      "csdb_id" => $csdb_id,

      'icnTitle' => $domXpath->evaluate("string(//imfAddressItems/icnTitle)"),
      'imfIdentIcn' => $domXpath->evaluate("string(//imfAddress/imfIdent/imfCode/@imfIdentIcn)"),
      'legacyIdents' => !empty($legacyIdents) ? json_encode($legacyIdents) : null,
      'icnKeywords' => !empty($icnKeywords) ? json_encode($icnKeywords) : null,
  
      'year' => $domXpath->evaluate("string(//imfAddress/imfAddressItems/issueDate/@year)"),
      'month' => $domXpath->evaluate("string(//imfAddress/imfAddressItems/issueDate/@month)"),
      'day' => $domXpath->evaluate("string(//imfAddress/imfAddressItems/issueDate/@day)"),
      
      'securityClassification' => $domXpath->evaluate("string(//dmStatus/security/@securityClassification)"),
      'responsiblePartnerCompany' => $domXpath->evaluate("string(//identAndStatusSection/descendant::responsiblePartnerCompany/enterpriseName)"),
      'brexDmRef' => $brexDmRef,
      'qa' => $QAtext,
      'remarks' => $CSDBObject->getRemarks($domXpath->evaluate("//imfIdentAndStatusSection/descendant::remarks")[0]),
  
      'json' => CSDBStatic::xml_to_json($CSDBObject->document),
      'xml' => $CSDBObject->document->C14N() // ga bisa pakai saveXML karena menghasilkan doctype, sementara SQL XML belum tahu caranya render xml yang ada dtd
    ];

    $imf = Csdb::getObject($filename,[], $storage_id)->first() ?? Csdb::getModelClass('Imf');
    $imf->timestamps = false;
    foreach($arr as $prop => $v){
      $imf->$prop = $v;
    }
    return $imf->save() ? $imf : false;
  }  
}
