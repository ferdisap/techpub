<?php

namespace App\Http\Controllers\CsdbApi;

use App\Http\Requests\Csdb\CsdbCreateByXMLEditor;
use App\Http\Requests\Csdb\CsdbDelete;
use App\Http\Requests\Csdb\CsdbImportFromDDN;
use App\Http\Requests\Csdb\CsdbPermanentDelete;
use App\Http\Requests\Csdb\CsdbRestore;
use App\Http\Requests\Csdb\CsdbUpdateByXMLEditor;
use App\Http\Requests\Csdb\UploadICN;
use App\Http\Resources\HistoryResource;
use App\Jobs\Csdb\FillObjectTable;
use App\Models\Csdb;
use App\Models\Csdb\Comment;
use App\Models\Csdb\History;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use PrettyXml\Formatter;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\Helper;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\Csdb\Ddn;
use App\Models\User;
use Ptdi\Mpub\Main\ICNDocument;

class MainController extends BaseController
{
  /**
   * masih belum tau cara baca request parameter jika clientnya pakai form data, karena selau null, atau error boundary client
   * response code 422 if fail
   * src: https://stackoverflow.com/questions/47269601/what-http-response-code-to-use-for-failed-post-request
   */
  public function create(CsdbCreateByXMLEditor $request)
  {
    $CSDBModel = new Csdb();
    $CSDBModel->CSDBObject = $request->validated('xmleditor')[0];
    $CSDBModel->filename = $CSDBModel->CSDBObject->getFilename();
    $CSDBModel->path = $request->validated()['path'];
    $CSDBModel->storage_id = $request->user()->id;
    $CSDBModel->initiator_id = $request->user()->id;
    if ($CSDBModel->saveDOMandModel(
      $request->user()->storage,
      [
        ['MAKE_CSDB_CRBT_History', [Csdb::class]],
        ['MAKE_USER_CRBT_History', [$request->user(), '', $CSDBModel->filename]]
      ]
    )) {
      return Response::make([
        'infotype' => 'note',
        'message' => "New {$CSDBModel->filename} has been created.",
        "csdb" => $CSDBModel,
      ], 200, ['content-type' => 'application/json']);
    }
    return Response::make([
      'infotype' => 'warning',
      'message' => "{$CSDBModel->filename} failed to create.",
      'errors' => $CSDBModel->CSDBObject->errors->get(),
      'csdb' => $CSDBModel
    ], 422, ['content-type' => 'application/json']);
  }

  /**
   * import csdb akan meng copy CSDBObject/xml nya juga
   */
  public function import(CsdbImportFromDDN $request, Csdb $CSDBModel)
  {
    // check duplicated file
    if (!($request->overwrite) && !empty($request->duplicatedCSDBModels)) {
      $filenames = $request->duplicatedCSDBModels;
      array_walk($filenames, fn (&$v) => $v = $v['filename']);
      return Response::make([
        "infotype" => "warning",
        "message" => "There is none of csdb imported. Some csdb's is prevented to overwrite.",
        "errors" => [
          'failure' => $filenames,
        ]
      ], 499, ['content-type' => "application/json"]);
    } else {
      $success = [];
      $fail = [];
      $storage = $CSDBModel->owner->storage;
      foreach ($request->validated('filenames') as $filename) {
        $CSDBModel = Csdb::getCsdb($filename, $request->user()->id)->first() ?? new Csdb();
        $CSDBModel->CSDBObject->load(CSDB_STORAGE_PATH . DIRECTORY_SEPARATOR . $storage . DIRECTORY_SEPARATOR . $filename);
        $CSDBModel->filename = $filename;
        $CSDBModel->path = $request->validated('path');
        $CSDBModel->storage_id = $request->user()->id;
        $CSDBModel->initiator_id = $request->user()->id;

        if ($CSDBModel->saveDOMandModel($request->user()->storage, [
          ['MAKE_CSDB_IMPT_History', [Csdb::class]],
          ['MAKE_USER_IMPT_History', [$request->user(), '', $CSDBModel->filename]]
        ])) {
          $success[] = $CSDBModel->filename;
        } else $fail[] = $CSDBModel->filename;
      }
      $totalFail = count($fail);
      $totalSuccess = count($success);
      $infotype = $totalSuccess < 1 ? "warning" : ($totalFail > 0 ? 'caution' : 'note');
      $code = $totalSuccess && !$totalFail ? 200 : (!$totalSuccess ? 422 : 299);
      $message = "Success to import " . join(", ", $success) . (!empty($fail) ? " and fail to import " . join(", ", $fail) : '.');

      $responseContent = [
        'infotype' => $infotype,
        'message' => $message,
        'data' => [
          'success' => $success
        ]
      ];

      if ($code != 200) $responseContent['errors'] = [
        'failure' => $fail,
      ];
      return Response::make($responseContent, $code, ['content-type' => 'application/json']);
    }
  }

  /**
   * query? isUpdate?string
   * ini bisa update dan create
   * @return Response JSON contain SQL object model with initiator data
   */
  public function uploadICN(UploadICN $request)
  {
    // #1 validation input form
    $validatedData = $request->validated();
    $file = $validatedData['entity'];
    $CSDBModel = $validatedData['oldCSDBModel'];
    $CSDBModel->CSDBObject->load($file->path());
    $CSDBModel->filename = $validatedData['filename'];
    $CSDBModel->path = $validatedData['path'];
    $CSDBModel->initiator_id = $request->user()->id;
    $CSDBModel->storage_id = $request->user()->id;
    if ($CSDBModel->saveDOMandModel($request->user()->storage, [
      [$request->isUpdate ? 'MAKE_CSDB_UPDT_History' : 'MAKE_CSDB_CRBT_History', [Csdb::class]],
      [$request->isUpdate ? 'MAKE_USER_UPDT_History' : 'MAKE_USER_CRBT_History', [$request->user(), '', $CSDBModel->filename]],
    ])) {
      $CSDBModel->initiator; // agar ada initiator nya

      return Response::make([
        'infotype' => 'note',
        'message' => $request->isUpdate ? "{$CSDBModel->filename} has been updated." : "New {$CSDBModel->filename} has been uploaded.",
        "csdb" => $CSDBModel,
      ], 200, ['content-type' => 'application/json']);
    } else {
      return Response::make([
        'infotype' => 'warning',
        'message' => "{$CSDBModel->filename} failed to" . $request->isUpdate ? 'update' : 'upload' . ".",
        'errors' => $CSDBModel->CSDBObject->errors->get(),
        'csdb' => $CSDBModel
      ], 422, ['content-type' => 'application/json']);
    }
  }

  /**
   * querykey? = 'form?xml/json (default xml)
   */
  public function read(Request $request, Csdb $CSDBModel)
  {
    if ($CSDBModel->lastHistory->code === 'CSDB-DELL' || $CSDBModel->lastHistory->code === 'CSDB-PDEL') {
      throw new HttpResponseException(response(["message" => $CSDBModel->filename . " has been deleted."], 404));
    }
    $storage = $CSDBModel->owner->storage;
    $CSDBModel->CSDBObject->load(CSDB_STORAGE_PATH . "/" . $storage . "/" . $CSDBModel->filename);
    if ($CSDBModel->CSDBObject->document) {
      switch ($request->form) {
        case 'json':
          $CSDBModel->object;
          if (isset($CSDBModel->object)) $CSDBModel->object->makeHidden(['content', 'json']); // karena pakai supervisor untuk membuat object jadi belum tentu
          else {
            $CSDBModel->setRelations([]); // di set relationnya menjadi kosong karena sebelumnya ada $CSDBModel->object;. Relation 'object' akan gagal karena akan membaca slef::class sehingga akan mencari where 'csdb'.'csdb_id' = ... padahal bukan 'csdb_id' tapi 'id'
            FillObjectTable::dispatchSync($request->user(), $CSDBModel, false);
            // $fill = new FillObjectTable($request->user(), $CSDBModel, false); // ini bisa
            // $fill->handle(); // ini bisa
            $CSDBModel->object;
            $CSDBModel->object->makeHidden(['content', 'json']);
          }
          $CSDBModel->owner->makeHidden(['storage']);
          return Response::make(
            [
              'csdb' => $CSDBModel->makeHidden(['id']),
              'json' => json_decode(CSDBStatic::xml_to_json($CSDBModel->CSDBObject->document)),
            ],
            200,
            ['Content-Type' => 'application/json']
          );
          break;
        case 'xml':
          $formatter = new Formatter();
          return Response::make(
            $formatter->format($CSDBModel->CSDBObject->document->saveXML()),
            200,
            ['Content-Type' => 'text/xml']
          );
          break;
        case 'pdf':
          $modelIdentCode = 'CN235';
          $config = new \DOMDocument();
          $config->load(\Ptdi\Mpub\Transformer\Transformator::config_uri());
          $xpath = new \DOMXPath($config);
          $xslFo = $xpath->evaluate("string(//config/output/method[@type='pdf']/path[@product-name='$modelIdentCode'])");
          if (!$xslFo) $xslFo = $xpath->evaluate("string(//config/output/method[@type='pdf']/path[@product-name='*'])");

          $output = CSDB_VIEW_PATH . '/transformed' . '/' . str_replace('.xml', '.fo', $CSDBModel->filename);
          $CSDBModel->loadCSDBObject();
          $fo = $CSDBModel->CSDBObject->transform_to_fo($xslFo, $output);
          if (!$fo) abort(204);
          $pdf = $CSDBModel->CSDBObject->transform_to_pdf($fo, str_replace('.fo', '.pdf', $fo));
          if (!$pdf) abort(204);

          return Response::make(
            file_get_contents($pdf),
            200,
            ['Content-Type' => 'application/pdf']
          );
        default:
          $isICN = $CSDBModel->CSDBObject->document instanceof ICNDocument;
          return Response::make(
            $isICN ? $CSDBModel->CSDBObject->document->getFile() : $CSDBModel->CSDBObject->document->saveXML(),
            200,
            ['Content-Type' => $isICN ? $CSDBModel->CSDBObject->document->getFileinfo()['mime_type'] : 'text/xml']
          );
      }
    }
    return abort(204);
  }

  public function ident(Request $request, Csdb $CSDBModel)
  {
    // if($request->route('CSDBModel')->lastHistory->code === 'CSDB-DELL' || $request->route('CSDBModel')->lastHistory->code === 'CSDB-PDEL'){
    //   throw new HttpResponseException(response(["message" => $request->route('CSDBModel')->filename . " has been deleted."],404));
    // }
    if ($CSDBModel->loadCSDBObject() && isset($CSDBModel->CSDBObject->document->doctype)) {
      $CSDBModel->object;
      if (!$CSDBModel->object) {
        $CSDBModel->setRelations([]); // di set relationnya menjadi kosong karena sebelumnya ada $CSDBModel->object;. Relation 'object' akan gagal karena akan membaca slef::class sehingga akan mencari where 'csdb'.'csdb_id' = ... padahal bukan 'csdb_id' tapi 'id'
        FillObjectTable::dispatchSync($request->user(), $CSDBModel, false);
        $CSDBModel->object;
      }
      switch ($CSDBModel->CSDBObject->document->doctype->nodeName) {
        case 'dmodule':
          $data = [
            'modelIdentCode' => $CSDBModel->object->modelIdentCode,
            'systemDiffCode' => $CSDBModel->object->systemDiffCode,
            'systemCode' => $CSDBModel->object->systemCode,
            'subSystemCode' => $CSDBModel->object->subSystemCode,
            'subSubSystemcode' => $CSDBModel->object->subSubSystemcode,
            'assyCode' => $CSDBModel->object->assyCode,
            'disassyCode' => $CSDBModel->object->disassyCode,
            'disassyCodeVariant' => $CSDBModel->object->disassyCodeVariant,
            'infoCode' => $CSDBModel->object->infoCode,
            'infoCodeVariant' => $CSDBModel->object->infoCodeVariant,
            'itemLocationCode' => $CSDBModel->object->itemLocationCode,
            'languageIsoCode' => $CSDBModel->object->languageIsoCode,
            'countryIsoCode' => $CSDBModel->object->countryIsoCode,
            'issueNumber' => $CSDBModel->object->issueNumber,
            'inWork' => $CSDBModel->object->inWork,
          ];
          break;
        case 'pm':
          $data = [
            'modelIdentCode' => $CSDBModel->object->modelIdentCode,
            'pmIssuer' => $CSDBModel->object->pmIssuer,
            'pmNumber' => $CSDBModel->object->pmNumber,
            'pmVolume' => $CSDBModel->object->pmVolume,
            'languageIsoCode' => $CSDBModel->object->languageIsoCode,
            'countryIsoCode' => $CSDBModel->object->countryIsoCode,
            'issueNumber' => $CSDBModel->object->issueNumber,
            'inWork' => $CSDBModel->object->inWork,
          ];
          break;
        case 'dml':
          $data = [
            'modelIdentCode' => $CSDBModel->object->modelIdentCode,
            'senderIdent' => $CSDBModel->object->senderIdent,
            'dmlType' => $CSDBModel->object->dmlType,
            'yearOfDataIssue' => $CSDBModel->object->yearOfDataIssue,
            'seqNumber' => $CSDBModel->object->seqNumber,
          ];
          break;
        case 'ddn':
          $data = [
            'modelIdentCode' => $CSDBModel->object->modelIdentCode,
            'senderIdent' => $CSDBModel->object->senderIdent,
            'receiverIdent' => $CSDBModel->object->receiverIdent,
            'yearOfDataIssue' => $CSDBModel->object->yearOfDataIssue,
            'seqNumber' => $CSDBModel->object->seqNumber,
          ];
          break;
        case 'comment':
          $data = [
            'modelIdentCode' => $CSDBModel->object->modelIdentCode,
            'senderIdent' => $CSDBModel->object->senderIdent,
            'commentType' => $CSDBModel->object->commentType,
            'yearOfDataIssue' => $CSDBModel->object->yearOfDataIssue,
            'seqNumber' => $CSDBModel->object->seqNumber,
          ];
          break;
        case 'icnmetadata':
          // TBD, karena belum siap crud, serta menghubungannya ke ICN
          $data = [];
        default:
          // TBD
          // lakukan untuk ICN, ambil metadata, kalau tidak ada return kosongin aja object nya
          $data = [];
          break;
      }
      $CSDBModel->ident = $data;
      $CSDBModel->owner->setVisible(['email']);
      return Response::make([
        "csdb" => $CSDBModel->makeHidden(["object"]), // hanya filename, path, ident, owner.email
      ], 200, ['content-type' => 'application/json']);
    }
    // jika ICN masih TBD
    return Response::make('', 204);
  }

  public function status(Request $request, Csdb $CSDBModel)
  {
    if ($CSDBModel->loadCSDBObject() && isset($CSDBModel->CSDBObject->document->doctype)) {
      $CSDBModel->object;
      if (!$CSDBModel->object) {
        $CSDBModel->setRelations([]); // di set relationnya menjadi kosong karena sebelumnya ada $CSDBModel->object;. Relation 'object' akan gagal karena akan membaca slef::class sehingga akan mencari where 'csdb'.'csdb_id' = ... padahal bukan 'csdb_id' tapi 'id'
        FillObjectTable::dispatchSync($request->user(), $CSDBModel, false);
        $CSDBModel->object;
      }
      switch ($CSDBModel->CSDBObject->document->doctype->nodeName) {
        case 'dmodule':
          $data = [
            'securityClassification' => $CSDBModel->object->securityClassification,
            'responsiblePartnerCompany' => $CSDBModel->object->responsiblePartnerCompany,
            'originator' => $CSDBModel->object->originator,
            'applicability' => $CSDBModel->object->applicability,
            'brexDmRef' => $CSDBModel->object->brexDmRef,
            'qa' => $CSDBModel->object->qa,
            'remarks' => $CSDBModel->object->remarks,
          ];
          break;
        case 'pm':
          $data = [
            'securityClassification' => $CSDBModel->object->securityClassification,
            'responsiblePartnerCompany' => $CSDBModel->object->responsiblePartnerCompany,
            'originator' => $CSDBModel->object->originator,
            'applicability' => $CSDBModel->object->applicability,
            'brexDmRef' => $CSDBModel->object->brexDmRef,
            'qa' => $CSDBModel->object->qa,
            'remarks' => $CSDBModel->object->remarks,
          ];
          break;
        case 'dml':
          $data = [
            'securityClassification' => $CSDBModel->object->securityClassification,
            'brexDmRef' => $CSDBModel->object->brexDmRef,
            'dmlRef' => $CSDBModel->object->dmlRef,
            'remarks' => $CSDBModel->object->remarks,
          ];
          break;
        case 'ddn':
          $data = [
            'securityClassification' => $CSDBModel->object->securityClassification,
            'brexDmRef' => $CSDBModel->object->brexDmRef,
            'authorization' => $CSDBModel->object->authorization,
            'remarks' => $CSDBModel->object->remarks,
          ];
          break;
        case 'comment':
          $data = [
            'securityClassification' => $CSDBModel->object->securityClassification,
            'commentPriority' => $CSDBModel->object->commentPriority,
            'commentResponse' => $CSDBModel->object->commentResponse,
            'commentRefs' => join(", ", $CSDBModel->object->commentRefs),
            // $table->json('commentRefs'); // jika kosong harus di isi dengan Array
            'brexDmRef' => $CSDBModel->object->brexDmRef,
            'remarks' => $CSDBModel->object->remarks,
          ];
          break;
        case 'icnmetadata':
          // TBD, karena belum siap crud, serta menghubungannya ke ICN
          $data = [];
        default:
          // TBD
          // lakukan untuk ICN, ambil metadata, kalau tidak ada return kosongin aja object nya
          $data = [];
          break;
      }
      $CSDBModel->status = $data;
      return Response::make([
        "csdb" => $CSDBModel,
      ], 200, ['content-type' => 'application/json']);
    }
    // jika ICN masih TBD
    return Response::make('', 204);
  }

  /**
   * querykey? = 'pg?0/number', 'pgtype?simple/cursor'
   */
  public function histories(Request $request, Csdb $CSDBModel)
  {
    if ($request->pg) {
      $CSDBModel->attachHistories($request->pg, $request->pgtype);
    } else {
      $CSDBModel->attachHistories(0, 'all')->histories->makeVisible(['id'])->makeHidden(['description']);
      $CSDBModel->histories = $CSDBModel->histories->map(fn ($v) => new HistoryResource($v));
    }
    return Response::make([
      'csdb' => $CSDBModel,
    ], 200, ['content-type' => 'application/json']);
  }

  public function comments(Request $request, Csdb $CSDBModel)
  {
    $CSDBModel->comments = Csdb::getObjects(Comment::class, ['exception' => ['CSDB-DELL', 'CSDB-PDEL']])
      ->where('commentRefs', 'like', "%{$CSDBModel->filename}%")->with('csdb.lastHistory')->get();
    $CSDBModel->comments->map(function ($com) {
      $com->csdb->initiator->makeHidden(['first_name', 'middle_name', 'last_name', 'job_title', 'storage', 'address', 'work_enterprise']);
      $com->csdb->lastHistory->makeHidden(['code', 'description']);
    });
    return Response::make([
      'csdb' => $CSDBModel,
    ], 200, ['content-type' => 'application/json']);
  }

  /**
   * response code 422 if fail
   * src: https://stackoverflow.com/questions/47269601/what-http-response-code-to-use-for-failed-post-request
   */
  public function update(CsdbUpdateByXMLEditor $request, Csdb $CSDBModel)
  {
    // $CSDBModel = $request->validated('oldCSDBModel')[0];
    $CSDBModel->CSDBObject = $request->validated('xmleditor')[0];
    $CSDBModel->path = $request->validated('path');
    if ($CSDBModel->saveDOMandModel($request->user()->storage, [
      History::MAKE_CSDB_UPDT_History($CSDBModel),
      History::MAKE_USER_UPDT_History($request->user(), '', $CSDBModel->filename)
    ])) {
      return Response::make([
        'infotype' => 'note',
        'message' => "{$CSDBModel->filename} has been update.",
        "csdb" => $CSDBModel,
      ], 200, ['content-type' => 'application/json']);
    }
    return Response::make([
      'infotype' => 'warning',
      'message' => "{$CSDBModel->filename} failed to update.",
      'errors' => $CSDBModel->CSDBObject->errors->get(),
      'csdb' => $CSDBModel
    ], 422, ['content-type' => 'application/json']);
  }

  public function delete(CsdbDelete $request)
  {
    $result = [
      'success' => [],
      'fail' => [],
    ];
    $validatedData = $request->validated();
    $CSDBModels = $validatedData['CSDBModelArray'];
    unset($validatedData['CSDBModelArray']);
    $qtyCSDBs = count($CSDBModels);
    for ($i = 0; $i < $qtyCSDBs; $i++) {
      $CSDB_HISTORYModel = History::MAKE_CSDB_DELL_History($CSDBModels[$i]);
      $USER_HISTORYModel = History::MAKE_USER_DELL_History($request->user(), '', $CSDBModels[$i]->filename);
      if (History::saveModel([$CSDB_HISTORYModel, $USER_HISTORYModel])) {
        $CSDBModels[$i]->save();
        $result['success'][] = $CSDBModels[$i]->filename;
      } else {
        $result['fail'][] = $CSDBModels[$i]->filename;
      }
    }
    $totalFail = count($result['fail']);
    $totalSuccess = count($result['success']);
    $infotype = $totalSuccess < 1 ? "warning" : ($totalFail > 0 ? 'caution' : 'note');
    $m = ($totalSuccess > 0 ? ("success: " . $totalSuccess . "/" . ($totalSuccess + $totalFail) . ", failure: " . $totalFail . "/" . ($totalSuccess + $totalFail)) : "fail: " . ($totalSuccess + $totalFail) . "/" . ($totalSuccess + $totalFail));
    // jika ada yang fail dan ada yang success, maka 299, jika totally fail 400, jika totally success 200
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
    return Response::make($responseContent, $code);
  }

  /**
   * sama dengan @delete() tapi beda history saja
   */
  public function permanentDelete(CsdbPermanentDelete $request)
  {
    $result = [
      'success' => [],
      'fail' => [],
    ];
    $validatedData = $request->validated();
    $CSDBModels = $validatedData['CSDBModelArray'];
    unset($validatedData['CSDBModelArray']);
    $qtyCSDBs = count($CSDBModels);
    for ($i = 0; $i < $qtyCSDBs; $i++) {
      $CSDB_HISTORYModel = History::MAKE_CSDB_PDEL_History($CSDBModels[$i]);
      $USER_HISTORYModel = History::MAKE_USER_PDEL_History($request->user(), '', $CSDBModels[$i]->filename);
      if (History::saveModel([$CSDB_HISTORYModel, $USER_HISTORYModel])) {
        $CSDBModels[$i]->save();
        $result['success'][] = $CSDBModels[$i]->filename;
      } else {
        $result['fail'][] = $CSDBModels[$i]->filename;
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
    return Response::make($responseContent, $code);
  }

  /**
   * sama dengan @delete() tapi beda history saja
   */
  public function restore(CsdbRestore $request)
  {
    $result = [
      'success' => [],
      'fail' => [],
    ];
    $validatedData = $request->validated();
    $CSDBModels = $validatedData['CSDBModelArray'];
    unset($validatedData['CSDBModelArray']);
    $qtyCSDBs = count($CSDBModels);
    for ($i = 0; $i < $qtyCSDBs; $i++) {
      $CSDB_HISTORYModel = History::MAKE_CSDB_RSTR_History($CSDBModels[$i]);
      $USER_HISTORYModel = History::MAKE_USER_RSTR_History($request->user(), '', $CSDBModels[$i]->filename);
      if (History::saveModel([$CSDB_HISTORYModel, $USER_HISTORYModel])) {
        $CSDBModels[$i]->save();
        $result['success'][] = $CSDBModels[$i]->filename;
      } else {
        $result['fail'][] = $CSDBModels[$i]->filename;
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
    return Response::make($responseContent, $code);
  }

  /**
   * querykey? = 'sc?', 'stt?act/dct', limit?integer
   */
  public function all(Request $request)
  {
    if ($request->stt === 'act') {
      $CSDBModels = Csdb::getCsdbs(['exception' => ['CSDB-DELL', 'CSDB-PDEL']], $request->user()->id);
    } elseif ($request->stt === 'dct') {
      $CSDBModels = Csdb::getCsdbs([], $request->user()->id);
    } else {
      $CSDBModels = Csdb::where('storage_id', $request->user()->id);
    }

    $sc = $request->sc;
    if ($sc) {
      $keywords = array_merge(Helper::explodeSearchKeyAndValue($sc, 'filename'));
      $query = Helper::generateWhereRawQueryString($keywords, $CSDBModels->getModel()->getTable(), ['path' => "#&value;"]);
      // $CSDBModels = $CSDBModels->whereRaw($query[0], $query[1]);
      $CSDBModels->whereRaw($query[0], $query[1]);
    }

    if ($request->limit) {
      // $CSDBModels = $CSDBModels->limit($request->limit);
      $CSDBModels->limit($request->limit);
    }

    // $CSDBModels->with(['owner' => fn (BelongsTo $query) => $query->without(['work_enterprise'])->toBase()->select(['id', 'storage'])]);
    $CSDBModels->with(['accessKey']);

    return Response::make([
      // "csdbs" => $CSDBModels->get(['id','storage_id','filename', 'path'])->toArray(),
      // "csdbs" => $CSDBModels->get(['id', 'storage_id', 'filename', 'path'])->map(fn ($csdb) => [$csdb->owner->storage, $csdb->path, $csdb->filename]),
      "csdbs" => $CSDBModels->get(['id', 'storage_id', 'filename', 'path'])->map(fn ($csdb) => "s1000d:{$csdb->path}/{$csdb->filename}?access_key={$csdb->accessKey->key}"),
    ], 200, ["content-type" => 'application/json']);
  }

  /**
   * untuk mendapatkan list DDN yang di dispatch ke request->user dari user lain
   * outputnya sama seperti getCsdbs
   * 
   * querykey? = 'sc?', limit?integer
   */
  public function dispatched(Request $request)
  {
    $sc = $request->sc;
    $keywords = Helper::explodeSearchKeyAndValue($sc, 'filename');

    $DDNModels = Ddn::with(['csdb' => function (Builder $query) use ($sc, $keywords) {
      $query->select(['id', 'filename', 'path', 'storage_id']);
      $query->with(['accessKey']);
      if ($sc) {
        $q = Helper::generateWhereRawQueryString($keywords, 'csdb', ['path' => "#&value;"]);
        $query->whereRaw($q[0], $q[1]);
      }
    }])
      ->whereNot('dispatchFrom_id', $request->user()->id)
      ->where('dispatchTo_id', $request->user()->id);

    if ($sc) {
      $query = Helper::generateWhereRawQueryString($keywords, $DDNModels->getModel()->getTable());
      $DDNModels = $DDNModels->whereRaw($query[0], $query[1]);
    }

    if ($request->limit) {
      $query->limit($request->limit);
    }

    $DDNModels = $DDNModels->get(['id', 'csdb_id', 'dispatchFrom_id', 'dispatchTo_id']);
    return Response::make([
      "csdbs" => $DDNModels->map(fn ($v) => $v->csdb = 's1000d:' . 'DISPATCHED/' . $v->csdb->path . "/" . $v->csdb->filename . "?access_key=" . $v->csdb->accessKey->key),
    ], 200, ['content-type' => 'application/json']);
  }

  /**
   * yang di CsdbController@forfolder_get_allobjects_list, support multiple path, eg: ?sc=path::csdb/amm,csdb/rfm.
   * disini, tdaik support multiple path agar proses lebih cepat
   * @belum di test di CrudTest::class
   * 
   * querykey? = 'sc?', 'stt?act/dct', 
   * dispatchTo?email
   * 
   * untuk data owner, hanya csdb.owner.storage
   * 
   */
  public function getCsdbsByPath(Request $request, string $path = 'csdb')
  {
    $isDispatch = false;
    // if path start with 'DISPATCHED', then it will look DDN list only where dispatched to client/request-user
    if (str_starts_with($path, 'DISPATCHED')) {
      $isDispatch = true;
      $path = preg_replace("/DISPATCHED\/?/", "", $path);
      $CSDBModels = new Csdb();
      $CSDBModels->objectClass = Ddn::class;
      $CSDBModels = $CSDBModels->where("filename", "like", "DDN-%"); // sengaja $CSDBModels di assign supaya menjadi class Builder dan $objectClass terinstance 
      $CSDBModels->with(['lastHistory', 'accessKey']);
      $userId = $request->user()->id;
      $CSDBModels = $CSDBModels->whereHas(
        'object',
        function (Builder $DDNModel) use ($userId) {
          $DDNModel->select(['id', 'csdb_id', 'dispatchFrom_id', 'dispatchTo_id'])->where('dispatchTo_id', $userId)->whereNot('dispatchFrom_id', $userId);
        }
      );
      // sc
      $keywords = array_merge(Helper::explodeSearchKeyAndValue($request->sc, 'filename'), ["path" => [$path]]);
      $query = Helper::generateWhereRawQueryString($keywords, $CSDBModels->getModel()->getTable(), ['path' => "#&value;"]);
      if (!empty($query)) $CSDBModels = $CSDBModels->whereRaw($query[0], $query[1]);
      // stt
      if ($request->stt === 'act') {
        $queryCodeHistory = History::generateWhereRawQueryString_historyException(['CSDB-DELL', 'CSDB-PDEL'], Csdb::class, $CSDBModels->getModel()->getTable());
        $CSDBModels = $CSDBModels->whereRaw($queryCodeHistory[0], $queryCodeHistory[1]);
      } else if ($request->stt === 'dct') {
        $queryCodeHistory = History::generateWhereRawQueryString(['CSDB-DELL', 'CSDB-PDEL'], Csdb::class, $CSDBModels->getModel()->getTable());
        $CSDBModels = $CSDBModels->whereRaw($queryCodeHistory[0], $queryCodeHistory[1]);
      }
      $CSDBModels->select(['id', 'filename', 'path', 'storage_id']);
      // get
      $CSDBModels = $CSDBModels->orderBy('filename')->paginate(perPage: 100, columns: ['id', 'filename', 'path', 'storage_id']);
      $CSDBModels->setPath($request->getUri());
    } else {
      // menyiapkan csdb object, bisa pakai $query->setEagerLoads([]) atau $query->without(['work_enterprise'])
      $CSDBModels = Csdb::with(['lastHistory', 'accessKey']);
      // sc
      $keywords = array_merge(Helper::explodeSearchKeyAndValue($request->sc, 'filename'), ["path" => [$path]]);
      $query = Helper::generateWhereRawQueryString($keywords, $CSDBModels->getModel()->getTable(), ['path' => "#&value;"]);
      if (!empty($query)) $CSDBModels = $CSDBModels->whereRaw($query[0], $query[1]);
      // stt
      if ($request->stt === 'act') {
        $queryCodeHistory = History::generateWhereRawQueryString_historyException(['CSDB-DELL', 'CSDB-PDEL'], Csdb::class, $CSDBModels->getModel()->getTable());
        $CSDBModels = $CSDBModels->whereRaw($queryCodeHistory[0], $queryCodeHistory[1]);
      } else if ($request->stt === 'dct') {
        $queryCodeHistory = History::generateWhereRawQueryString(['CSDB-DELL', 'CSDB-PDEL'], Csdb::class, $CSDBModels->getModel()->getTable());
        $CSDBModels = $CSDBModels->whereRaw($queryCodeHistory[0], $queryCodeHistory[1]);
      }
      $CSDBModels->select(['id', 'filename', 'path', 'storage_id']);
      // get
      $CSDBModels = $CSDBModels->where('storage_id', $request->user()->id)->orderBy('filename')->paginate(100);
      $CSDBModels->setPath($request->getUri());
    }

    // menyiapkan folder
    $folders = new Csdb();
    if ($isDispatch) {
      $userId = $request->user()->id;
      $folders->objectClass = Ddn::class;
      $folders = $folders->whereHas(
        'object',
        function (Builder $DDNModel) use ($userId) {
          $DDNModel->select(['id', 'csdb_id', 'dispatchFrom_id', 'dispatchTo_id'])->where('dispatchTo_id', $userId)->whereNot('dispatchFrom_id', $userId);
        }
      );
    } else {
      $folders = $folders->where('storage_id', $request->user()->id);
    }
    // make query and get
    $query = Helper::generateWhereRawQueryString(['path' => [$path . "/"]], $folders->getModel()->getTable());
    $folders = $folders->whereRaw($query[0], $query[1]);

    if (isset($queryCodeHistory)) $folders = $folders->whereRaw($queryCodeHistory[0], $queryCodeHistory[1]);

    $folders->select(['path']); // ini ditulis agar SQL tidak query seluruh column yang akan memberatkan. Sepertinya ini tidak perlu ditulis karena sudah ada get['path'], tapi tidak bisa lihat di toSql() nya
    $folders = array_values(array_unique($folders->get(['path'])->toArray(), SORT_REGULAR));
    // menyiapkan path untuk replace, dimana subfolder akan dihilangkan disetiap hasil query
    $pathReplace = str_replace("/", "\/", $path);
    // menghilangkan sub-subfolder 
    $l_folders = count($folders);
    for ($i = 0; $i < $l_folders; $i++) {
      // pengecekan terhadap setiap keyword paths tidak diperlukan lagi karena saat pencarian setiap path keyword sudah ditambah '/' sehingga pencarian spesifik untuk sub folder 
      $folders[$i] = join("", $folders[$i]); // saat didapat dari database, bentuknya array berisi satu path saja
      $folders[$i] = ($isDispatch ? 'DISPATCHED/' : '') . preg_replace("/({$pathReplace})(\/[a-zA-Z0-9]+)(\/.+)?/", "$1$2", $folders[$i]); // menghilangkan subfolder. eg.: query path='csdb', result='csdb/cn235/amm'. Nah 'amm' nya dihilangkan
    }
    $folders = array_values(array_filter(array_unique($folders, SORT_STRING), fn ($v) => ($v != null) || ($v != ''))); // array_values agar tidak assoc atau supaya indexnya teratur
    sort($folders);

    // return
    return Response::make([
      // "infotype" => "note",
      // "message" => '',
      "pagination" => $CSDBModels,
      "path" => $path,
      'paths' => $folders ?? [],
    ]);
  }

  /**
   * UNTUK PDF, nanti pake status code 206 partial content, ref MDN, https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/206
   */
}
