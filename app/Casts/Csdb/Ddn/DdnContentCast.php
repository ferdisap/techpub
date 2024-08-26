<?php

namespace App\Casts\Csdb\Ddn;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Ptdi\Mpub\Main\Helper;

class DdnContentCast implements CastsAttributes
{
  /**
   * Cast the given value.
   * get value akan menjadi array
   * @param  array<string, mixed>  $attributes
   */
  public function get(Model $model, string $key, mixed $value, array $attributes): mixed
  {
    return json_decode($value);
  }

  /**
   * Prepare the given value for storage.
   * set value akan menjadi json string curly atau json string array []
   * @param  array<string, mixed>  $attributes
   */
  public function set(Model $model, string $key, mixed $v, array $attributes): mixed
  {
    return is_array($v) ? json_encode($v) :(
      $v && Helper::isJsonString($v) ? $v : json_encode($v ? [$v] : [])
    );
  }
}
