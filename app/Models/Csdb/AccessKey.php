<?php

namespace App\Models\Csdb;

use App\Models\Csdb;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Exceptions\HttpResponseException;

class AccessKey extends Model
{
  use HasFactory;

  /**
   * The table associated with the model.
   * @var string
   */
  protected $table = 'access_key';

  /**
   * Indicates if the modul should be timestamped
   * 
   * @var bool
   */
  public $timestamps = false;

  /**
   * Indicates if the model's ID is auto-incrementing.
   * @var bool
   */
  public $incrementing = true;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['csdb_id', 'user_id', 'key', 'abilities', 'expires_at'];

  /**
   * The attributes that should be hidden for serialization.
   * @var array<int, string>
   */
  protected $hidden = ['id', 'csdb_id', 'user_id', 'abilities', 'expires_at'];

  // /**
  //  * @return App\Models\Csdb
  //  */
  // public function resolveRouteBinding($value, $field = null)
  // {
  //   $ability = request()->route('ability');
  //   $date = \Carbon\Carbon::now();
  //   return $ability ? Csdb::with(['accessKeys' => fn (HasMany $AccessKeyModel) => $AccessKeyModel->where($field, self::decryptAccessKey(urldecode($value)))])->where('abilities', 'like', '%' . $ability . '%')->whereDate('expires_at', '>', $date)->firstOrFail() :
  //     Csdb::with(['accessKeys' => fn (HasMany $AccessKeyModel) => $AccessKeyModel->where($field, self::decryptAccessKey(urldecode($value)))])->orderBy('expires_at', 'desc')->whereDate('expires_at', '>', $date)->firstOrFail();
  // }

  /**
   * Set the model created_at touse current timezone.
   */
  protected function key(): Attribute
  {
    return Attribute::make(
      // set: fn (string $v) => self::encryptAccessKey($v),
      // get: fn (string $v) => self::decryptAccessKey($v),
      // get: fn (string $v) => \urlencode(self::encryptAccessKey($v)),
      get: function(string $v){
        $key = \urlencode(self::encryptAccessKey($v));
        while(str_contains(\urldecode($key),' ') || str_contains(\urldecode($key),'+')){
          $key = \urlencode(self::encryptAccessKey($v));
          // throw new \Error(str_contains(\urldecode($key),' ') ? 'true' : 'false' . ' => ' . $key);
        }
        return $key;
      }
    );
  }

  public function csdb(): BelongsTo
  {
    return $this->belongsTo(Csdb::class, 'csdb_id', 'id');
  }

  /**
   * src: https://stackoverflow.com/questions/16600708/how-do-you-encrypt-and-decrypt-a-php-string
   * return value must be stored to db
   * @param string $accessKey is provided by the client
   */
  public static function encryptAccessKey(string $accessKey)
  {
    // $accessKey = "9611222007552"; // from client
    // $cipher_method = 'aes-128-ctr';
    // $enc_key = openssl_digest(php_uname(), 'SHA256', TRUE);
    // // var_dump(php_uname());
    // $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
    // $crypted_key = openssl_encrypt($accessKey, $cipher_method, $enc_key, 0, $enc_iv) . "::" . bin2hex($enc_iv); // stored to db
    // return $crypted_key;
    $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-ctr'));
    return openssl_encrypt($accessKey, 'aes-128-ctr', openssl_digest(php_uname(), 'SHA256', TRUE), 0, $enc_iv) . "::" . bin2hex($enc_iv);
  }

  public static function decryptAccessKey(string $cryptedKey)
  {
    // list($cryptedKey, $enc_iv) = explode("::", $cryptedKey);
    // $cipher_method = 'aes-128-ctr';
    // $enc_key = openssl_digest(php_uname(), 'SHA256', TRUE);
    // $accessKey = openssl_decrypt($cryptedKey, $cipher_method, $enc_key, 0, hex2bin($enc_iv));
    // return $accessKey;
    try {
      list($cryptedKey, $enc_iv) = explode("::", $cryptedKey);
      return openssl_decrypt($cryptedKey, 'aes-128-ctr', openssl_digest(php_uname(), 'SHA256', TRUE), 0, hex2bin($enc_iv));
    } catch (\Throwable $th) {
      throw new HttpResponseException(response(["message" => "access_key is not valid."], 400));
    }
  }
}
