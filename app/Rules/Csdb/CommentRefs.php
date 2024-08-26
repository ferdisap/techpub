<?php

namespace App\Rules\Csdb;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CommentRefs implements ValidationRule
{
  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if (!(is_array($value))) {
      $fail("{$attribute} must be in array");
      return;
    };
    $type = '';
    array_walk($value, function($v) use(&$type, $fail, $attribute) {
      if(!$type) $type = substr($v,0,3);
      else {
        if($type !== substr($v, 0,3)) $fail("{$attribute} must contain one type of CSDB object.");
      }
    });
    if(!$type) $fail("{$attribute} must have valid CSDB object name.");
  }
}
