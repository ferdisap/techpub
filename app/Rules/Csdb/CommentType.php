<?php

namespace App\Rules\Csdb;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CommentType implements ValidationRule
{

  public function __construct(
    public mixed $parentCommentFilename    
  ){

  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if($this->parentCommentFilename && substr($this->parentCommentFilename,0,3) != 'COM'){
      $fail("Cannot resolve parent comment filename.");
    }

    if(!$this->parentCommentFilename && $value !== 'q'){
      $fail("Comment type must be in 'q' if you are trying to make new comment.");
    }
    elseif($this->parentCommentFilename && !($value === 'i' || $value === 'r')){
      $fail("Comment type must be in 'i' or 'r' if you are trying to reply comment.");
    }

    // switch ini in-casesensitive. artinya 'p' dan 'P' itu sama
    switch($value){
      case 'q': return;
      case 'i': return;
      case 'r': return;
      default: $fail("Comment type must be value of 'q','i',or 'r'.");
    }
  }
}
