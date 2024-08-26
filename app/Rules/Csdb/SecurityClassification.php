<?php

namespace App\Rules\Csdb;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SecurityClassification implements ValidationRule
{
  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    // if(strlen($value) != 2 && (int)$value) $fail("$value must be number in two digits.");

    // harus dua digit, number, dan <=5
    if(!(($value != '00') && (strlen($value) === 2) && (is_numeric($value))) && (((int) $value <= 5))) $fail("{$attribute} must be in two digits without number or none.");
    
  }
}
