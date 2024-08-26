<?php

namespace App\Http\Controllers;

// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// use Illuminate\Foundation\Validation\ValidatesRequests;

use App\Models\Csdb;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Ptdi\Mpub\Main\Helper;
use SimpleXMLElement;

class Controller extends BaseController
{
  // use AuthorizesRequests, ValidatesRequests;

  public function index(Request $request)
  {
    // return view('welcome');
    // return view('techpub.app');
    return redirect(RouteServiceProvider::CSDB);
  }

  public function authcheck()
  {
    // dd(Auth::user()->toArray());
    return response()->json(
      Auth::user()->toArray(),200);
    // return response()->json([
    //   'name' => Auth::user()->name,
    //   'email' => Auth::user()->email,
    // ],200);
  }

  // public function getWorkerJs(Request $request)
  // {
  //   $worker = file_get_contents(resource_path('js/csdb4/worker.js'));
  //   return Response::make($worker,200,[
  //     'content-type' => 'application/javascript'
  //   ]);
  // }
  // public function getAxiosJs(Request $request)
  // {
  //   $axoiospath = realpath(getcwd().'../../node_modules/axios/dist/axios.js');
  //   $axios = file_get_contents($axoiospath);
  //   return Response::make($axios,200,[
  //     'content-type' => 'application/javascript'
  //   ]);
  // }

  public static function getAllRoutesNamed()
  {
    $allRoutes = Route::getRoutes()->getRoutes();
    $allRoutes = array_filter($allRoutes, fn ($r) => $r->getName());
    
    foreach ($allRoutes as $k => $v) {
      $allRoutes[$v->getName()] = $v;
      unset($allRoutes[$k]);
    }
    $allRoutes = array_map(function ($v) {
      $params = $v->parameterNames(); // ['csdb', 'repo']
      $bindingFields = $v->bindingFields(); // ['csdb' => 'filename', 'repo' => 'name']
      foreach($params as $k => $param){
        if(!empty($bindingFields) AND isset($bindingFields[$param])){
          $param = $bindingFields[$param];  // param 'csdb' diubah menjadi 'filename'. karena csdb adalah model class 
        }
        $params[$param] = ":$param";
        unset($params[$k]);
      }
      $path = route($v->getName(), array_values($params), false); // "/api/ietm/{repo:name}/{filename}" menjadi "/api/ietm/:name/:filename" agar JS mudah memasukkan key dan valuenya
      $ret = [
        'name' => $v->getName(),
        'method' => $v->methods(),
        'path' => $path,
        'params' => $params,
      ];
      return $ret;
    }, $allRoutes);
    return $allRoutes;
  }


  /**
   * digunakan untuk mencari file.
   * Fungsi ini dipakai saat seeding csdb SQL
   */
  public static function get_file(string $path, string $filename = '', bool $all = false){
    $exclude = array('.','..','.git','.gitignore');
    // $arr = array_diff(scandir($dir), $exclude);
    if($path && $filename){
      $file = file_get_contents($path.DIRECTORY_SEPARATOR.$filename, FILE_USE_INCLUDE_PATH);
      return [$file, mime_content_type($path.DIRECTORY_SEPARATOR.$filename)];
    }
    // elseif($path){
    elseif($path){
      $list = array();
      $ls = array_diff(scandir($path), $exclude);
      foreach($ls as $l){
        if(!is_dir($path.DIRECTORY_SEPARATOR.$l)){
          array_push($list,$l);
        }
      }
      return $list;
    }
  }

  /**
   * DEPRECIATED
   */
  public function ret($code, $messages = [])
  {
    $data = [
      'messages' => $messages,
    ];
    $args = func_get_args();
    for ($i=2; $i < count($args); $i++) { 
      if(is_array($args[$i])){
        $data = array_merge($data, $args[$i]);
      } else {
        $data[] = $args[$i];
      }
    }
    return response()->json($data, $code);
  }

  /**
   * akan membuat output sama dengan $fail() pada $request->validate();
   * jika ingin mengirim response berupa $model->get(), maka parameter adalah [data: $this->model->get()->toArray()];
   * jika ingin mengirim response berupa $model->paginate(), maka parameter adalah $this->model->toArray();
   */
  public function ret2($code, $messages = [])
  {
    $data = ["message" => ''];
    $args = func_get_args();

    // if(!($code >= 200 AND $code < 300)){
    //   $data['errors'] = [];
    //   $data['infotype'] = "warning";
    // } else {
    //   $data['infotype'] = "note";
    // }

    $isArr = function($message, $fn) use(&$data, $code){
      if(is_array($message)) {
        foreach($message as $key => $value){
          // jika array sequential 
          if((int)$key OR $key == 0){
            if(is_array($value)){
              $fn($value, $fn);
            } else {
              if($value){
                $data['message'] .= $value . \PHP_EOL;
              }
            }
          }
          // jika array assoc
          else {
            if(isset($data['errors'])){
              $data['errors'][$key] = $value;
            } else {
              $data[$key] = $value;
            }
          }
        }
      } else {
        if($message){
          $data['message'] .= $message .\PHP_EOL;
        }
      }
    };

    for ($i=1; $i < count($args); $i++) { 
      $message =  $args[$i];
      $isArr($message, $isArr);
    }
    // return response()->json($data, $code);
    $headers = $data['headers'] ?? ['Content-Type' => 'application/json'];
    unset($data['headers']);
    $contentType = $headers['Content-Type'] ?? $headers['content-Type'] ?? $headers['Content-type'] ?? $headers['content-type'];
    if($contentType === 'text/xml'){
      $xml = new SimpleXMLElement('<responsedata/>');
      $this->arrayToXml($data, $xml);
      return Response::make($xml->saveXML(),$code,$headers);
    }
    return Response::make($data,$code,$headers);
  }

    /**
   * Convert an array to XML
   * @param array $array
   * @param SimpleXMLElement $xml
   */
  private function arrayToXml($array, &$xml){
    foreach ($array as $key => $value) {
        if(is_int($key)){
            $key = "e";
        }
        if(is_array($value)){
            $label = $xml->addChild($key);
            $this->arrayToXml($value, $label);
        }
        else {
            $xml->addChild($key, $value);
        }
    }
  }


  
  /**
   * all returned array contains path is relative path
   */
  public static function searchFile(string $path, string $filename = '', bool $getFile = false)
  {
    $exclude = array('.','..','.git','.gitignore');
    $list = array();
    foreach(array_diff(scandir($path), $exclude) as $l){
      if(is_dir($path."/".$l)){
        $x = self::searchFile($path."/".$l); // get file
        $x = array_map((fn($v) => $l."/".$v), $x); // agar jadi relative path
        $list = array_merge($list, $x);
      } else {
        $list[] = $l;
      }
    }
    if($filename){
      $text =  array_values(array_filter($list, fn($v) => str_contains($v, $filename)))[0];
      if($getFile){
        return file_get_contents($path. DIRECTORY_SEPARATOR. $text);
      } else {
        return $text;
      }
    }
    return $list;
  }

  // public function route(Request $request, string $name)
  // {
  //   return redirect()->route($name, $request->all());
  // }

    /**
   * $model bisa berupa ModelsCsdb atau DB::table()
   */
  // public mixed $model;

  /**
   * default search column db is filename
   * harus set $this->model terlebih dahulu baru bisa jalankan fungsi ini
   */
  public function search_xx($keyword, &$messages = [])
  {
    $keywords = Helper::explodeSearchKey(str_replace("_",'\_',$keyword));
    // dd($keywords);
    foreach($keywords as $k => $kyword){
      if((int)$k OR $k == 0){ // jika $kyword == eg.: 'MALE-0001Z-P', ini tidak ada separator '::' jadi default pencarian column filename
        $kywords = Helper::exploreSearchValue($kyword);
        $this->model->whereRaw("filename LIKE '%{$kywords[0]}%' ESCAPE '\'");
        for ($i=1; $i < count($kywords); $i++) { 
          $this->model->orWhereRaw("filename LIKE '%{$kywords[$i]}%' ESCAPE '\'");
        }
      } else {
        if($k === 'typeonly'){
          $column = 'filename';
          // $column = '';
        } else {
          $column = Csdb::columnNameMatching($k, 'csdb');
        }
        if($column){
          $kywords = Helper::exploreSearchValue($kyword);
          $this->model->whereRaw("{$column} LIKE '%{$kywords[0]}%' ESCAPE '\'");
          for ($i=1; $i < count($kywords); $i++) {
            $this->model->orWhereRaw("{$column} LIKE '%{$kywords[$i]}%' ESCAPE '\'");
          }
          // if($k == 'typeonly'){
          //   $this->model->whereRaw("path LIKE '%male%' ESCAPE '\'");
          // }
        }
        else {
          $messages[] = "'{$keyword}' cannot be parsed.";
        }
      }
    }
    // dd($this->model);
    // dd($keywords);
    return $keywords;
  }

  // QUERY untuk undeleted
  // SELECT * FROM csdb WHERE id IN ( SELECT history.owner_id FROM history WHERE history.owner_class = 'App\\Models\\Csdb' AND (history.code <> 'CSDB-DELL' OR history.code <> 'CRBT-PDEL') AND history.created_at = (SELECT max(history.created_at) from history) )
  /**
   * DEPRECIATED, dipindah ke PTDI MPUB Helper jadi static function
   * jika "?sc=DMC" => maka querynya WHERE each.column like %DMC% , joined by 'OR';
   * jika "?sc=filename::DMC%20path::csdb" => maka querynya WHERE filename LIKE '%DMC%' AND path LIKE '%csdb%';
   * jika "?sc=filename::DMC,PMC" => maka querynya WHERE filename LIKE '%DMC%' OR filename LIKE '%PMC%';
   * jika "?sc=filename::DMC%20filename::022" => maka querynya WHERE filename LIKE '%DMC%' AND filename LIKE '%022%';
   * @param Array, index0 = query string, index1 = exploded keywords
   * @return Array
   */
  // public function generateWhereRawQueryString($keyword, Array $strictString = ['col' => "%#&value;%"], string $table = '', $historyCodeExeception = ['CSDB-DELL', 'CSDB-PDEL'])
  // public function generateWhereRawQueryString($keyword, string $table, Array $strictString = ['col' => "%#&value;%"])
  // {
  //   // $isFitted = false;
  //   // contoh1
  //   // $keywords = [
  //   //   'path' => ['A','B'],
  //   //   'filename' => ['C','D', 'E'],
  //   //   'editable' => ['F','G'],
  //   // ];
  //   // contoh2
  //   // $keywords = [
  //   //   'path' => ['A','B'],
  //   //   'filename' => ['C'],
  //   // ];
  //   // contoh3
  //   // $keywords = [
  //   //   'path' => ['A'],
  //   //   'filename' => ['B','C','D'],
  //   //   'editable' => ['E'],
  //   // ];
  //   $keywords = is_array($keyword) ? $keyword : Helper::explodeSearchKeyAndValue($keyword);
  //   if(empty($keywords)) return [];

  //   // jika $keyword tidak ada column namenya, maka akan mengambil seluruh column name database
  //   // contoh $request->sc = "Senchou";. Kita tidak tahu 'Senchou' ini dicari di column mana, jadi cari di semua column di database
  //   // $table = $table ? $table : ($this->model instanceof Builder ? $this->model->getModel()->getTable() : $this->model->getTable());
  //   // $fitToColumn = function($keywordsExploded)use($table){
  //   //   $column = DB::getSchemaBuilder()->getColumnListing($table);
  //   //   for ($i=0; (int)$i < count($column); $i++) { 
  //   //     $k = $column[$i];
  //   //     $column[$k] = $keywordsExploded;
  //   //     unset($column[$i]);
  //   //   }
  //   //   return $column;
  //   // };
    
  //   // if(isset($this->model) && (get_class($this->model) === Csdb::class)){
  //   //   if(array_is_list($keywords)){
  //   //     $keywords = $fitToColumn($keywords);
  //   //     $isFitted = true;
  //   //   }
  //   //   // $keywords['path'] = array_map(fn($v) => $v = substr($v,-1,1) === '/' ? $v : $v . "/", $keywords['path']);
  //   //   // $keywords['initiator_id'] = $keywords['initiator_id'] ?? [Auth::user()->id]; // kayaknya ini ga perlu karena suatu saat ada orang yang import csdb object pakai DDN
  //   //   $keywords['available_storage'] = [Auth::user()->storage];
  //   // } else {
  //   //   if(array_is_list($keywords)){
  //   //     $keywords = $fitToColumn($keywords);
  //   //     $isFitted = true;
  //   //   }
  //   // }
  //   // dump($keywords);

  //   // create space
  //   $keys = array_keys($keywords);
  //   $createSpace = function($k, $space = '', $cb)use($keywords, $keys ){
  //     // create space
  //     $queryArr = $keywords[$keys[$k]];
  //     $l = count($queryArr);
  //     $isNextCol = isset($keys[$k+1]);   
  //     $squareOpen = 0;
  //     $curvOpen = 0;
  //     if($l-1 > 0 AND $isNextCol){
  //       $space .= "{";
  //       $curvOpen++;
  //     }
  //     elseif($l-1 > 0) {
  //       $space .= "[";
  //       $squareOpen++;
  //     }
  //     // untuk perbaikan contoh3 dan contoh3
  //     elseif($l === 1 AND !$isNextCol) {
  //       $space .= "[";
  //       $squareOpen++;
  //     }
  //     else {
  //       $space .= "{";
  //       $curvOpen++;
  //     };
  //     for ($i=0; $i < $l; $i++) { 
  //       $isNextIndex = $i+1 < $l;
  //       $space .= '"COL'.$k.'_'.$i.'"';
  //       if($isNextCol){
  //         $space .= ":";
  //         $space .= $cb($k+1, '', $cb);
  //       }
  //       if($isNextIndex) $space .= ",";
  //     }
  //     while($curvOpen > 0){
  //       $space .= "}";
  //       $curvOpen--;
  //     }
  //     while($squareOpen > 0){
  //       $space .= "]";
  //       $squareOpen--;
  //     }
  //     return $space;
  //   };
  //   $space = $createSpace(0,'', $createSpace);

  //   // fill the space
  //   $vCode = " ? ";
  //   $dictionary = array();
  //   $dictionaryBindValue = array();
  //   foreach($keywords as $col => $queryArr){
  //     $colnum = array_search($col,$keys);
  //     if($col === 'typeonly') $col = 'filename';
  //     foreach($queryArr as $i => $v){
  //       $indexString = "COL{$colnum}_{$i}";
  //       $id = rand(0,9999); // mencegah kalau kalau ada value yang sama antar column
  //       // $escapedV = str_replace("_", "\_",$v); // sudah dicoba di SQLITE tapi error di MySQL
  //       $escapedV = str_replace("_", "|_",$v); // sudah dicoba di MySQL (belum dicoba di SQLITE) dan sesuai ref book MySQL 8.4 page 2170/6000
  //       $strictStr = str_replace('#&value;', $escapedV, $strictString[$col] ?? "%#&value;%"); // variable $strictString jangan di re asign
  //       $col = preg_replace("/___[0-9]+$/", "", $col); // menghilangkan suffix "___XXX" yang ditambahkan di fungsi ...Main\Helper::class@explodeSearchKeyAndValue
  //       // $dictionary["<<".$v.$id.">>"] = " {$col} LIKE '{$strictStr}' ESCAPE '\'"; // // sudah dicoba di SQLITE tapi error di MySQL
  //       // $dictionary["<<".$v.$id.">>"] = " {$col} LIKE '{$strictStr}' ESCAPE '|'"; // sudah dicoba di MySQL (belum dicoba di SQLITE) dan sesuai ref book MySQL 8.4 page 2170/60004 https://downloads.mysql.com/docs/refman-8.4-en.a4.pdf
  //       // ditambah $table.$col agar query tidak bingung karena ada table name sebelum column
  //       // $dictionary["<<".$v.$id.">>"] = " {$table}.{$col} LIKE '{$strictStr}' ESCAPE '|'"; // sudah dicoba di MySQL (belum dicoba di SQLITE) dan sesuai ref book MySQL 8.4 page 2170/60004 https://downloads.mysql.com/docs/refman-8.4-en.a4.pdf
  //       $dictionary["<<".$v.$id.">>"] = " {$table}.{$col} LIKE {$vCode} ESCAPE '|'"; // sudah dicoba di MySQL (belum dicoba di SQLITE) dan sesuai ref book MySQL 8.4 page 2170/60004 https://downloads.mysql.com/docs/refman-8.4-en.a4.pdf
  //       $space = str_replace($indexString, "<<".$v.$id.">>", $space);
  //       $dictionaryBindValue["<<".$v.$id.">>"] = $strictStr;
  //     }
  //   }

  //   // change the filled space to the final string query
  //   $arr = json_decode($space,true);
  //   $str = '';
  //   $merge = function($prevVal, $arr, $cb){
  //     $str = '';
  //     // $joinAND = !$isFitted ? ' AND ' : ' OR '; // kalau di fittedkan artinya satu keyword untuk mencari semua column. Artinya query SQL akan join pakai OR
  //     $joinAND = ' AND ';
  //     if(array_is_list($arr)){
  //       foreach($arr as $i => $v){
  //         if($prevVal) $arr[$i] = "$prevVal".$joinAND."$v";
  //         else $arr[$i] = "$v";
  //         $arr[$i] = "(" . $arr[$i] . ")"; // tambahan agar setiap setelah AND akan di kurung
  //       }
  //       $str = join(" OR ", $arr);
  //     } else { // jika bukan aray assoc maka berarti ini adalah kolom terakhir
  //       foreach($arr as $i => $v){
  //         if($prevVal) $arr[$i] = $cb($prevVal . $joinAND . $i, $v, $cb);
  //         else $arr[$i] = $cb($prevVal . $i, $v, $cb);
  //       }
  //       $str = join(" OR ", $arr);
  //     }
  //     return $str;
  //   };
  //   $str = "(".$merge($str, $arr, $merge). ")"; // dikurung agar tidak tergabung dengan variable $options

  //   // replace string by the dictionary value and make bindValue and types
  //   preg_match_all("/<<[^>]+>>/",$str,$bindValue,PREG_PATTERN_ORDER,0);
  //   $bindValue = join(";;;;",$bindValue[0]); // match regex adalah $result = [ ['','',''] ], jadi [0] adalah mengambil isi match nya;
  //   $types = $bindValue;
  //   foreach($dictionary as $k => $v){
  //     $str = str_replace($k,$v, $str);
  //     $bindValue = str_replace($k,$dictionaryBindValue[$k], $bindValue);
  //     $types = str_replace($k, (is_integer($dictionaryBindValue[$k]) ?  'i' : (is_double($dictionaryBindValue[$k]) ? 'd' : 's')), $types);
  //   }
  //   $bindValue = explode(";;;;",$bindValue);
  //   $types = str_replace(";;;;",',',$types);
    
  //   return [(string)$str, (array)$bindValue, (string)$vCode ,(string) $types, $keywords];
  // }

  /**
   * jugo bisa ES6 module
   */
  public function getWorker(Request $request, string $filename)
  {
    $content = file_get_contents(resource_path("js/csdb/worker/{$filename}"));
    return response($content,200,[
      "Content-Type" => "text/javascript",
      "Date" => now()->toString(),
      // "Cache-Control" => 'max-age=604800', // one week
      // "Cache-Control" => 'max-age=60', // one minute
      "Cache-Control" => 'max-age=300', // five minute
    ]);
  }
  
}
