<?php

namespace App\Models\Csdb;

use App\Models\Code;
use App\Models\Csdb;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class History extends Model
{
  use HasFactory;

  // public static bool $useBindParamFormGeneratedQuery = false;

  protected $table = 'history';

  protected $hidden = ['id','owner_class', 'owner_id'];

  public $timestamps = false;

  // protected $fillable = ['code', 'description', 'user_id', 'csdb_id'];
  // protected $fillable = ['code', 'description', 'owner_id', 'owner_class'];
  protected $fillable = ['code', 'description', 'owner_id', 'owner_class', 'created_at'];

  /**
   * FUNGSI: untuk mendapatkan latest history dari sebuah eloquent model
   * tinggal di get() saat selanjutnya
   */
  public static function getHistory(mixed $eloquentModel, array $code = [])
  {
    $ownerClass = addslashes(get_class($eloquentModel)); // jika tidak pakai bindParam, maka tambakan addSlashes pada class. Jadi bukam 'App\Csdb\Model' melainkan 'App\\Csdb\\Model'
    $queryHistory = "(";
    // $queryHistory = "history.code = '{$code}' AND ";
    if(!empty($code)){
      $queryHistory .= "(";
      for ($i=0; $i < count($code); $i++) { 
        $queryHistory .= "history.code LIKE '{$code[$i]}'";
        if(isset($code[$i+1])) $queryHistory .= " OR ";
      }
      $queryHistory .= ") AND ";
    }
    $queryHistory .= "history.created_at = (SELECT MAX(history.created_at) FROM history WHERE history.owner_id = {$eloquentModel->id} AND history.owner_class = '{$ownerClass}')";
    $queryHistory .= ")";
    $HISTORYModel = new self();
    $HISTORYModel = $HISTORYModel->whereRaw($queryHistory);
    return $HISTORYModel;
  }

  /**
   * FUNGSI: untuk mendapatkan semua history dari sebuah eloquent model
   * tinggal di get() saat selanjutnya
   */
  public static function getHistories(mixed $eloquentModel, array $code = [])
  {
    $ownerClass = addslashes(get_class($eloquentModel)); // jika tidak pakai bindParam, maka tambakan addSlashes pada class. Jadi bukan 'App\Csdb\Model' melainkan 'App\\Csdb\\Model'    
    $id = $eloquentModel->id;
    
    $queryHistory = "(";
    // $queryHistory = "history.code = '{$code}' AND ";
    if(!empty($code)){
      $queryHistory .= "(";
      for ($i=0; $i < count($code); $i++) { 
        $queryHistory .= "history.code LIKE '{$code[$i]}'";
        if(isset($code[$i+1])) $queryHistory .= " OR ";
      }
      $queryHistory .= ") AND ";
    }
    $queryHistory .= "`history`.`owner_id` = {$id} AND `history`.`owner_class` = '{$ownerClass}'";
    $queryHistory .= ")";
    $HISTORYModel = new self();
    $HISTORYModel = $HISTORYModel->whereRaw($queryHistory);
    return $HISTORYModel;
  }

  /**
   * ### ini berfungsi untuk generate string dimana SQL model tidak terdapat dalam history dengan code tertentu, biasanya diguankan codenya ['CSDB-DELL', 'CSDB-PDEL'] yang artinya model tidak boleh ada history 'CSDB-DELL' atau CSDB-PDEL di history terakhir 
   * ### lawan dari fungsi ini adalah @generateWhereRawQueryString().
   * contoh kurang tepat =  eg: SELECT * FROM csdb WHERE id IN ( SELECT history.owner_id FROM history WHERE history.owner_class = 'App\\Models\\Csdb' AND (history.code <> 'CSDB-DELL' OR history.code <> 'CRBT-PDEL') AND history.created_at = (SELECT MAX(history.created_at) from history) )
   * contoh yang kurang tepat = 
   * SELECT * FROM csdb WHERE id IN ( 
   * 	  SELECT history.owner_id FROM history WHERE (history.code <> 'CSDB-DELL' OR history.code <> 'CSDB-PDEL') AND history.created_at = (
   * 		  SELECT MAX(history.created_at) FROM history
   *    )
   *  )
   * contoh yang tepat = 
   * -- berhasil jika csdb.id last record = CSDB-DELL
   * -- query ini digunakan untuk mencari CSDBObject yang last record nya bukan CSDB-DELL
   * -- sudah dicoba untuk mencari CSDBObject jumlah 5 record, 2 CSDB-DELL dan 3 lain-lain
   * SELECT * FROM csdb WHERE csdb.id NOT IN (
   * 	  SELECT history.owner_id FROM history WHERE (history.code = 'CSDB-DELL' OR history.code = 'CSDB-PDEL') AND history.created_at = (
   *         SELECT MAX(history.created_at) FROM history WHERE history.owner_id = csdb.id
   *    )
   * )
   */
  public static function generateWhereRawQueryString_historyException($historyCode = [], string $classModel, string $table) :array
  {
    $vCode = " ? ";
    $types = ''; // saat ini masih 'i','d','s'. value 'b' (blob) masih belum tau cara menentukannya
    $bindValue = [];
    $query = '';
    if (count($historyCode) > 0) {
      $query .= "(";
      $query .= "(" . $table . ".id NOT IN (SELECT history.owner_id FROM history WHERE (";
      foreach ($historyCode as $i => $code) {
        $query .= "history.code = {$vCode}";
        $bindValue[] = $code;
        $types .= 's'; // s semua karena code history berupa string
        if (isset($historyCode[$i + 1])) $query .= " OR ";
      }
      $query .= ") AND history.created_at = (SELECT MAX(history.created_at) FROM history WHERE history.owner_id = " .
        $table
        . ".id AND history.owner_class = ";
      $query .=
        $vCode
        . "))))";
      $bindValue[] = $classModel;
      $types .= 's';
    }
    return [(string)$query, (array)$bindValue, (string)$vCode ,(string) $types];
  }

  /**
   * untuk mendapatkan history terkahir. jika $historyCode lebih dari 1, maka akan di join pakai ' OR '
   */
  public static function generateWhereRawQueryString(array $historyCode = [], string $classModel, string $table) :array
  {
    $vCode = " ? ";
    $types = ''; // saat ini masih 'i','d','s'. value 'b' (blob) masih belum tau cara menentukannya
    $bindValue = [];
    $query = '';
    if (($length = count($historyCode)) > 0) {
      $query = "( {$table}.id IN ( SELECT history.owner_id FROM history WHERE ";
      $query .= "(";
      for ($i = 0; $i < $length; $i++) {
        if (!(Code::where('name', $historyCode[$i])->first('id'))) return '';
        // $query .= "history.code = '{$historyCode[$i]}'";
        $query .= "history.code = {$vCode}";
        $bindValue[] = $historyCode[$i];
        $types .= 's'; // s karena semua code history berupa string
        if (isset($historyCode[$i + 1])) $query .= " OR ";
      }
      $query .= ")";
      $query .= " AND history.created_at = (SELECT MAX(history.created_at) FROM history WHERE history.owner_id = {$table}.id AND history.owner_class = ";
      $query .= $vCode . ")))"; 
      $bindValue[] = $classModel;
      $types .= "s";
    }
    return [(string)$query, (array)$bindValue, (string)$vCode ,(string) $types];
  }

  /**
   * save semua HISTORYModel yang masuk kedalam parameter fungsi
   * @return bool
   */
  public static function saveModel(array $HISTORYModels): bool
  {
    // $HISTORYModels = func_get_args();
    $created_at = now()->format('Y-m-d H:i:s'); // format sama dengan class Csdb timestamps (skrg tidak ada) yang bisa di cache
    $length = count($HISTORYModels);
    for ($i = 0; $i < $length; $i++) {
      $HISTORYModels[$i]->created_at = $created_at;
      if (!($HISTORYModels[$i]->save())) {
        for ($x = $i - 1; $x >= 0; $x--) {
          $HISTORYModels[$x]->delete();
          return false;
        }
      }
    }
    return true;
  }

  public static function revert_saveModel(array $HISTORYModels): bool
  {
    // $HISTORYModels = func_get_args();
    $length = count($HISTORYModels);
    $length = func_num_args();
    for ($i = 0; $i < $length; $i++) {
      if (!($HISTORYModels[$i]->delete())) {
        for ($x = $i - 1; $x >= 0; $x--) {
          $HISTORYModels[$x]->save();
          return false;
        }
      }
    }
    return true;
  }

  /**
   * @return {App\Models\Csdb\History}
   */
  public static function make_history(Code $CODEModel, string $description, string $owner_id, string $owner_class): History
  {
    $HISTORYModel = new self();
    $HISTORYModel->code = $CODEModel->name;
    $HISTORYModel->description = $description;
    $HISTORYModel->owner_id = $owner_id;
    $HISTORYModel->owner_class = $owner_class;
    $HISTORYModel->created_at = now()->format('Y-m-d H:i:s'); // format sama dengan class Csdb
    return $HISTORYModel;
  }

  private static function resolveIdAndOwnerClass(mixed $model)
  {
    
  }

  ############################# START of CSDB HISTORY FUNCTION #############################
  /**
   * Delete CSDB Object
   */
  public static function MAKE_CSDB_DELL_History(mixed $CSDBModel, string $description = '')
  {
    $CODEModel = Code::where('name', 'CSDB-DELL')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description;
    }
    return self::make_history($CODEModel, $description, $CSDBModel->id, get_class($CSDBModel));
  }

  /**
   * Permanent delete CSDB Object
   */
  public static function MAKE_CSDB_PDEL_History(mixed $CSDBModel, string $description = '')
  {
    $CODEModel = Code::where('name', 'CSDB-PDEL')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description;
    }
    return self::make_history($CODEModel, $description, $CSDBModel->id, get_class($CSDBModel));
  }

  /**
   * Create CSDB Object
   */
  public static function MAKE_CSDB_CRBT_History(mixed $CSDBModel, string $description = '')
  {
    $CODEModel = Code::where('name', 'CSDB-CRBT')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description;
    }
    return self::make_history($CODEModel, $description, $CSDBModel->id, get_class($CSDBModel));
  }

  /**
   * Update CSDB Object
   */
  public static function MAKE_CSDB_UPDT_History(mixed $CSDBModel, string $description = '')
  {
    $CODEModel = Code::where('name', 'CSDB-UPDT')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description .' by '.request()->user()->email;
    }
    return self::make_history($CODEModel, $description, $CSDBModel->id, get_class($CSDBModel));
  }

  /**
   * Update path CSDB Object
   */
  public static function MAKE_CSDB_PATH_History(mixed $CSDBModel, string $description = '')
  {
    $CODEModel = Code::where('name', 'CSDB-PATH')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description . "(" . $CSDBModel->path . ")";
    }
    return self::make_history($CODEModel, $description, $CSDBModel->id, get_class($CSDBModel));
  }

  /**
   * Update storage CSDB Object
   */
  public static function MAKE_CSDB_STRG_History(mixed $CSDBModel, string $description = '')
  {
    $CODEModel = Code::where('name', 'CSDB-STRG')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description . "(" . $CSDBModel->storage . ")";
    }
    return self::make_history($CODEModel, $description, $CSDBModel->id, get_class($CSDBModel));
  }

  /**
   * Restore storage CSDB Object
   */
  public static function MAKE_CSDB_RSTR_History(mixed $CSDBModel, string $description = '')
  {
    $CODEModel = Code::where('name', 'CSDB-RSTR')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description;
    }
    return self::make_history($CODEModel, $description, $CSDBModel->id, get_class($CSDBModel));
  }

  
  /**
   * CSDB Object has imported to requester storage
   */
  public static function MAKE_CSDB_IMPT_History(mixed $CSDBModel, string $description = '')
  {
    $CODEModel = Code::where('name', 'CSDB-IMPT')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description . "(" . request()->user()->email . ")";
    }
    return self::make_history($CODEModel, $description, $CSDBModel->id, get_class($CSDBModel));
  }

  ############################# END of CSDB HISTORY FUNCTION #############################
  ############################# START of USER HISTORY FUNCTION #############################
  /**
   * User create CSDB Object
   */
  public static function MAKE_USER_CRBT_History(User $USERModel, string $description = '', string $CSDBFilename = '')
  {
    $CODEModel = Code::where('name', 'USER-CRBT')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description . "(" . $CSDBFilename . ")";
    }
    return self::make_history($CODEModel, $description, $USERModel->id, get_class($USERModel));
  }

  /**
   * User create CSDB Object
   */
  public static function MAKE_USER_UPDT_History(User $USERModel, string $description = '', string $CSDBFilename = '')
  {
    $CODEModel = Code::where('name', 'USER-UPDT')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description . "(" . $CSDBFilename . ")";
    }
    return self::make_history($CODEModel, $description, $USERModel->id, get_class($USERModel));
  }

  /**
   * User DELL CSDB Object
   */
  public static function MAKE_USER_DELL_History(User $USERModel, string $description = '', string $CSDBFilename = '')
  {
    $CODEModel = Code::where('name', 'USER-DELL')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description . "(" . $CSDBFilename . ")";
    }
    return self::make_history($CODEModel, $description, $USERModel->id, get_class($USERModel));
  }

  /**
   * User permanent delete CSDB Object
   */
  public static function MAKE_USER_PDEL_History(User $USERModel, string $description = '', string $CSDBFilename = '')
  {
    $CODEModel = Code::where('name', 'USER-PDEL')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description . "(" . $CSDBFilename . ")";
    }
    return self::make_history($CODEModel, $description, $USERModel->id, get_class($USERModel));
  }

  /**
   * User update path CSDB Object
   */
  public static function MAKE_USER_PATH_History(User $USERModel, string $description = '', string $CSDBFilename = '')
  {
    $CODEModel = Code::where('name', 'USER-PATH')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description . "(" . $CSDBFilename . ")";
    }
    return self::make_history($CODEModel, $description, $USERModel->id, get_class($USERModel));
  }

  /**
   * User update storage CSDB Object
   */
  public static function MAKE_USER_STRG_History(User $USERModel, string $description = '', string $CSDBFilename = '')
  {
    $CODEModel = Code::where('name', 'USER-STRG')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description . "(" . $CSDBFilename . ")";
    }
    return self::make_history($CODEModel, $description, $USERModel->id, get_class($USERModel));
  }

  /**
   * User restore storage CSDB Object
   */
  public static function MAKE_USER_RSTR_History(User $USERModel, string $description = '', string $CSDBFilename = '')
  {
    $CODEModel = Code::where('name', 'USER-RSTR')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description . "(" . $CSDBFilename . ")";
    }
    return self::make_history($CODEModel, $description, $USERModel->id, get_class($USERModel));
  }

  /**
   * User import CSDB Object
   */
  public static function MAKE_USER_IMPT_History(User $USERModel, string $description = '', string $CSDBFilename = '')
  {
    $CODEModel = Code::where('name', 'USER-IMPT')->first(["id", "name", 'description']);
    if ($CODEModel) {
      if (!$description) $description = $CODEModel->description . "(" . $CSDBFilename . ")";
    }
    return self::make_history($CODEModel, $description, $USERModel->id, get_class($USERModel));
  }

  ############################# END of USER HISTORY FUNCTION #############################
}
