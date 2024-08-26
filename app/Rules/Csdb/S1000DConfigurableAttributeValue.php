<?php

namespace App\Rules\Csdb;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class S1000DConfigurableAttributeValue implements ValidationRule
{
  protected $type = '';
  public function __construct(string $type)
  {
    $this->type = $type;
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if($value === '') return; // artinya tidak perlu di validasi

    // misal $value = 'cp01'; artinya type harus diset 'cp'
    $l = strlen($this->type);
    $a = substr($value, 0, $l); // cp
    $b = substr($value, $l); // 01/02/dst...
    
    // should be $a = true AND $b = true
    $a = ($a === $this->type);
    $b = (($b != '00') && (strlen($b) === 2) && (is_numeric($b)) && ((int) $b <= 99));

    if (!($a && $b)) $fail("{$attribute} value must be contain '{$this->type}' and suffixed by the two digit number less than 100");
  }
}
