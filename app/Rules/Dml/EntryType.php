<?php

namespace App\Rules\Dml;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EntryType implements ValidationRule
{
  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $allowed = [
      "",
      "n",
      "c",
      "d",
      // "new",
      // "changed",
      // "deleted",
    ];
    if(!in_array($value, $allowed)){
      $allowed = array_map( fn($v) => $v == '' ? ("'no need to be fullfilled'") : $v,$allowed);
      $last = count($allowed) - 1;
      $allowed[$last] = 'or '. $allowed[$last];
      $fail("The {$attribute} must be " . join(', ', $allowed) . ".");
    }
  }
}
