<?php

namespace App\Http\Controllers\CsdbApi;

use App\Http\Requests\Csdb\CsdbCreateByXMLEditor;
use App\Http\Requests\Csdb\CsdbDelete;
use App\Http\Requests\Csdb\CsdbPermanentDelete;
use App\Http\Requests\Csdb\CsdbRestore;
use App\Http\Requests\Csdb\CsdbUpdateByXMLEditor;
use App\Models\Csdb;
use App\Models\Csdb\History;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use PrettyXml\Formatter;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\Helper;

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
   * querykey? = 'form?xml/json (default xml)
   */
  public function read(Request $request, Csdb $CSDBModel)
  {
    $CSDBModel->CSDBObject->load(CSDB_STORAGE_PATH . "/" . $request->user()->storage . "/" . $CSDBModel->filename);
    if ($CSDBModel->CSDBObject->document) {
      switch ($request->form) {
        case 'json':
          $CSDBModel->makeHidden(['id']);
          $CSDBModel->object->makeHidden(['content', 'json']);
          return Response::make(
            [
              'csdb' => $CSDBModel,
              'json' => json_decode(CSDBStatic::xml_to_json($CSDBModel->CSDBObject->document)),
            ],
            200,
            ['Content-Type' => 'application/json']
          );
          break;
        default:
          $formatter = new Formatter();
          return Response::make(
            $formatter->format($CSDBModel->CSDBObject->document->saveXML()),
            200,
            ['Content-Type' => 'text/xml']
          );
          break;
      }
    }
    return abort(204);
  }

  // public function read_json(Request $request, Csdb $CSDBModel)
  // {
  //   $CSDBModel->CSDBObject->load(CSDB_STORAGE_PATH . "/" . $request->user()->storage . "/" . $CSDBModel->filename);
  //   if ($CSDBModel->CSDBObject->document) {
  //     return Response::make([
  //       'model' => $CSDBModel->makeHidden(['id']),
  //       'json' => json_decode(CSDBStatic::xml_to_json($CSDBModel->CSDBObject->document)),
  //     ],200,['Content-Type' => 'application/json']
  //     );
  //   }
  //   return abort(204);
  // }

  /**
   * response code 422 if fail
   * src: https://stackoverflow.com/questions/47269601/what-http-response-code-to-use-for-failed-post-request
   */
  public function update(CsdbUpdateByXMLEditor $request, Csdb $CSDBModel)
  {
    $CSDBModel = $request->validated('oldCSDBModel')[0];
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
  public function getCsdbs(Request $request)
  {
    if ($request->stt === 'act') {
      $CSDBModels = Csdb::getCsdbs(['exception' => ['CSDB-DELL', 'CSDB-PDEL']]);
    } elseif ($request->stt === 'dct') {
      $CSDBModels = Csdb::getCsdbs();
    } else {
      $CSDBModels = Csdb::where('storage_id', $request->user()->id);
    }
    if ($request->sc) {
      $keywords = array_merge(Helper::explodeSearchKeyAndValue($request->get('sc'), 'filename'));
      $query = Helper::generateWhereRawQueryString($keywords, $CSDBModels->getModel()->getTable(), ['path' => "#&value;"]);
      $CSDBModels = $CSDBModels->whereRaw($query[0], $query[1]);
    }

    if ($request->limit) {
      $CSDBModels = $CSDBModels->limit($request->limit);
    }

    return Response::make([
      "csdbs" => $CSDBModels->get(['filename', 'path'])->toArray(),
    ], 200, ["content-type" => 'application/json']);
  }

  /**
   * yang di CsdbController@forfolder_get_allobjects_list, support multiple path, eg: ?sc=path::csdb/amm,csdb/rfm.
   * disini, tdaik support multiple path agar proses lebih cepat
   * @belum di test di CrudTest::class
   * 
   * querykey? = 'sc?', 'stt?act/dct',
   */
  public function getCsdbsByPath(Request $request, string $path = 'csdb')
  {
    // menyiapkan csdb object
    $CSDBModels = Csdb::with(['initiator', 'lastHistory']);
    $keywords = array_merge(Helper::explodeSearchKeyAndValue($request->get('sc'), 'filename'), ["path" => [$path]]);
    $query = Helper::generateWhereRawQueryString($keywords, $CSDBModels->getModel()->getTable(), ['path' => "#&value;"]);

    // $hcode = $request->stt ? ($request->stt === 'all' ? [] : ($request->stt === 'act' ? ['CSDB-DELL', 'CSDB-PDEL'] : ($request->stt ===)))
    // switch ($request->stt) {
    //   case 'act': $hcode = ['exception' => ['CSDB-DELL', 'CSDB-PDEL']]; break;      
    //   case 'dct': $hcode = ['code' => ['CSDB-DELL', 'CSDB-PDEL']]; break;      
    //   default: $hcode = []; break;
    // }
    if (!empty($query)) $CSDBModels = $CSDBModels->whereRaw($query[0], $query[1]);

    if ($request->stt === 'act') {
      $queryCode = History::generateWhereRawQueryString_historyException(['CSDB-DELL', 'CSDB-PDEL'], Csdb::class, $CSDBModels->getModel()->getTable());
      $CSDBModels = $CSDBModels->whereRaw($queryCode[0], $queryCode[1]);
    } else if ($request->stt === 'dct') {
      $queryCode = History::generateWhereRawQueryString(['CSDB-DELL', 'CSDB-PDEL'], Csdb::class, $CSDBModels->getModel()->getTable());
      $CSDBModels = $CSDBModels->whereRaw($queryCode[0], $queryCode[1]);
    }

    $CSDBModels = $CSDBModels->where('storage_id', $request->user()->id)
      ->orderBy('filename')->paginate(100);
    $CSDBModels->setPath($request->getUri());

    // message
    $m = '';

    // menyiapkan folder
    $folders = new Csdb();
    // make query and get
    $query = Helper::generateWhereRawQueryString(['path' => [$path . "/"]], $folders->getTable());
    $folders = $folders->where('storage_id', $request->user()->id)
      ->whereRaw($query[0], $query[1]);

    if (isset($queryCode)) $folders = $folders->whereRaw($queryCode[0], $queryCode[1]);

    $folders = array_values(array_unique($folders->get(['path'])->toArray(), SORT_REGULAR));
    // menyiapkan path untuk replace, dimana subfolder akan dihilangkan disetiap hasil query
    $pathReplace = str_replace("/", "\/", $path);
    // menghilangkan sub-subfolder 
    $l_folders = count($folders);
    for ($i = 0; $i < $l_folders; $i++) {
      // pengecekan terhadap setiap keyword paths tidak diperlukan lagi karena saat pencarian setiap path keyword sudah ditambah '/' sehingga pencarian spesifik untuk sub folder 
      $folders[$i] = join("", $folders[$i]); // saat didapat dari database, bentuknya array berisi satu path saja
      $folders[$i] = preg_replace("/({$pathReplace})(\/[a-zA-Z0-9]+)(\/.+)?/", "$1$2", $folders[$i]); // menghilangkan subfolder. eg.: query path='csdb', result='csdb/cn235/amm'. Nah 'amm' nya dihilangkan
    }
    $folders = array_values(array_filter(array_unique($folders, SORT_STRING), fn ($v) => ($v != null) || ($v != ''))); // array_values agar tidak assoc atau supaya indexnya teratur
    sort($folders);

    // return
    return Response::make([
      "infotype" => "note",
      "message" => $m,
      "pagination" => $CSDBModels,
      "path" => $path,
      'paths' => $folders ?? [],
    ]);
  }

  /**
   * UNTUK PDF, nanti pake status code 206 partial content, ref MDN, https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/206
   */
}
