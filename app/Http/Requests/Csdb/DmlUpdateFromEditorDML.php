<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use App\Rules\Csdb\SecurityClassification;
use App\Rules\Dml\EntryIdent;
use App\Rules\Dml\EntryIssueType;
use App\Rules\Dml\EntryType;
use App\Rules\EnterpriseCode;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Ptdi\Mpub\Main\CSDBStatic;

class DmlUpdateFromEditorDML extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      // dml ident
      'ident-securityClassification' => ['required', new SecurityClassification(true)],
      'ident-brexDmRef' => ['required', function (string $attribute, mixed $value, Closure $fail) {
        if (empty(CSDBStatic::decode_dmIdent($value))) {
          $fail("The {$attribute} is wrong rule.");
        }
      }],
      'ident-remarks' => '',
      // dml entries
      // 'entries' => [function (string $attribute, mixed $value, Closure $fail) {
      // }],
      'entryIdent' => [fn (string $attribute, mixed $value, Closure $fail) => count($value) !== count(array_unique($value)) ? $fail("Entry Ident must be unique.") : true],
      'entryIdent.*' => ['required', new EntryIdent(null)],
      'dmlEntryType.*' => [new EntryType],
      'issueType.*' => [new EntryIssueType],
      'securityClassification.*' => [new SecurityClassification(false)],
      'enterpriseCode.*' => [new EnterpriseCode(false)],
      'enterpriseName.*' => ['required'],
      'remarks' => 'array',
      'answer' => 'array',
      'answerToEntry.*' => [fn (string $attribute, mixed $value, Closure $fail) => (empty($value) || $value === 'y' || $value === 'n') ? true : $fail("answerToEntry value must be in 'y' or 'n'")],
    ];
  }

  public function prepareForValidation()
  {
    if($this->route('CSDBModel')->lastHistory->code === 'CSDB-DELL' || $this->route('CSDBModel')->lastHistory->code === 'CSDB-PDEL'){
      abort(404, $this->route('CSDBModel')->filename . " has been deleted.");
    }

    $ident = $this->get('ident'); // array
    $this->request->remove('ident');

    if(!is_array($ident['ident-remarks']) AND is_string($ident['ident-remarks'])){
      $ident['ident-remarks'] = explode("\n",$ident['ident-remarks']);
    }

    $entries = $this->get('entries');
    $this->request->remove('entries');
    $l = 0;
    $entries['no'] = [];
    $entries['entryIdent'] = [];
    $entries['dmlEntryType'] = [];
    $entries['issueType'] = [];
    $entries['securityClassification'] = [];
    $entries['enterpriseName'] = [];
    $entries['enterpriseCode'] = [];
    $entries['remarks'] = [];
    $entries['answer'] = [];
    $entries['answerToEntry'] = [];
    while (isset($entries[$l])) {
      array_push($entries['no'], $entries[$l]['no']);
      array_push($entries['entryIdent'], $entries[$l]['entryIdent']);
      
      $dmlEntryType = '';
      switch ($entries['dmlEntryType']) {
        case 'new'||'n': $dmlEntryType = 'n';break;
        case 'changed'||'change'||'c': $dmlEntryType = 'c';break;
        case 'deleted'||'delete'||'d': $dmlEntryType = 'd';break;
      }
      array_push($entries['dmlEntryType'], $dmlEntryType);
      array_push($entries['issueType'], $entries[$l]['issueType']);
      array_push($entries['securityClassification'], $entries[$l]['securityClassification']);
      array_push($entries['enterpriseName'], $entries[$l]['enterpriseName']);
      array_push($entries['enterpriseCode'], $entries[$l]['enterpriseCode']);
      array_push($entries['remarks'], array_filter($entries[$l]['remarks'], fn($v) => $v)); // kan remarks itu array, jadi di filter supaya tidak ada yang null agar nantinya tidak dibuat element <simplePara> yang kosong
      array_push($entries['answer'], array_filter($entries[$l]['answer'], fn($v) => $v)); // kan remarks itu array, jadi di filter supaya tidak ada yang null agar nantinya tidak dibuat element <simplePara> yang kosong
      array_push($entries['answerToEntry'], $entries[$l]['answerToEntry']);
      unset($entries[$l]);
      $l++;
    }
    $this->replace(array_merge($ident,$entries));
  }

  /**
   * @deprecated karena tidak dipakai lagi di CsdbApi/DmlController
   */
  protected function passedValidation()
  {
    // $this->DMLModel = Csdb::getObject($this->route('filename'))->first();
    $this->DMLModel = $this->route('CSDBModel')->object;
  }

  protected function failedValidation(Validator $validator)
  {
    throw (new HttpResponseException(response([
      'infotype' => 'caution',
      'message' => $validator->errors()->first(),
      'errors' => $validator->errors()->toArray(),
    ],422)));
  }
}
