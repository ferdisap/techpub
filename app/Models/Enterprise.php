<?php

namespace App\Models;

use App\Casts\RemarksTes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Ptdi\Mpub\Main\Helper;

class Enterprise extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'code',
    'address',
    'remarks',
  ];

  /**
   * Indicates if the modul should be timestamped
   * 
   * @var bool
   */
  public $timestamps = false;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'enterprises';

  /**
   * The primary key associated with the table.
   *
   * @var string
   */
  protected $primaryKey = 'id';

  /**
   * The attributes that should be cast.
   * @var array
   */
  // protected $casts = [
    // 'address' => 'array', // tidak perlu kalau columnnya json
    // 'remarks' => 'array' // tidak perlu kalau columnnya json karna otomatis ke array saat 
  // ];

  /**
   * saat create
   * jika tidak kita masukkan valunya maka ini berjalan DAN fungsi :Attribute TIDAK berjalan
   * jika kita masukin valuenya walaupun NULL, maka ini TIDAK berjalan DAN fungsi :Attribute berjalan
   */
  protected $attributes = [
    'remarks' => "[]",
  ];

  protected $hidden = ['id', 'code_id'];

  protected $with = ['code'];

  /**
   * harus json string
   * set value akan menjadi json string curly atau json string array []
   * get value akan menjadi array
   * json di sini bentuk/schemanya sama seperti jika menggunakan CSDBStatic::simple_decode_element (harusnya)
   * CSDBStatic::simple_decode_element($DDNModel->csdb->CSDBObject->evaluate("//dispatchTo/dispatchAddress/address")[0], $d); $d = $d['address'];
   */
  protected function address(): Attribute
  {
    return Attribute::make(
      set: function ($v) {
        $v = is_array($v) ? $v :(
          Helper::isJsonString($v) ? json_decode($v, true) : [$v]
        ); // output array;
        
        $a = [
          "city" => $v['city'] ?? '',
          "country" => $v['country'] ?? '',
          'department' => $v['department'] ?? '',
          'street' => $v['street'] ?? '',
          'postOfficeBox' => $v['postOfficeBox'] ?? '',
          'postalZipCode' => $v['postalZipCode'] ?? '',
          'city' => $v['city'] ?? '',
          'country' => $v['country'] ?? '',
          'state' => $v['state'] ?? '',
          'province' => $v['province'] ?? '',
          'building' => $v['building'] ?? '',
          'room' => $v['room'] ?? '',
          'phoneNumber' => $v['phoneNumber'] ?? [],
          'faxNumber' => $v['faxNumber'] ?? [],
          'email' => $v['email'] ?? [],
          'internet' => $v['internet'] ?? [],
          'SITA' => $v['SITA'] ?? '',
        ];
        
        return json_encode($a);
      },
      get: fn($v) => json_decode($v, true),
    );
  }

  /**
   * harus json string
   * set value akan menjadi json string curly atau json string array []
   * get value akan menjadi array
   */
  protected function remarks(): Attribute
  {
    return Attribute::make(
      set: fn($v) => is_array($v) ? json_encode($v) :(
        $v && Helper::isJsonString($v) ? $v : json_encode($v ? [$v] : [])
      ),
      get: fn($v) => json_decode($v, true),
    );
  }

  public function code() :BelongsTo
  {
    return $this->belongsTo(Code::class);
  }
  
}
