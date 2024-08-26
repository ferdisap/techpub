<?php

namespace App\Models\Csdb;

use App\Casts\Csdb\Ddn\DdnContentCast;
use App\Casts\Csdb\RemarksCast;
use App\Models\Csdb;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ptdi\Mpub\Main\CSDBStatic;
use Illuminate\Support\Facades\Auth;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\Helper;

class Ddn extends Csdb
{
  use HasFactory;

  protected $fillable = [
    'csdb_id',

    'modelIdentCode',
    'senderIdent',
    'receiverIdent',
    'yearOfDataIssue',
    'seqNumber',

    "year",
    "month",
    "day",

    "dispatchTo_id",
    "dispatchFrom_id",

    'securityClassification',
    'brexDmRef',
    'authorization',
    'remarks',

    'ddnContent',

    'json',
    'xml'
  ];

  protected $table = 'ddn';

  protected $attributes = [
    'ddnContent' => '[]',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = ['id', 'csdb_id', 'dispatchTo_id', 'dispatchFrom_id' ,'json', 'xml'];

  public $timestamps = false;

  /**
   * The attributes that should be cast.
   * @var array
   */
  protected $casts = [
    'ddnContent' => DdnContentCast::class,
    'remarks' => RemarksCast::class,
  ];

  /**
   * relationship untuk user
   */
  public function dispatchTo() :BelongsTo
  {
    return $this->belongsTo(User::class,'dispatchTo_id', 'id');
  }

  /**
   * relationship untuk user
   */
  public function dispatchFrom() :BelongsTo
  {
    return $this->belongsTo(User::class,'dispatchFrom_id');
  }

  public function create_xml(string $storagePath, Array $params)
  {
    $this->CSDBObject = new CSDBObject('5.0');
    $this->CSDBObject->setPath(CSDB_STORAGE_PATH . "/" . $storagePath);
    $this->CSDBObject->setConfigXML(CSDB_VIEW_PATH . DIRECTORY_SEPARATOR . "xsl" . DIRECTORY_SEPARATOR . "Config.xml"); // nanti diubah mungkin berbeda antara pdf dan html meskupun harusnya SAMA. Nanti ConfigXML mungkin tidak diperlukan jika fitur BREX sudah siap sepenuhnya.
    $this->CSDBObject->createDDN($params);

    if($this->CSDBObject->document){
      return true;
    }
    return false;
  }

  public static function fillTable($csdb_id, CSDBObject $CSDBObject)
  {
    $filename = $CSDBObject->filename;
    // $decode_ident = CSDBStatic::decode_ddnIdent($filename,false); 
    // $ddnAddressItems = $CSDBObject->document->getElementsByTagName('ddnAddressItems')[0];
    // $issueDate = $ddnAddressItems->firstElementChild;

    // $domXpath = new \DOMXpath($CSDBObject->document);
    // $sc = $domXpath->evaluate("string(//identAndStatusSection/descendant::security/@securityClassification)");
    // $authorization = $domXpath->evaluate("string(//identAndStatusSection/descendant::authorization)");
    // $brexElement = $domXpath->evaluate("//identAndStatusSection/descendant::brexDmRef/dmRef/dmRefIdent")[0];
    // $brexDmRef = CSDBStatic::resolve_dmIdent($brexElement);
    // $remarks = $CSDBObject->getRemarks($domXpath->evaluate("//identAndStatusSection/descendant::remarks")[0]);

    $domXpath = new \DOMXpath($CSDBObject->document);
    $modelIdentCode = $domXpath->evaluate("string(//ddnAddress/ddnIdent/ddnCode/@modelIdentCode)");
    $senderIdent = $domXpath->evaluate("string(//ddnAddress/ddnIdent/ddnCode/@senderIdent)");
    $receiverIdent = $domXpath->evaluate("string(//ddnAddress/ddnIdent/ddnCode/@receiverIdent)");
    $yearOfDataIssue = $domXpath->evaluate("string(//ddnAddress/ddnIdent/ddnCode/@yearOfDataIssue)");
    $seqNumber = $domXpath->evaluate("string(//ddnAddress/ddnIdent/ddnCode/@seqNumber)");

    $year = $domXpath->evaluate("string(//ddnAddress/ddnAddressItems/issueDate/@year)");
    $month = $domXpath->evaluate("string(//ddnAddress/ddnAddressItems/issueDate/@month)");
    $day = $domXpath->evaluate("string(//ddnAddress/ddnAddressItems/issueDate/@day)");

    $securityClassification = $domXpath->evaluate("string(//ddnStatus/security/@securityClassification)");
    $brexElement = $domXpath->evaluate("//identAndStatusSection/descendant::brexDmRef/dmRef/dmRefIdent")[0];
    $brexDmRef = CSDBStatic::resolve_dmIdent($brexElement);
    $authorization = $domXpath->evaluate("string(//identAndStatusSection/descendant::authorization)");
    $remarks = $CSDBObject->getRemarks($domXpath->evaluate("//identAndStatusSection/descendant::remarks")[0]);

    $dispatchTo_id = User::where('last_name', $domXpath->evaluate("string(//ddnAddressItems/dispatchTo/dispatchAddress/dispatchPerson/lastName)"));
    if($firstName = $domXpath->evaluate("string(//ddnAddressItems/dispatchTo/dispatchAddress/dispatchPerson/firstName)")) $dispatchTo_id->where('first_name', $firstName);
    if(count(($dispatchTo_id = $dispatchTo_id->get('id'))) > 1) $dispatchTo_id = 0;
    else $dispatchTo_id = $dispatchTo_id[0]->id;

    $dispatchFrom_id = User::where('last_name', $domXpath->evaluate("string(//ddnAddressItems/dispatchFrom/dispatchAddress/dispatchPerson/lastName)"));
    if($firstName = $domXpath->evaluate("string(//ddnAddressItems/dispatchFrom/dispatchAddress/dispatchPerson/firstName)")) $dispatchFrom_id->where('first_name', $firstName);
    if(count(($dispatchFrom_id = $dispatchFrom_id->get('id'))) > 1) $dispatchFrom_id = 0;
    else $dispatchFrom_id = $dispatchFrom_id[0]->id;

    $ddnContent = $domXpath->evaluate("//ddnContent/descendant::dispatchFileName|//ddnContent/mediaIdent");
    if(!empty($ddnContent)){
      $r = [];
      foreach($ddnContent as $content){
        switch ($content->tagName) {
          case 'dispatchFileName':
            $r[] = $content->nodeValue;
            break;
          case 'mediaIdent':
            // TBD
            break;
        }
      }
      $ddnContent = $r;
    } else {
      $ddnContent = [];
    }

    $arr = [
      "csdb_id" => $csdb_id,
      
      'modelIdentCode' => $modelIdentCode,
      'senderIdent' => $senderIdent,
      'receiverIdent' => $receiverIdent,
      'yearOfDataIssue' => $yearOfDataIssue,
      'seqNumber' => $seqNumber,
      
      "year" => $year,
      "month" => $month,
      "day" => $day,

      "dispatchTo_id" => $dispatchTo_id,
      "dispatchFrom_id" => $dispatchFrom_id,
      
      'securityClassification' => $securityClassification,
      'brexDmRef' => $brexDmRef,
      'authorization' => $authorization,
      'remarks' => $remarks,
      
      'ddnContent' => $ddnContent,

      'json' => CSDBStatic::xml_to_json($CSDBObject->document),
      'xml' => $CSDBObject->document->C14N() // ga bisa pakai saveXML karena menghasilkan doctype, sementara SQL XML belum tahu caranya render xml yang ada dtd
    ];

    $ddn = Csdb::getObject($filename)->first() ?? Csdb::getModelClass('Ddn');
    foreach($arr as $prop => $v){
      $ddn->$prop = $v;
    }
    return $ddn->save() ? $ddn : false;
  }
}
