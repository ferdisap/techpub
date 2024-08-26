<?php

namespace App\Models\Csdb;

use App\Casts\Csdb\Comment\CommentContentCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Csdb;
use Illuminate\Support\Facades\Auth;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Ptdi\Mpub\Main\Helper;

class Comment extends Csdb
{
  use HasFactory;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'comment';

  /**
   * saat create
   * jika tidak kita masukkan valunya maka ini berjalan DAN fungsi :Attribute TIDAK berjalan
   * jika kita masukin valuenya walaupun NULL, maka ini TIDAK berjalan DAN fungsi :Attribute berjalan
   */
  protected $attributes = [
    'commentRefs' => '[]',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  // protected $hidden = ['id', 'csdb_id', 'json', 'xml'];
  protected $hidden = ['csdb_id', 'json', 'xml'];

  /**
   * The attributes that should be cast.
   * @var array
   */
  protected $casts = [
    'commentRefs' => 'json',
    // 'commentContent' => 'array'
    'commentContent' => CommentContentCast::class
  ];

  protected $fillable = [
    'csdb_id',

    'modelIdentCode',
    'senderIdent',
    'commentType',
    'yearOfDataIssue',
    'seqNumber',

    "languageIsoCode",
    "countryIsoCode",

    'commentTitle',
    'year',
    'month',
    'day',

    'securityClassification',
    'commentPriority',
    'commentResponse',
    'commentRefs',
    'brexDmRef',
    'remarks',

    'commentContent',

    'json',
    'xml'
  ];

  public $timestamps = false;

  /**
   * harus json string
   * set value akan menjadi json string curly atau json string array []
   * get value akan menjadi array
   */
  protected function commentRefs(): Attribute
  {
    return Attribute::make(
      set: fn ($v) => is_array($v) ? json_encode($v) : ($v && Helper::isJsonString($v) ? $v : json_encode($v ? [$v] : [])
      ),
      get: fn ($v) => json_decode($v, true),
    );
  }

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  // protected function casts(): array
  // {
  //   return [
  //     'commentContent' => CommentContentCast::class
  //   ];
  // }

  public function create_xml(string $storagePath, array $params)
  {
    $this->CSDBObject = new CSDBObject('5.0');
    $this->CSDBObject->setPath(CSDB_STORAGE_PATH . "/" . $storagePath);
    $this->CSDBObject->setConfigXML(CSDB_VIEW_PATH . DIRECTORY_SEPARATOR . "xsl" . DIRECTORY_SEPARATOR . "Config.xml"); // nanti diubah mungkin berbeda antara pdf dan html meskupun harusnya SAMA. Nanti ConfigXML mungkin tidak diperlukan jika fitur BREX sudah siap sepenuhnya.
    $this->CSDBObject->createCOM($params);

    if ($this->CSDBObject->document) {
      return true;
    }
    return false;
  }

  public static function fillTable($csdb_id, CSDBObject $CSDBObject)
  {
    $filename = $CSDBObject->filename;
    $domXpath = new \DOMXpath($CSDBObject->document);

    $modelIdentCode = $domXpath->evaluate("string(//commentAddress/commentIdent/commentCode/@modelIdentCode)");
    $senderIdent = $domXpath->evaluate("string(//commentAddress/commentIdent/commentCode/@senderIdent)");
    $commentType = $domXpath->evaluate("string(//commentAddress/commentIdent/commentCode/@commentType)");
    $yearOfDataIssue = $domXpath->evaluate("string(//commentAddress/commentIdent/commentCode/@yearOfDataIssue)");
    $seqNumber = $domXpath->evaluate("string(//commentAddress/commentIdent/commentCode/@seqNumber)");

    $languageIsoCode = $domXpath->evaluate("string(//commentAddress/commentIdent/language/@languageIsoCode)");
    $countryIsoCode = $domXpath->evaluate("string(//commentAddress/commentIdent/language/@countryIsoCode)");
    $commentTitle = $domXpath->evaluate("string(//commentAddress/commentAddressItems/@commentTitle)");
    $year = $domXpath->evaluate("string(//commentAddress/commentAddressItems/issueDate/@year)");
    $month = $domXpath->evaluate("string(//commentAddress/commentAddressItems/issueDate/@month)");
    $day = $domXpath->evaluate("string(//commentAddress/commentAddressItems/issueDate/@day)");

    $securityClassification = $domXpath->evaluate("string(//commentStatus/security/@securityClassification)");
    $commentPriority = $domXpath->evaluate("string(//commentStatus/commentPriority/@commentPriorityCode)");
    $commentResponse = $domXpath->evaluate("string(//commentStatus/commentResponse/@responseType)");
    $commentRefs = $domXpath->evaluate("//commentStatus/commentRefs/*/*");
    if (!empty($commentRefs)) {
      $r = [];
      foreach ($commentRefs as $refsGroup) {
        $r[] = CSDBStatic::resolve_ident($refsGroup->firstElementChild);
      }
      $commentRefs = $r;
    } else {
      $commentRefs = [];
    }
    $brexElement = $domXpath->evaluate("//identAndStatusSection/descendant::brexDmRef/dmRef/dmRefIdent")[0];
    $brexDmRef = CSDBStatic::resolve_dmIdent($brexElement);
    $remarks = $CSDBObject->getRemarks($domXpath->evaluate("//identAndStatusSection/descendant::remarks")[0]);

    $commentContent = $domXpath->evaluate("//commentContent/*");
    if (!empty($commentContent)) {
      $r = '';
      foreach ($commentContent as $content) {
        switch ($content->tagName) {
          case 'simplePara':
            $r .= '\n' . $content->nodeValue;
            break;
          case 'attachmentRef':
            $r .= "\n Refer to attachment no. " . $filename . "-" . $content->getAttribute('attachmentNumber') . "." . strtolower($content->getAttribute('fileExtension')) . "\n";
            break;
        }
      }
      $commentContent = $r;
    } else {
      $commentContent = '';
    }
    $commentContent = trim($commentContent, '\n');

    $arr = [
      "csdb_id" => $csdb_id,

      'modelIdentCode' => $modelIdentCode,
      'senderIdent' => $senderIdent,
      'commentType' => $commentType,
      'yearOfDataIssue' => $yearOfDataIssue,
      'seqNumber' => $seqNumber,

      "languageIsoCode" => $languageIsoCode,
      "countryIsoCode" => $countryIsoCode,

      'commentTitle' => $commentTitle,
      'year' => $year,
      'month' => $month,
      'day' => $day,

      'securityClassification' => $securityClassification,
      'commentPriority' => $commentPriority,
      'commentResponse' => $commentResponse,
      'commentRefs' => $commentRefs,
      'brexDmRef' => $brexDmRef,
      'remarks' => $remarks,

      'commentContent' => $commentContent,

      'json' => CSDBStatic::xml_to_json($CSDBObject->document),
      'xml' => $CSDBObject->document->C14N() // ga bisa pakai saveXML karena menghasilkan doctype, sementara SQL XML belum tahu caranya render xml yang ada dtd
    ];

    $comment = Csdb::getObject($filename)->first() ?? Csdb::getModelClass('comment');
    $comment->timestamps = false;
    foreach ($arr as $prop => $v) {
      $comment->$prop = $v;
    }
    return $comment->save() ? $comment : false;
  }
}
