<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EnterpriseCode extends Base implements ValidationRule
{
  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    // jika required dan value null
    if($this->required AND !$value){
      $fail("The {$attribute} is required.");
    }
    // jika tidak di required maka preg_match tetap dibutuhkan

    if($value){
      preg_match("/[A-Z0-9]{5}/",$value, $matched);
      if(empty($matched)){
        $fail("The {$attribute} must contain five digits alphanumeric or capital letter.");
      }
    }
  }
}
