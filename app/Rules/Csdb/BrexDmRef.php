<?php

namespace App\Rules\Csdb;

use App\Models\Csdb;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Ptdi\Mpub\Main\CSDBStatic;

class BrexDmRef implements ValidationRule
{
  public function __construct(public bool $required = true)
  {
    
  }
  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if($this->required && !$value) {
      $fail("$attribute is required.");
      return;
    }

    if(substr($value,0,3) !== 'DMC') $fail('BREX filename must be an DMC.');
    if (count(explode("_", $value)) < 3) $fail("The {$attribute} must contain IssueInfo and Language.");
    $decode = CSDBStatic::decode_dmIdent($value);
    if ($decode and $decode['dmCode']['infoCode'] != '022') $fail("The {$attribute} infoCode must be '022'.");
    if($brex = Csdb::where('filename','filename')->first()){
      if(!str_contains($brex->available_storage, Auth::user()->storage)){
        $fail('The BREX are not available in your own storage.');
      }
      $fail('The BREX are not available in storage application.');
    }
  }
}
