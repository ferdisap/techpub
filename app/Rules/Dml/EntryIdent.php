<?php

namespace App\Rules\Dml;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Ptdi\Mpub\Helper;
use Ptdi\Mpub\Main\CSDBStatic;

class EntryIdent implements ValidationRule
{
  public $filename;

  public function __construct($filename)
  {
    $this->filename = $filename;
  }

  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $entryIdent, Closure $fail): void
  {
    // validasi pakai decode_ident
    $ident = CSDBStatic::decode_ident($entryIdent);
    if(empty($ident)) {
      $fail("{$entryIdent} cannot be decoded.");
      return;
    };

    return;

    // sementara ini validasi entry ident sampai disini saja, 16 juli 2024;
    $filename = $this->filename;
    // jika entryIdent == 'DML/CSL' maka tidak perlu ada language di namanya
    // jika filename yang diupdate == 'CSL' maka harus denga issueInfo dan language nya
    $isCSL = substr($filename, 0, 3) == 'CSL' ? true : false;
    // kalau entryIdent DML/CSL
    // dan (kalau ini file ini CSL, count_ident harus terdiri dari code, issueInfo (2) || kalau file ini DML, count ident hanya terdiri dari code saja (1))
    if (in_array($ident['prefix'], ['DML-', 'CSL-'])) {
      $isexist_issueInfo = ($ident['issueInfo']['issueNumber'] AND $ident['issueInfo']['inWork']) ? true : false;
      if(($isCSL AND !$isexist_issueInfo) OR (!$isCSL AND $isexist_issueInfo)){
        $fail("{$entryIdent} is wrong rule.");
      }
    } else {
      // kalau entryIdent bukan DML/CSL (DMC, PMC, etc)
      // dan (kalau ini file ini CSL, count_ident harus terdiri dari code, issueInfo, language (3) || kalau file ini DML, count ident hanya terdiri dari code saja (1))
      $isexist_issueInfo = ($ident['issueInfo']['issueNumber'] AND $ident['issueInfo']['inWork']) ? true : false;
      $isexist_language =  ($ident['language']['languageIsoCode'] AND $ident['language']['countryIsoCode']) ? true : false;
      if(($isCSL AND (!$isexist_issueInfo OR !$isexist_language)) OR (!$isCSL AND ($isexist_issueInfo OR $isexist_language))){
        $fail("{$entryIdent} is wrong rule.");
      }
    }
    return;

    $filename = $this->filename;
    // jika entryIdent == 'DML/CSL' maka tidak perlu ada language di namanya
    // jika filename yang diupdate == 'CSL' maka harus denga issueInfo dan language nya
    $isCSL = substr($filename, 0, 3) == 'CSL' ? true : false;
    // kalau entryIdent DML/CSL
    // dan (kalau ini file ini CSL, count_ident harus terdiri dari code, issueInfo (2) || kalau file ini DML, count ident hanya terdiri dari code saja (1))
    if (in_array(substr($entryIdent, 0, 3), ['DML', 'CSL'])) {
      $count_ident = count(explode("_", $entryIdent));
      if (($isCSL and $count_ident != 2) or (!$isCSL and $count_ident != 1)) {
        $fail("{$entryIdent} is wrong rule.");
      }
    } else {
      // kalau entryIdent bukan DML/CSL (DMC, PMC, etc)
      // dan (kalau ini file ini CSL, count_ident harus terdiri dari code, issueInfo, language (3) || kalau file ini DML, count ident hanya terdiri dari code saja (1))
      $count_ident = count(explode("_", $entryIdent));
      if (($isCSL and $count_ident != 3) or (!$isCSL and $count_ident != 1)) {
        $fail("{$entryIdent} is wrong rule.");
      }
    }
  }
}
