<?php

namespace App\Http\Controllers\Csdb;

use App\Http\Controllers\Controller;
use App\Http\Requests\Csdb\CsdbChangePath;
use App\Http\Requests\Csdb\CsdbCreateByXMLEditor;
use App\Http\Requests\Csdb\CsdbDelete;
use App\Http\Requests\Csdb\CsdbPermanentDelete;
use App\Http\Requests\Csdb\CsdbRestore;
use App\Http\Requests\Csdb\CsdbUpdateByXMLEditor;
use App\Http\Requests\Csdb\Download;
use App\Http\Requests\Csdb\UploadICN;
use App\Models\Csdb;
use App\Models\Csdb\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use PrettyXml\Formatter;
use Ptdi\Mpub\Fop\Fop;
use Ptdi\Mpub\Main\CSDBError;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\Helper;
use ZipStream\ZipStream;

class CsdbController extends Controller
{
  public function app()
  {
    return view('csdb.app');
  }

  public function create(CsdbCreateByXMLEditor $request)
  {
    $CSDBModel = new Csdb();
    $CSDBModel->CSDBObject = $request->validated()['xmleditor'][0];
    $CSDBModel->filename = $CSDBModel->CSDBObject->getFilename();
    $CSDBModel->path = $request->validated()['path'];
    $CSDBModel->storage_id = $request->user()->id;
    $CSDBModel->initiator_id = $request->user()->id;    
    if ($CSDBModel->saveDOMandModel($request->user()->storage,
        [ 
          ['MAKE_CSDB_CRBT_History',[Csdb::class]], 
          ['MAKE_USER_CRBT_History', [$request->user(), '', $CSDBModel->filename]]
        ]
    )) {
      // $CSDBModel->initiator; // agar ada initiator nya
      return $this->ret2(200, ["New {$CSDBModel->filename} has been created."], ["csdb" => $CSDBModel], ['infotype' => 'info']);
    }
    return $this->ret2(400, ["{$CSDBModel->filename} failed to create."], CSDBError::getErrors(), ['csdb' => $CSDBModel]);
  }

  /**
   * jika user mengubah filename, filename akan kembali seperti asalnya karena update akan mengubah seluruhnya selain filename
   * @return Response JSON contain SQL object model with initiator data
   */
  public function update(CsdbUpdateByXMLEditor $request, string $filename)
  {
    $CSDBModel = $request->validated()['oldCSDBModel'][0];
    $CSDBModel->CSDBObject = $request->validated()['xmleditor'][0];
    $CSDBModel->path = $request->validated()['path'];
    if ($CSDBModel->saveDOMandModel($request->user()->storage,[
      History::MAKE_CSDB_UPDT_History($CSDBModel),
      History::MAKE_USER_UPDT_History($request->user(),'',$CSDBModel->filename)
    ])) {
      // $CSDBModel->initiator; // agar ada initiator nya
      return $this->ret2(200, ["New {$CSDBModel->filename} has been update."], ["csdb" => $CSDBModel], ['infotype' => 'info']);
    }
    return $this->ret2(400, ["{$CSDBModel->filename} failed to update."], CSDBError::getErrors(), ['csdb' => $CSDBModel]);
  }

  public function read_json(Request $request, string $filename)
  {
    if (!($OBJECTModel = Csdb::getObject($filename, ['exception' => ['CSDB-DELL', 'CSDB-PDEL']])->first())) return $this->ret2(400, ["{$filename} fails to be showed."]);
    $OBJECTModel->CSDBObject->load(CSDB_STORAGE_PATH . "/" . $request->user()->storage . "/" . $filename);
    $json = json_decode(CSDBStatic::xml_to_json($OBJECTModel->CSDBObject->document));
    return $this->ret2(200, ['model' => $OBJECTModel->makeHidden(['id']), 'json' => $json]); // ini yang dipakai vue
  }

  public function read_pdf_object(Request $request, Csdb $CSDBModel)
  {
    // $modelIdentCode = Helper::get_attribute_from_filename($CSDBModel->filename, 'modelIdentCode');  
    $modelIdentCode = 'CN235';
    $config = new \DOMDocument();
    $config->validateOnParse = true;
    $config->load(CSDB_VIEW_PATH . "/xsl/Config.xml");
    $xpath = new \DOMXPath($config);
    $fo = CSDB_VIEW_PATH . "/xsl" . "/" . $xpath->evaluate("string(//method[@type='pdf']/pathCache)") . "/" . $CSDBModel->filename . ".fo";
    $response = Response::make();
    $pathxsl = CSDB_VIEW_PATH . "/xsl" . "/" . $xpath->evaluate("string(//config/output/method[@type='pdf']/path[@product-name='{$modelIdentCode}' or @product-name='*'])");

    $storage = $CSDBModel->initiator->storage;
    $CSDBModel->CSDBObject->load(CSDB_STORAGE_PATH . "/" . $storage . "/" . $CSDBModel->filename);
    $CSDBModel->CSDBObject->setConfigXML(CSDB_VIEW_PATH . DIRECTORY_SEPARATOR . "xsl" . DIRECTORY_SEPARATOR . "Config.xml"); // nanti diubah mungkin berbeda antara pdf dan html meskupun harusnya SAMA. Nanti ConfigXML mungkin tidak diperlukan jika fitur BREX sudah siap sepenuhnya.

    CSDBStatic::$footnotePositionStore[$CSDBModel->filename] = [];
    $transformed = $CSDBModel->CSDBObject->transform_to_xml($pathxsl, [
      "filename" => $CSDBModel->filename,
      "alertPathBackground" => "file:///" . str_replace("\\", "/", CSDB_VIEW_PATH . "/xsl/pdf/assets"),
    ]);
    if (file_put_contents($fo, $transformed) and ($pdf = Fop::FO_to_PDF($fo))) {
      $response->header('Content-Type', 'application/pdf');
      return $response->setContent($pdf);
    }
    abort(400);
  }
  
  public function read_html_object(Request $request, Csdb $CSDBModel)
  {
  }

  /**
   * nanti dipakai saat orang ingin cetak PDF/HTML atau sekedar ngecek ketersediaan object di folder mereka masing2
   */
  public function checkAvailableObject(Request $request)
  {
    $dir = scandir(CSDB_STORAGE_PATH . "/" . $request->user()->storage);
    $dir = array_filter($dir, fn ($v) => substr($v, -4, 4) === '.xml');
    $analyze = [];
    $unavailable = [];
    foreach ($dir as $filename) {
      $refObjects = [];
      $CSDBObject = new CSDBObject("5.0");
      $CSDBObject->load(CSDB_STORAGE_PATH . "/" . $request->user()->storage . "/" . $filename);
      $domXpath = new \DOMXpath($CSDBObject->document);
      $ref = $domXpath->evaluate("//dmRefIdent|//pmRefIdent|*[@infoEntityIdent]");
      foreach ($ref as $el) {
        $f = '';
        $tagName = $el->tagName;
        switch ($tagName) {
          case 'dmRefIdent':
            $f = CSDBStatic::resolve_dmIdent($el);
            break;
          case 'pmRefIdent':
            $f = CSDBStatic::resolve_pmIdent($el);
            break;
          default:
            $f = $el->getAttribute('infoEntityIdent');
            break;
        }
        if (!(in_array($f, $dir))) {
          $refObjects[] = $f;
          $unavailable[] = $f;
        };
      }
      if (!empty($refObjects)) {
        $analyze[] = [
          'calledin' => $filename,
          'unavailable' => $refObjects,
        ];
      }
    }
    $unavailable = array_unique($unavailable);
    array_walk($unavailable, function (&$v) use ($analyze) {
      $cin = [];
      foreach ($analyze as $a) {
        if (in_array($v, $a['unavailable'])) {
          $cin[] = $a['calledin'];
        }
      }
      $v = [
        'filename' => $v,
        'calledin' => $cin,
      ];
    }, $analyze);
    return $this->ret2(200, ['analyze' => $analyze, 'unavailable' => $unavailable]);
  }

  public function get_object_raw(Request $request, Csdb $CSDBModel)
  {
    $CSDBModel->CSDBObject->load(CSDB_STORAGE_PATH . "/" . $request->user()->storage . "/" . $CSDBModel->filename);
    if ($CSDBModel->CSDBObject->document) {
      $formatter = new Formatter();
      return Response::make($formatter->format($CSDBModel->CSDBObject->document->saveXML()), 200, ['Content-Type' => 'text/xml']);
    }
    return $this->ret2(200, "There is no such of {$CSDBModel->filename}", ['headers' => ['Content-Type' => 'text/xml']]);
  }

  public function get_icn_raw(Request $request, Csdb $CSDBModel)
  {
    $CSDBModel->CSDBObject->load(CSDB_STORAGE_PATH . "/" . $request->user()->storage . "/" . $CSDBModel->filename);
    return Response::make($CSDBModel->CSDBObject->document->getFile(), 200, [
      'Content-Type' => $CSDBModel->CSDBObject->document->getFileinfo()['mime_type'],
    ]);
  }

  ###### semua fungsi lama taruh dibawah ######

  /**
   * always return code 200
   * biasa dipakai listtree
   */
  public function get_allobjects_list(Request $request)
  {
    // $CSDBModel = new Csdb();
    // $query = History::generateWhereRawQueryString_historyException(['CSDB-DELL', 'CSDB-PDEL'],get_class($CSDBModel),$CSDBModel->getTable());
    // $query[0] = Helper::replaceSQLQueryWithBindedParam($query[0],$query[1],$query[2],$query[3]);
    // $ret = $CSDBModel->whereRaw($query[0]); // jika mau pakai replace @replaceSQLQueryWithBindedParam()
    // $ret = $CSDBModel->whereRaw($query[0], $query[1]);
    // $csdbs = $csdbs->where('initiator_id',$request->user()->id);
    $csdbs = Csdb::getCsdbs(['exception' => ['CSDB-DELL', 'CSDB-PDEL']]);
    $csdbs = $csdbs->get(['filename','path'])->toArray();
    return $this->ret2(200, ["csdbs" => $csdbs]);
  }

  /**
   * return model which instance of Dmc,Pmc, Dml, other
   */
  public function get_object_model(Request $request, string $filename)
  {
    // $type = substr($filename, 0, 3);
    // $model = Csdb::getModelClass(ucfirst($type)); // gabisa kalau mencari comment karena modelnya bukan 'Com.php' tapi 'Comment.php'
    // $model->setProtected(['with' => 'csdb.initiator']);
    // $model = $model->where('filename', $filename)->first();
    $model = Csdb::getObject($filename)->first();
    return $model ? $this->ret2(200, ["model" => $model->toArray()]) : $this->ret2(400, ["no such {$filename} available."]);
  }

  /**
   * return csdbs which return instance of Csdb::class
   */
  public function get_object_csdbs(Request $request)
  {
    // $CSDBModel = new Csdb();
    // $query = Helper::generateWhereRawQueryString($request->get('sc'), $CSDBModel->getTable());
    // $queryException = History::generateWhereRawQueryString_historyException(['CSDB-DELL', 'CSDB-PDEL'],get_class($CSDBModel),$CSDBModel->getTable());
    // $ret = $CSDBModel->whereRaw($query[0],$query[1]);
    // $ret = $ret->whereRaw($queryException[0],$queryException[1]);
    // $ret = $ret->orderBy('filename')->get();
    // return $this->ret2(200,['csdbs' => $ret->toArray()]);

    $CSDBModels = Csdb::searchCsdbs($request->get('sc'), ['exception' => ['CSDB-DELL', 'CSDB-PDEL']]);
    $CSDBModels = $CSDBModels->orderBy('filename')->get();
    return $this->ret2(200,['csdbs' => $CSDBModels->toArray()]);
  }

  public function forfolder_get_allobjects_list(Request $request)
  {
    // validasi. Jadi ketika tidak ada path ataupun sc, ataupun filename (KOSONG) maka akan mencari path = "csdb/"
    if ($request->path === "/") $request->merge(['path' => 'csdb']);

    // menyiapkan csdb object
    $CSDBModels = Csdb::with(['initiator','lastHistory']);
    // $keywords = Helper::explodeSearchKeyAndValue($request->get('sc'), 'filename',["typeonly"=>"filename"]); // nanti ditiadakan typeonly karena DML dan DMC digabung di Explorer.vue
    $keywords = Helper::explodeSearchKeyAndValue($request->get('sc'), 'filename');
    $query = Helper::generateWhereRawQueryString($keywords, $CSDBModels->getModel()->getTable(), ['path' => "#&value;"]);
    $queryExecption = History::generateWhereRawQueryString_historyException(['CSDB-DELL', 'CSDB-PDEL'],Csdb::class,$CSDBModels->getModel()->getTable());
    $CSDBModels = $CSDBModels->whereRaw($query[0], $query[1]);
    $CSDBModels = $CSDBModels->whereRaw($queryExecption[0], $queryExecption[1]);
    $CSDBModels = $CSDBModels->where('storage_id', $request->user()->id);
    $CSDBModels = $CSDBModels->orderBy('filename')->paginate(100);
    $CSDBModels->setPath($request->getUri());
    
    $m = '';
    // menyiapkan folder
    if (isset($keywords['path'])) {
      $folders = new Csdb();

      // menambah slash pada ujung path agar bisa dicari sub folder nya
      array_walk($keywords['path'], fn(&$v) => ((substr($keywords['path'][0], -1,1) !== '/') ? ($v = $v . "/") : ''));

      // make query and get
      $query = Helper::generateWhereRawQueryString(['path' => $keywords['path']],$folders->getTable());
      $folders = $folders->where('storage_id', $request->user()->id);
      $folders = $folders->whereRaw($query[0],$query[1]);
      $folders = $folders->whereRaw($queryExecption[0],$queryExecption[1]);
      $folders = $folders->get(['path'])->toArray();
      $folders = array_unique($folders, SORT_REGULAR);
      $folders = array_values(($folders));
      array_walk($keywords['path'], fn(&$v) => $v = substr($keywords['path'][0],0,-1) ); // menghilangkan '/' di ujung path query yang ditambah sebelumnya

      // menghilangkan sub-subfolder 
      $l_folders = count($folders);
      $allPath = join("|",$keywords['path']); // jika pencarian multiple path, maka dijoin pakai pipe symbol sesuai pencarian di regex;
      $allPath = str_replace("/","\/",$allPath);
      // dd($folders);
      for ($i=0; $i < $l_folders; $i++) { 
        // pengecekan terhadap setiap keyword paths tidak diperlukan lagi karena saat pencarian setiap path keyword sudah ditambah '/' sehingga pencarian spesifik untuk sub folder 
        $folders[$i] = join("", $folders[$i]); // saat didapat dari database, bentuknya array berisi satu path saja
        $folders[$i] = preg_replace("/({$allPath})(\/[a-zA-Z0-9]+)(\/.+)?/","$1$2",$folders[$i]); // menghilangkan subfolder. eg.: query path='csdb', result='csdb/cn235/amm'. Nah 'amm' nya dihilangkan
      }
      $folders = array_unique($folders,SORT_STRING);
      $folders = array_filter($folders, fn ($v) => ($v != null) || ($v != ''));
      $folders = array_values($folders); // supaya tidak assoc atau supaya indexnya teratur
      sort($folders);
    }

    // return
    if (isset($keywords['path']) and count($keywords['path']) === 1) $current_path = $keywords['path'][0];
    $CSDBModels = $CSDBModels->toArray();
    $CSDBModels['csdbs'] = $CSDBModels['data'];
    unset($CSDBModels['data']);
    return $this->ret2(200, $CSDBModels, ['message' => $m, 'infotype' => "caution", 'folders' => $folders ?? [], "current_path" => $current_path ?? '']);
  }

  /**
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
    if ($CSDBModel->saveDOMandModel($request->user()->storage,[
      [$request->isUpdate ? 'MAKE_CSDB_UPDT_History' : 'MAKE_CSDB_CRBT_History', [Csdb::class]],
      [$request->isUpdate ? 'MAKE_USER_UPDT_History' : 'MAKE_USER_CRBT_History', [$request->user(),'',$CSDBModel->filename]],
    ])) {
      $CSDBModel->initiator; // agar ada initiator nya
      
      $message = $request->isUpdate ? "{$CSDBModel->filename} has been updated." : "New {$CSDBModel->filename} has been uploaded.";
      // $message = $request->isUpdate ? "New {$CSDBModel->filename} has been uploaded." : "{$CSDBModel->filename} has been updated." ;
      return $this->ret2(200, [$message], ["csdb" => $CSDBModel, 'infotype' => 'info']);
    } else {
      return $this->ret2(400, ["{$validatedData['filename']} failed to upload."], CSDBError::getErrors(), ["csdb" => $CSDBModel]);
    }
  }

  /**
   * jika ada filenamSearch, default pencarian adalah column 'filename'
   */
  public function get_deletion_list(Request $request)
  {
    $CSDBModels = Csdb::getCsdbs(['code' => ['CSDB-DELL']]);
    $CSDBModels = $CSDBModels->with(['lastHistory']);
    $CSDBModels = $CSDBModels->paginate(10)->toArray();
    $CSDBModels['csdbs'] = $CSDBModels['data'];
    unset($CSDBModels['data']);
    return $this->ret2(200, $CSDBModels);
  }

  /**
   * belum bisa menangani banyak file untuk dibuatkan zip
   */
  public function download_objects(Download $request)
  {
    $validatedData = $request->validated();
    $l = count($validatedData['CSDBModelArray']);    
    if($l > 1){
      // handle zipping file here
      $zipName = $request->user()->storage . "_". now()->getTimestamp() . ".zip";
      $zip = new ZipStream(
        outputName: $zipName,
        sendHttpHeaders: false,
      );
      $storage = $request->user()->storage;
      for ($i=0; $i < $l; $i++) { 
        $zip->addFileFromPath(
          fileName: $validatedData['CSDBModelArray'][$i]->filename,
          path: CSDB_STORAGE_PATH . DIRECTORY_SEPARATOR . $storage . DIRECTORY_SEPARATOR . $validatedData['CSDBModelArray'][$i]->filename,
        );
      }
      
      $zip->finish();
      return response()->streamDownload(fn() => $zip, $zipName,[
        // 'Content-Type' => 'application/octet-stream',
        // 'Content-Transfer-Encoding' => 'Binary',
        // 'Content-disposition' => 'attachment; filename="'. $zipName .'"', // tidak diperlukan karena sidah di parameter @streamDownload()
        'Content-Type' => 'application/zip', // biar bisa di buka di browser, bukan pakai 'application/octet-stream'
      ]);
    }
    elseif($l === 1){
      $name = $request->user()->storage . "/". $validatedData['CSDBModelArray'][0]->filename;
      $file = Storage::disk('csdb')->get($name);
      $mime = Storage::disk('csdb')->mimeType($name);
      return response()->streamDownload(fn()=>$file,$validatedData['CSDBModelArray'][0]->filename,[
        'Content-Type' => $mime,
      ]);
    }
    abort(404);
  }

  /**
   * @return array
   */
  public function change_object_path(CsdbChangePath $request)
  {
    $validatedData = $request->validated();
    foreach($validatedData['CSDBModelArray'] as $model){
      $model->path = (string) $validatedData['path'];
      if($model->save()){
        History::saveModel([History::MAKE_CSDB_PATH_History($model)]);
      }
    }
    return $this->ret2(200,[ join(", ", $validatedData['filename']) . " success move to {$validatedData['path']}." ],['infotype' => 'info']);
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
    for ($i=0; $i < $qtyCSDBs; $i++) { 
      $CSDB_HISTORYModel = History::MAKE_CSDB_DELL_History($CSDBModels[$i]);
      // $OBJECT_HISTORYModel = History::MAKE_CSDB_DELL_History($CSDBModels[$i]->meta);
      $USER_HISTORYModel = History::MAKE_USER_DELL_History($request->user(),'', $CSDBModels[$i]->filename);
      if(History::saveModel([$CSDB_HISTORYModel, $USER_HISTORYModel])){
        $result['success'][] = $CSDBModels[$i]->filename;
      } else {
        $result['fail'][] = $CSDBModels[$i]->filename;
      }
    }
    $totalFail = count($result['fail']);
    $totalSuccess = count($result['success']);
    $infotype = $totalSuccess < 1 ? "warning" : ($totalFail > 0 ? 'caution' : 'info');
    $m = ($totalSuccess > 0 ? 'Success delete to ' . join(", ", $result['success']) : '') . ($totalFail > 0 ? " and fail delete to " . join(", ", $result['fail']) : '');
    return $this->ret2(200, ['message' => $m,'infotype' => $infotype]);
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
    for ($i=0; $i < $qtyCSDBs; $i++) { 
      $CSDB_HISTORYModel = History::MAKE_CSDB_PDEL_History($CSDBModels[$i]);
      // $OBJECT_HISTORYModel = History::MAKE_CSDB_PDEL_History($CSDBModels[$i]->object);
      $USER_HISTORYModel = History::MAKE_USER_PDEL_History($request->user(),'', $CSDBModels[$i]->filename);
      if(History::saveModel([$CSDB_HISTORYModel,$USER_HISTORYModel])){
        $result['success'][] = $CSDBModels[$i]->filename;
      } else {
        $result['fail'][] = $CSDBModels[$i]->filename;
      }
    }
    $totalFail = count($result['fail']);
    $totalSuccess = count($result['success']);
    $infotype = $totalSuccess < 1 ? "warning" : ($totalFail > 0 ? 'caution' : 'info');
    $m = ($totalSuccess > 0 ? 'Success permanent delete to ' . join(", ", $result['success']) : '') . ($totalFail > 0 ? " and fail permanent delete to " . join(", ", $result['fail']) : '');
    return $this->ret2(200, ['message' => $m,'infotype' => $infotype]);
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
    for ($i=0; $i < $qtyCSDBs; $i++) { 
      $CSDB_HISTORYModel = History::MAKE_CSDB_RSTR_History($CSDBModels[$i]);
      $USER_HISTORYModel = History::MAKE_USER_RSTR_History($request->user(),'', $CSDBModels[$i]->filename);
      if(History::saveModel([$CSDB_HISTORYModel,$USER_HISTORYModel])){
        $result['success'][] = $CSDBModels[$i]->filename;
      } else {
        $result['fail'][] = $CSDBModels[$i]->filename;
      }
    }
    $totalFail = count($result['fail']);
    $totalSuccess = count($result['success']);
    $infotype = $totalSuccess < 1 ? "warning" : ($totalFail > 0 ? 'caution' : 'info');
    $m = ($totalSuccess > 0 ? 'Success restore  to ' . join(", ", $result['success']) : '') . ($totalFail > 0 ? " and fail restore to " . join(", ", $result['fail']) : '');
    return $this->ret2(200, ['message' => $m,'infotype' => $infotype]);
  }
}
