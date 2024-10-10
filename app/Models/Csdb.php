<?php

namespace App\Models;

use App\Jobs\Csdb\DmcTableFiller;
use App\Jobs\Csdb\FillObjectTable;
use App\Models\Csdb\Comment;
use App\Models\Csdb\Ddn;
use App\Models\Csdb\Dmc;
use App\Models\Csdb\Dml;
use App\Models\Csdb\History;
use App\Models\Csdb\Pmc;
use Carbon\Carbon;
use DOMDocument;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\TryCatch;
use Ptdi\Mpub\CSDB as MpubCSDB;
use Ptdi\Mpub\ICNDocument;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\Helper as Helper;

// use Ptdi\Mpub\Pdf2\Applicability;

/**
 * yang dimaksud CSDBModel adalah instance class ini.
 * yangd dimaksud 'Csdb'(s) adalah instance class.
 * yang dimaksud CSDBObject atau CSDBMeta adalah instance class Dmc, Pmc, dll extends class ini.
 * yangd dimaksud 'Model' adalah instance class Dmc, Pmc, dll extends class ini.
 */
class Csdb extends Model
{
  use HasFactory;
  // HasUlids, 
  // Applicability;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'csdb';

  /**
   * The primary key associated with the table.
   *
   * @var string
   */
  protected $primaryKey = 'id';

  /**
   * Indicates if the model's ID is auto-incrementing.
   *
   * @var bool
   */
  public $incrementing = true;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  // protected $fillable = ['filename', 'path', 'available_storage','initiator_id', 'deleter_id', 'deleted_at'];
  // protected $fillable = ['filename', 'path', 'available_storage','initiator_id', 'deleted_at'];
  protected $fillable = ['filename', 'path', 'storage_id', 'initiator_id'];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  // protected $hidden = ['initiator_id', 'id'];
  protected $hidden = ['id', 'initiator_id', 'storage_id'];

  /**
   * The attributes that should be cast.
   * @var array
   */
  protected $casts = [
    'remarks' => 'array'
  ];

  /**
   * The model's default values for attributes.
   *
   * @var array
   */
  protected $attributes = [
    // 'deleter_id' => 0,
    // 'available_storage' => 'foo',
  ];

  /**
   * Indicates if the modul should be timestamped
   * 
   * @var bool
   */
  public $timestamps = false;

  protected $with = [];

  /**
   * digunakan untuk function getCsdb, getCsdbs, getObject, getObjects
   * jika null maka tidak pakai query storage_id, jika 0 maka pakai request()->user()->id
   */
  public static $storage_user_id = 0;

  /**
   * Set the model created_at touse current timezone.
   */
  protected function createdAt(): Attribute
  {
    return Attribute::make(
      // set: fn (string $v) => now()->toString(),
      set: fn (string $v) => now()->format('Y-m-d H:i:s'),
      // set: fn (string $v) => date('Y-m-d H:i:s', $v),
      // get: fn (string $v) => Carbon::parse($v)->timezone(7)->toString(),
    );
  }

  /**
   * Set the model updated_at touse current timezone.
   */
  protected function updatedAt(): Attribute
  {
    return Attribute::make(
      // set: fn (string $v) => now()->toString(),
      set: fn (string $v) => now()->format('Y-m-d H:i:s'),
      // set: fn (string $v) => date('Y-m-d H:i:s', $v),
      // get: fn (string $v) => Carbon::parse($v)->timezone(7)->toString()
      // get: fn (string $v) => 
      // get: fn (string $v) => Carbon::createFromFormat('D M d Y H:i:s O+', $v)->toString()
    );
  }

  /**
   * Set path tanpa slash "/" di end string
   * Get path dengan slash "/" di end string
   */
  protected function path(): Attribute
  {
    return Attribute::make(
      set: fn (string $v) => strtoupper(substr($v, -1, 1) === '/' ? rtrim($v, "/") : $v),
      // set: fn(string $v) => substr($v,-1,1) === '/' ? $v : $v . "/",
      // get: fn(string $v) => substr($v,-1,1) === '/' ? $v : $v . "/",
      get: fn (string $v) => strtoupper($v),
    );
    // dd(substr($str,-1,1 ));
  }

  /**
   * DEPRECIATED, diganti oleh method setProtected
   */
  public function hide(mixed $column)
  {
    if (is_array($column)) {
      foreach ($column as $col) {
        $this->hidden[] = $col;
      }
    } elseif ($column == false) {
      $this->hidden = [];
    } else {
      $this->hidden[] = $column;
    }
    $this->hidden = array_unique($this->hidden);
  }

  /**
   * relationship untuk csdb
   */
  public function initiator(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * relationship untuk object
   */
  public function csdb(): BelongsTo
  {
    return $this->belongsTo(Csdb::class, 'csdb_id', 'id');
  }

  /**
   * relationship untuk csdb
   * tidak bisa dipakai untuk eager loading, kecuali static attribute objectClass sudah di instantiate atau ada param/query filename
   */
  public string $objectClass;
  public function object(): hasOne
  {
    $filename = self::$objectClass ?? $this->filename ?? request()->route()->parameter('filename') ?? request()->get('filename');
    if ($filename) $class = self::getClassObjectByFilename($filename);
    else $class = self::class; // nanti jadinya null kalau pakai $class self
    return $this->hasOne($class);
  }

  /**
   * DEPRECATED
   * return many objects
   */
  public function objects(): HasOne
  {
    $type = substr($this->filename, 0, 3);
    $class = '';
    switch ($type) {
      case 'DML':
        $class = Dml::class;
        break;
      case 'COM':
        $class = Com::class;
        break;
      case 'DDN':
        $class = Ddn::class;
        break;
      case 'PMC':
        $class = Pmc::class;
        break;
      default:
        $class = Dmc::class;
        break;
    }
    return $this->hasOne($class, 'filename', 'filename');
  }

  /**
   * DEPRECATED
   * relationship untuk csdb
   */
  public function storager(): BelongsTo
  {
    return $this->belongsTo(User::class, 'storage_id', 'id');
  }

  /**
   * relationship untuk csdb
   */
  public function owner(): BelongsTo
  {
    return $this->belongsTo(User::class, 'storage_id', 'id');
  }

  /**
   * DEPRECIATED, diganti histories
   * get models of all history that sorted by first to end
   */
  public function history(): MorphMany
  {
    return $this->morphMany(History::class, 'owner', 'owner_class'); //'owner' itu nanti dirender menjadi 'owner_id' 
  }
  public function histories(): MorphMany
  {
    return $this->morphMany(History::class, 'owner', 'owner_class'); //'owner' itu nanti dirender menjadi 'owner_id'
  }
  public function attachHistories(int $paginate, $type = '')
  {
    switch ($type) {
      case 'simple':
        $this->histories = History::getHistories($this)->orderByDesc('created_at')->simplePaginate($paginate);
        break;
      case 'cursor':
        $this->histories = History::getHistories($this)->orderByDesc('created_at')->cursorPaginate($paginate);
        break;
      default:
        $this->histories = History::getHistories($this)->orderByDesc('created_at')->paginate($paginate);
        break;
    }
  }

  /**
   * get the last history
   * ini digunakan untuk model csdb, bukan object
   */
  public function lastHistory(): MorphOne
  {
    return $this->morphOne(History::class, 'owner', 'owner_class')->latestOfMany('created_at');
  }

  /**
   * @param {string} $filename object csdb 
   * @param {array} $history where contains ['code' => [], 'exception' => []]
   * tinggal di get() saat selanjutnya
   */
  public static function getCsdb(string $filename, array $historyCode = [])
  {
    $CSDBModel = new self();

    $table = $CSDBModel->getTable();
    $class = self::class;

    // filter by filename
    $CSDBModel = $CSDBModel->whereRaw('(filename = ? )', [$filename]);

    // filter by storage
    // $CSDBModel = $CSDBModel->whereRaw('(storage_id = ? )',[request()->user()->id]);
    if (self::$storage_user_id === 0) ($CSDBModel = $CSDBModel->whereRaw('(storage_id = ? )', [request()->user()->id]));
    // elseif (self::$storage_user_id) ($CSDBModel = $CSDBModel->whereRaw('(storage_id = ? )', [self::$storage_user_id]));

    // filter by last history
    if (!empty($historyCode)) {
      if (isset($historyCode['code'])) {
        $queryWhereRawHistory = History::generateWhereRawQueryString($historyCode['code'], $class, $table);
        $CSDBModel = $CSDBModel->whereRaw($queryWhereRawHistory[0], $queryWhereRawHistory[1]);
      } elseif (isset($historyCode['exception'])) {
        $queryWhereRawHistory = History::generateWhereRawQueryString_historyException($historyCode['exception'], $class, $table);
        $CSDBModel = $CSDBModel->whereRaw($queryWhereRawHistory[0], $queryWhereRawHistory[1]);
      }
    }
    return $CSDBModel;
  }

  /**
   * @param {string} $filename object csdb 
   * @param {array} $history where contains ['code' => [], 'exception' => []]
   * tinggal di get() saat selanjutnya
   */
  public static function getCsdbs(array $historyCode = [])
  {
    $CSDBModels = new self();

    $table = $CSDBModels->getTable();
    $class = self::class;

    // filter by storage
    // $CSDBModels = $CSDBModels->whereRaw("({$table}.storage_id = ? )",[request()->user()->id]);
    if (self::$storage_user_id === 0) ($CSDBModels = $CSDBModels->whereRaw("({$table}.storage_id = ? )", [request()->user()->id]));
    elseif (self::$storage_user_id) ($CSDBModels = $CSDBModels->whereRaw("({$table}.storage_id = ? )", [self::$storage_user_id]));

    // filter by last history
    if (!empty($historyCode)) {
      if (isset($historyCode['code'])) {
        $queryWhereRawHistory = History::generateWhereRawQueryString($historyCode['code'], $class, $table);
        $CSDBModels = $CSDBModels->whereRaw($queryWhereRawHistory[0], $queryWhereRawHistory[1]);
      } elseif (isset($historyCode['exception'])) {
        $queryWhereRawHistory = History::generateWhereRawQueryString_historyException($historyCode['exception'], $class, $table);
        $CSDBModels = $CSDBModels->whereRaw($queryWhereRawHistory[0], $queryWhereRawHistory[1]);
      }
    }
    return $CSDBModels;
  }

  /**
   * @param {string} $filename object csdb 
   * @param {array} $history where contains ['code' => [], 'exception' => []]
   */
  public static function getObject(string $filename, array $historyCode = [])
  {
    $eloquentClassModel = self::getClassObjectByFilename($filename);
    if(!$eloquentClassModel) return self::getCsdb($filename, $historyCode);
    $OBJECTModel = new $eloquentClassModel();
    $OBJECTModel = $OBJECTModel->with(['csdb'])->whereHas('csdb', function (Builder $query) use ($historyCode, $filename) {
      $query->where('filename', $filename);
      // $query->where('storage_id', self::$storage_user_id ?? request()->user()->id);
      if (self::$storage_user_id === 0) ($query->where('storage_id', request()->user()->id));
      elseif (self::$storage_user_id) ($query->where('storage_id', self::$storage_user_id));
      if (!empty($historyCode)) {
        if (isset($historyCode['code'])) {
          $queryWhereRawHistory = History::generateWhereRawQueryString($historyCode['code'], Csdb::class, env('DB_TABLE_CSDB', 'csdb'));
          $query->whereRaw($queryWhereRawHistory[0], $queryWhereRawHistory[1]);
        } elseif (isset($historyCode['exception'])) {
          $queryWhereRawHistory = History::generateWhereRawQueryString_historyException($historyCode['exception'], Csdb::class, env('DB_TABLE_CSDB', 'csdb'));
          $query->whereRaw($queryWhereRawHistory[0], $queryWhereRawHistory[1]);
        }
      }
    });
    return $OBJECTModel;
  }

  /**
   * @param {string} $filename object csdb 
   * @param {array} $history where contains ['code' => [], 'exception' => []]
   */
  public static function getObjects(string $eloquentClassModel, array $historyCode = [])
  {
    $eloquentClassModel = new $eloquentClassModel;
    if(!$eloquentClassModel) return self::getCsdbs($historyCode);
    $OBJECTModels = new $eloquentClassModel();
    $OBJECTModels = $OBJECTModels->with(['csdb'])->whereHas('csdb', function (Builder $query) use ($historyCode) {
      // $query->where('storage_id', self::$storage_user_id ?? request()->user()->id);
      if (self::$storage_user_id === 0) ($query->where('storage_id', request()->user()->id));
      elseif (self::$storage_user_id) ($query->where('storage_id', self::$storage_user_id));

      if (!empty($historyCode)) {
        if (isset($historyCode['code'])) {
          $queryWhereRawHistory = History::generateWhereRawQueryString($historyCode['code'], Csdb::class, env('DB_TABLE_CSDB', 'csdb'));
          $query->whereRaw($queryWhereRawHistory[0], $queryWhereRawHistory[1]);
        } elseif (isset($historyCode['exception'])) {
          $queryWhereRawHistory = History::generateWhereRawQueryString_historyException($historyCode['exception'], Csdb::class, env('DB_TABLE_CSDB', 'csdb'));
          $query->whereRaw($queryWhereRawHistory[0], $queryWhereRawHistory[1]);
        }
      }
    });
    return $OBJECTModels;
  }

  /**
   * @param {string} $filename object csdb 
   * @param {array} $history where contains ['code' => [], 'exception' => []]
   */
  public static function searchCsdbs(mixed $keywords, $historyCode = [])
  {
    $CSDBModels = new self();

    $table = $CSDBModels->getTable();
    $class = self::class;

    $query = Helper::generateWhereRawQueryString($keywords, $CSDBModels->getTable());
    if ($query[0]) {
      $CSDBModels = $CSDBModels->whereRaw($query[0], $query[1]);
    }

    // filter by last history
    if (!empty($historyCode)) {
      if (isset($historyCode['code'])) {
        $queryWhereRawHistory = History::generateWhereRawQueryString($historyCode['code'],  $class, $table);
        $CSDBModels = $CSDBModels->whereRaw($queryWhereRawHistory[0], $queryWhereRawHistory[1]);
      } elseif (isset($historyCode['exception'])) {
        $queryWhereRawHistory = History::generateWhereRawQueryString_historyException($historyCode['exception'], $class, $table);
        $CSDBModels = $CSDBModels->whereRaw($queryWhereRawHistory[0], $queryWhereRawHistory[1]);
      }
    }

    return $CSDBModels;
  }

  /**
   * DEPRECIATED, karena project name sudah tidak ada di database
   * Get the post that owns the comment.
   */
  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class, 'project_name');
  }



  ###### CUSTOM #######
  public CSDBObject $CSDBObject;

  public function __construct()
  {
    $this->usesUniqueIds = true; // agar sama jika pakai/tanpa __construct
    $this->CSDBObject = new CSDBObject("5.0");
  }

  public function loadCSDBObject(): bool
  {
    $storage = $this->owner->storage ?? $this->csdb->owner->storage;
    $filename = $this->filename ?? $this->csdb->filename;
    $this->CSDBObject->load(CSDB_STORAGE_PATH . DIRECTORY_SEPARATOR . $storage . DIRECTORY_SEPARATOR . $filename);
    return $this->CSDBObject ? true : false;
  }

  /**
   * DEPRECIATED. Dipindah ke Mpub CSDBObject class
   * helper function untuk crew.xsl
   * ini tidak bisa di pindah karena bukan static method
   * * sepertinya bisa dijadikan static, sehingga fungsinya lebih baik ditaruh di CsdbModel saja
   */
  public function setLastPositionCrewDrillStep(int $num)
  {
    $this->lastPositionCrewDrillStep = $num;
  }

  /**
   * DEPRECIATED. Dipindah ke Mpub CSDBObject class
   * helper function untuk crew.xsl
   * ini tidak bisa di pindah karena bukan static method
   * sepertinya bisa dijadikan static, sehingga fungsinya lebih baik ditaruh di CsdbModel saja
   */
  public function getLastPositionCrewDrillStep()
  {
    return $this->lastPositionCrewDrillStep ?? 0;
  }

  /**
   * DEPRECIATED. diganti oleh $CSDBObject
   */
  public \DOMDocument $DOMDocument;

  /**
   * DEPRECIATED. karena fungsi transform_to_xml dipindah ke Mpub CSDBObject class
   */
  public string $output = 'html';

  /**
   * DEPRECIATED. Tidak akan dipakai lagi
   */
  public string $repoName = '';

  /**
   * DEPRECIATED. Tidak akan dipakai lagi
   */
  public string $objectpath = '';

  /**
   * DEPRECIATED. TIdak akan dipakai lagi
   */
  public string $absolute_objectpath = '';

  /**
   * DEPRECIATED. Akan ditaruh di Mpub CSDBObject class
   */
  public function transform_to_xml($path_xsl, $filename_xsl = '', $configuration = '')
  {
    if (!$filename_xsl) {
      $type = $this->DOMDocument->documentElement->nodeName;
      $filename_xsl = "{$type}.xsl";
    }

    $xsl = MpubCSDB::importDocument($path_xsl . "/", $filename_xsl);

    $xsltproc = new \XSLTProcessor();
    $xsltproc->importStylesheet($xsl);
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => MpubCSDB::class . "::$name", get_class_methods(MpubCSDB::class)))());
    $xsltproc->registerPHPFunctions((fn () => array_map(fn ($name) => self::class . "::$name", get_class_methods(self::class)))());
    // $xsltproc->registerPHPFunctions([self::class . "::getLastPositionCrewDrillStep", self::class . "::setLastPositionCrewDrillStep"]);
    $xsltproc->registerPHPFunctions();

    // $xsltproc->setParameter('', 'repoName', $this->repoName);
    // $xsltproc->setParameter('', 'objectpath', $this->objectpath);
    // $xsltproc->setParameter('', 'absolute_objectpath', $this->absolute_objectpath);
    // $schemaFilename = MpubCSDB::getSchemaUsed($this->DOMDocument,'filename');
    // $xsltproc->setParameter('', 'schema', $schemaFilename);
    $xsltproc->setParameter('', 'configuration', $configuration);
    if ($this->filename) {
      $decode_ident = Helper::decode_ident($this->filename);
      $object_code = $decode_ident[array_key_first($decode_ident)];
      $object_code = array_filter($object_code, fn ($v) => $v);
      $object_code = join("-", $object_code);
      $xsltproc->setParameter('', 'object_code', $object_code);
    }
    // $xsltproc->setParameter('', 'icnPath', '/images/'); // nanti diganti '/csdb/'
    $xsltproc->setParameter('', 'icnPath', '/csdb/icn'); // nanti diganti '/csdb/'

    if ($this->output == 'html') {
      $transformed = str_replace("#ln;", '<br/>', $xsltproc->transformToXml($this->DOMDocument));
    } else {
      $transformed = str_replace("#ln;", chr(10), $xsltproc->transformToXml($this->DOMDocument));
    }

    $transformed = str_replace("\n", '', $transformed);

    $transformed = preg_replace("/\s+/m", ' ', $transformed);
    $transformed = preg_replace("/v-on_/m", 'v-on:', $transformed); // nanti ini dihapus. Setiap xml akan ditambahkan namespace xmlns:v-bind, xmlns:v-on, dll 
    $transformed = preg_replace('/xmlns:[\w\-=":\/\\\\._]+/m', '', $transformed); // untuk menghilangkan attribute xmlns

    return $transformed;
  }

  /**
   * jika $column tersedia di database 'csdb' atau 'csdb_deleted'.
   * jika ada dua column yang sama, tetap akan diambil column pertama yang found.
   * @return string 
   */
  public static function columnNameMatching(string $column, string $dbName = '')
  {
    if (!$dbName) {
      $found = array_unique(array_merge(DB::getSchemaBuilder()->getColumnListing($dbName), DB::getSchemaBuilder()->getColumnListing('csdb_deleted')));
    } else {
      $found = DB::getSchemaBuilder()->getColumnListing($dbName);
    }
    $found = array_filter($found, function ($v) use ($column) {
      $v = str_contains($v, $column) ? $column : (str_contains($column, $v) ? $column : false
      );
      return $v;
    });
    $found = !empty($found) ? $found[array_key_first($found)] : '';
    return $found;
  }

  /**
   * sudah termasuk revert save
   * save file dulu, kemudian model
   * sudah bisa save file ICN. Mungkin namanya tidak relevan lagi, jadi nanti didepreciated
   * saat membuat history, isi $historyStatic function adalah [ [(string)method, (array)params] ],  index[0] = string methodName, index1 = array params
   * jika ada parameter method history yang membutuhkan instance class ini atau turunan class ini (Dmc, Pmc, dll) maka isi param nya dengan namespace class
   * @param {string} $storageName
   * @param {array} $historyStaticFunction
   * @return bool
   */
  public function saveDOMandModel(string $storageName = '', $historyStaticFunction = [], $config = ['connection' => 'sync', 'mailNotification' => true])
  {
    if (!$storageName) {
      if ($name = User::find($this->initiator_id)) $storageName = $name->storage;
      else return false;
    }
    $filename = $this->filename ?? $this->csdb->filename;
    $save_file = fn () => Storage::disk('csdb')->put($storageName . "/" . $filename, ($this->CSDBObject->document instanceof \DOMDocument ? $this->CSDBObject->document->saveXML() : $this->CSDBObject->document->getFile()));
    $revert_save_file =
      fn () => ($fileContents = Storage::disk('csdb')->get($storageName . "/" . $filename))
        ? Storage::disk('csdb')->put($storageName . "/" . $filename, $fileContents)
        : Storage::disk('csdb')->delete($storageName . "/" . $filename);
    if ($save_file()) {
      if ($this->save()) {
        // create history
        $HISTORYModels = [];
        foreach ($historyStaticFunction as $history) {
          if ($history instanceof History) {
            $HISTORYModels[] = $history;
          } else {
            $method = $history[0];
            $params = $history[1];
            foreach ($params as $i => $p) {
              if ($p === self::class) {
                $params[$i] = $this;
              };
            }
            $HISTORYModels[] = call_user_func_array(array(History::class, $method), $params);
          }
        }
        if (!(History::saveModel($HISTORYModels))) {
          $this->delete();
          $revert_save_file();
          return false;
        }
        
        // fill object dilakukan oleh worker. @dispatchSync return 404 Not Found
        // $fillObjectTableConfig = ['connection' => 'sync', 'mailNotification' => true];
        $fillObjectTableConfig = [];
        foreach ($config as $key => $value) {
          $fillObjectTableConfig[$key] = $value;
        }
        if(isset($fillObjectTableConfig['connection'])){
          // dd($fillObjectTableConfig['connection']);
          if(get_class($this) === Csdb::class) FillObjectTable::dispatch(request()->user(), $this, $fillObjectTableConfig['mailNotification'] ?? false)->onConnection($fillObjectTableConfig['connection']); // using queue
          else FillObjectTable::dispatch(request()->user(), $this->csdb, $fillObjectTableConfig['mailNotification'] ?? false)->onConnection($fillObjectTableConfig['connection']);
        }
        
        return true;
      }
      $revert_save_file();
      return false;
    }
    return false;
  }

  /**
   * DEPRECIATED, karena tidak ada lagi column 'available_storage' dan column 'filename' sudah tidak lagi uniue
   */
  public function appendAvailableStorage(string $storage)
  {
    if (!($this->available_storage)) $this->available_storage = $storage;
    elseif (!str_contains($this->available_storage, $storage)) $this->available_storage .= "," . $storage;
  }

  /**
   * awalnya diperlukan untuk Dmc@fillTable
   */
  public function setProtected(array $props)
  {
    foreach ($props as $prop => $v) {
      $this->$prop = $v;
    }
  }

  public function getProtected(string $props)
  {
    return $this->$props;
  }

  /**
   * masih terbatas pada object yang ada classnya masing2 seperti Dmc, Pmc, Ddn, Comment, Dml. Tapi belum untuk Icn
   */
  public static function getModelClass(string $model)
  {
    $class = "\App\Models\Csdb\\" . $model;
    if (class_exists($class)) {
      $self = new $class;
      $self->setProtected([
        'table' => $self->getProtected('table') ?? [],
        'fillable' => $self->getProtected('fillable') ?? [],
        'casts' => $self->getProtected('casts') ?? [],
        'attributes' => $self->getProtected('attributes') ?? [],
        'timestamps' => $self->getProtected('timestamps') ?? false,
      ]);
      return $self;
    }
    return new self();
  }

  /** @return {string} of class object by type filename */
  public static function getClassObjectByFilename(string $filename)
  {
    $type = substr($filename, 0, 3);
    $class = "\App\Models\Csdb\\";
    switch ($type) {
      case 'DMC':
        $class .= 'Dmc'; break;
      case 'PMC':
        $class .= 'Pmc'; break;
      case 'DML':
        $class .= 'Dml'; break;
      case 'DDN':
        $class .= 'Ddn'; break;
      case 'COM':
        $class .= 'Comment'; break;
      default: return '';
    }
    return $class;
  }

  /**
   * DEPRECIATED Tidak diperlukan lagi karena sudah di instance di class ini @getModelClass
   */
  // public static function instanceModel()
  // {
  //   $self = new self();
  //   $self->setProtected([
  //     'table' => $self->getProtected('table') ?? [],
  //     'fillable' => $self->getProtected('fillable') ?? [],
  //     'casts' => $self->getProtected('casts') ?? [],
  //     'attributes' => $self->getProtected('attributes') ?? [],
  //     'timestamps' => $self->getProtected('timestamps') ?? false,
  //   ]);
  //   return $self;
  // }
































  // ##################### DEPRECIATED below #####################

  /**
   * DEPRECIATED karena sudah beda schema database, tidak ada lagi remarks
   * syaratnya harus manggil id agar bisa di save. Sengaja tidak dibuat manual agar tidak asal isi
   * biasanya, securityClassification, stage, crud
   * @return void
   */
  public bool $direct_save = true;
  public function setRemarks($key, $value = '')
  {
    $remarks = $this->remarks;
    $values = $remarks[$key] ?? [];
    switch ($key) {
      case 'searchkey':
        array_unshift($values, $value);
        if (count($values) >= 5) array_pop($values);
        $values = array_unique($values);
        break;
      case 'title':
        $values = $this->setRemarks_title($value);
        break;
      case 'remarks':
        $values = $this->setRemarks_remarks($value);
        break;
      case 'ident':
        $values = $this->setRemarks_ident($value);
        break;
      case 'status':
        $values = $this->setRemarks_status($value);
        break;
      case 'history':
        $values = $this->setRemarks_history($value);
        break;
      default:
        $values = $value;
        break;
    }
    $remarks[$key] = $values;
    $this->remarks = $remarks;

    if ($this->direct_save) {
      $this->save();
    }
  }

  private function setRemarks_history($value)
  {
    $history = $this->remarks['history'] ?? [];
    if (!$value) return $history;
    array_push($history, $value);
    return $history;
  }

  private function setRemarks_ident()
  {
    $ident = (CSDBStatic::decode_ident($this->filename));
    unset($ident['xml_string']);
    return $ident;
  }

  /**
   * hanya untuk document instanceof \DOMDocument
   */
  private function setRemarks_status()
  {
    // $brex = ($this->CSDBObject->getBrexDm());
    // dd($brex->getFilename());
    $status = [
      'securityClassification' => $this->CSDBObject->getSC('text'),
      'brexDmRef' => $this->CSDBObject->getBrexDm()->getFilename(),
    ];
    $doctypeName = $this->CSDBObject->document->doctype->nodeName;
    if ($doctypeName === 'comment') {
      $status['commentPriority'] = '';
    } elseif ($doctypeName === 'dmodule' or $doctypeName === 'pm') {
      $status['qualityAssurance'] = $this->CSDBObject->getQA();
    }
    return $status;
  }

  /** 
   * DEPRECIATED. diganti ke setRemarks_status
   * untuk set remarks sesuai xpath //identAndStatusSection/descendant::remarks/simplePara
   * @param mixed $value bisa berupa string, atau DOM Document
   * @return string 
   * */
  private function setRemarks_remarks($value = '')
  {
    $remarks_string = [];
    if ($value instanceof \DOMDocument) {
      $domXpath = new \DOMXPath($value);
    } else {
      $domXpath = new \DOMXPath($this->CSDBObject->document);
    }
    $simpleParas = $domXpath->evaluate('//identAndStatusSection/descendant::remarks/simplePara');
    foreach ($simpleParas as $key => $simplePara) {
      $remarks_string[] = $simplePara->textContent;
    }
    $remarks_string = join(PHP_EOL, $remarks_string);
    return !empty($remarks_string) ? $remarks_string : '';
  }


  /**
   * @return string
   */
  private function setRemarks_title($dom = '')
  {
    if (!$dom) {
      $dom = MpubCSDB::importDocument(storage_path('csdb'), $this->filename);
    }
    if ($dom instanceof ICNDocument) {
      $imfFilename = MpubCSDB::detectIMF(storage_path('csdb'), $dom->getFilename());
      $dom = MpubCSDB::importDocument(storage_path('csdb'), $imfFilename);
      if (!$dom) return '';
    }
    return MpubCSDB::resolve_DocTitle($dom);
  }

  /**
   * DEPRECIATED, diganti dengan saveDOMandModel
   * sudah termasuk revert save
   * save file dulu, kemudian model
   * sudah bisa save file ICN. Mungkin namanya tidak relevan lagi, jadi nanti didepreciated
   */
  public function saveModelAndDOM()
  {
    $fileContents = Storage::disk('csdb')->get($this->filename);
    $save_file = fn () => Storage::disk('csdb')->put($this->filename, ($this->CSDBObject->document instanceof \DOMDocument ? $this->CSDBObject->document->saveXML() : $this->CSDBObject->document->getFile()));
    $revert_save_file =
      fn () => $fileContents
        ? Storage::disk('csdb')->put($this->filename, $fileContents)
        : Storage::disk('csdb')->delete($this->filename);
    if ($save_file()) {
      if ($this->CSDBObject->document instanceof \DOMDocument) {
        $this->setRemarks('ident');
        $this->setRemarks('status');
        if (!$csdbobject) {
          $revert_save_file();
          return false;
        }
      }
      if ($this->save()) return true;
      else {
        $revert_save_file();
        return false;
      }
    }
    return false;
  }
  /**
   * untuk menambah namespace pada DOMDocument xsl
   */
  // private function addVueNamespace(\DOMDocument $doc)
  // {
  //   $ns = ['v-bind','v-on'];
  //   $root = $doc->firstElementChild;
  //   // xmlns:v="https://vuejs.org"
  //   foreach ($ns as $namespace) {
  //     $root->setAttribute("xmlns:{$namespace}", "https://vuejs.org/{$namespace}");
  //   }
  //   return $doc;
  // }

  /**
   * akan mengubah URI nya dari file:...../csdb/DMC-aaaa.xml menjadi file:...../csdb/
   */
  public function showCGMArkElement(): void
  {
    // dd($this->CSDBObject->document->documentElement->getAttributeNS('noNamespaceSchemaLocation', 'http://www.w3.org/2001/XMLSchema-instance'));
    if (str_contains($this->CSDBObject->document->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'noNamespaceSchemaLocation'), 'crew.xsd')) return;
    $xsltString = <<<XSLT
    <?xml version="1.0" encoding="UTF-8"?>
    <xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
      
      <xsl:output method="xml" omit-xml-declaration="yes"/>

      <xsl:template match="@* | node()">
        <xsl:copy>
          <xsl:apply-templates select="@* | node()"/>
        </xsl:copy>
      </xsl:template>

      <xsl:template match="//*[@changeMark = '1']">
        <__cgmark>
          <xsl:copy-of select="."/>
        </__cgmark>
      </xsl:template>
    </xsl:transform>
    XSLT;

    $xslDoc = new DOMDocument();
    $xslDoc->loadXML($xsltString);

    $xsltProc = new \XSLTProcessor();
    $xsltProc->importStylesheet($xslDoc);

    $newDoc = $xsltProc->transformToDoc($this->CSDBObject->document->cloneNode(true)); // di clone agar DOCTYPE tidak hilang, // baseURInya kosong
    $root = $newDoc->documentElement->cloneNode(true);
    $importRoot = $this->CSDBObject->document->importNode($root, true);
    $this->CSDBObject->document->documentElement->replaceWith($importRoot);
  }

  public function hideCGMArkElement(): void
  {
    $xsltString = <<<XSLT
    <?xml version="1.0" encoding="UTF-8"?>
    <xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
      
      <xsl:output method="xml" omit-xml-declaration="yes"/>

      <xsl:template match="@* | node()">
        <xsl:copy>
          <xsl:apply-templates select="@* | node()"/>
        </xsl:copy>
      </xsl:template>

      <xsl:template match="cgmark">
        <xsl:copy-of select="child::*"/>
      </xsl:template>
    </xsl:transform>
    XSLT;

    $xslDoc = new DOMDocument();
    $xslDoc->loadXML($xsltString);

    $xsltProc = new \XSLTProcessor();
    $xsltProc->importStylesheet($xslDoc);

    $newDoc = $xsltProc->transformToDoc($this->CSDBObject->document->cloneNode(true)); // di clone agar DOCTYPE tidak hilang, // baseURInya kosong
    $root = $newDoc->documentElement->cloneNode(true);
    $importRoot = $this->CSDBObject->document->importNode($root, true);
    $this->CSDBObject->document->documentElement->replaceWith($importRoot);
    return;
  }
}
