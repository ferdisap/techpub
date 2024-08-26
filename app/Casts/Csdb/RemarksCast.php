<?php

namespace App\Casts\Csdb;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class RemarksCast implements CastsAttributes
{
  /**
   * Cast the given value.
   *
   * @param  array<string, mixed>  $attributes
   */
  public function get(Model $model, string $key, mixed $value, array $attributes): mixed
  {
    return $value;
  }

  /**
   * Prepare the given value for storage.
   *
   * @param  array<string, mixed>  $attributes
   */
  public function set(Model $model, string $key, mixed $value, array $attributes): mixed
  {
    return $value;
  }
}
