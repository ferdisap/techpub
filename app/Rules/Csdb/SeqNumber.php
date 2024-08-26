<?php

namespace App\Rules\Csdb;

use App\Models\Csdb;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SeqNumber implements ValidationRule
{
  public function __construct(
    public bool $required = true, 
    public string $docType,
    public bool $unique = true,
  ){}
  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if($this->unique){
      $CSDBModel = new Csdb();
      switch ($this->docType) {
        case 'ddn':
          $CSDBModel = $CSDBModel->where('filename', 'like', 'DDN-%')
                           ->where('filename', 'like', "%-${value}.xml")
                           ->first();
          if($CSDBModel){
            $fail("Cannot make DDN due to unique filename constraint.");
            return;
          }
          break;        
        case 'comment':
          $CSDBModel = $CSDBModel->where('filename', 'like', 'COM-%')
                           ->where('filename', 'like', "%-${value}\_%")
                           ->first();
          if($CSDBModel){
            $fail("Cannot make COM due to unique filename constraint.");
            return;
          }
          break;        
      }
    }

    if((strlen($value) !== 5) && ((strlen(preg_replace('/[0-9]+/m','',$value))) > 0)) $fail("{$attribute} must contain five numeric characters.");
  }
}
