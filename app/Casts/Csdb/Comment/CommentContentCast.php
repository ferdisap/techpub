<?php

namespace App\Casts\Csdb\Comment;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CommentContentCast implements CastsAttributes
{
  /**
   * Cast the given value.
   *
   * @param  array<string, mixed>  $attributes
   */
  public function get(Model $model, string $key, mixed $value, array $attributes): mixed
  {
    return explode('\n',$value);
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
