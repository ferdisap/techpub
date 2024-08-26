<?php

namespace App\Rules;

class Base
{
  public bool $required;
  
  public function __construct($required)
  {
    $this->required = $required;
  }
}
