<?php

namespace App\Rules\Csdb;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Language implements ValidationRule
{
  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if((strlen($value) !== 2) && ((strlen(preg_replace('/[0-9\W]+/m','',$value))) !== 2)) $fail("{$attribute} must be in two digits without number or none.");
  }
}
