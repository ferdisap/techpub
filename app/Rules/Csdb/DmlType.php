<?php

namespace App\Rules\Csdb;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DmlType implements ValidationRule
{
  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    // switch ini in-casesensitive. artinya 'p' dan 'P' itu sama
    switch($value){
      case 'c': return;
      case 'p': return;
      case 's': return;
      default: $fail("Comment type must be value of 'c','p',or 's'.");
    }
    dd($value);
  }
}
