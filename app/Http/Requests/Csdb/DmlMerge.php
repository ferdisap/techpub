<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DmlMerge extends FormRequest
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
      'path' => '',
      'CSDBModel' => [function(string $a, mixed $CSDBModel, Closure $fail){
        if(!(strpos($CSDBModel->filename,'-P-'))) $fail("Only DML with type 'P' can be merged.");
        $CSDBModel->loadCSDBObject();
        if(!$CSDBModel->CSDBObject->document || !($CSDBModel->CSDBObject->document instanceof \DOMDocument)){
          $fail("Cannot load CSDB Object of $a.");
        }
      }],
      'sourceModels' => ['required', 'array'],
      'sourceModels.*' => [function(string $a, mixed $sourceModel, Closure $fail){
        if(!$sourceModel) return $fail("$a is required or it is not available in CSDB.");
        if(substr($sourceModel->filename,0,3) !== 'DML') $fail("$a is not DML.");
        if(strpos($sourceModel->filename,'-S-')) $fail("Only DML with type 'P' or 'C' as a source of merging.");
        $sourceModel->loadCSDBObject();
        if(!$sourceModel->CSDBObject->document || !($sourceModel->CSDBObject->document instanceof \DOMDocument)){
          $fail("Cannot load CSDB Object of $a.");
        }
      }],
    ];
  }

  protected function prepareForValidation(): void
  {
    $CSDBModel = Csdb::getCsdb($this->route('filename'),['exception' => ['CSDB-DELL', 'CSDB-PDEL']])->first();
    $sourceModels = $this->source;
    if(is_string($sourceModels)) $sourceModels = preg_split("/,|\s/m",$sourceModels);

    foreach ($sourceModels as $key => $filename) {
      $sourceModels[$key] = Csdb::getCsdb($filename,['exception' => ['CSDB-DELL', 'CSDB-PDEL']])->first();
    }

    $this->merge([
      'path' => $this->path ?? 'CSDB/DML',
      'CSDBModel' => $CSDBModel,
      'sourceModels' => $sourceModels
    ]);
  }

  protected function passedValidation()
  {
    $MergedCSDBModel = new Csdb();
    $MergedCSDBModel->CSDBObject = $this->validated('CSDBModel')->CSDBObject;
    $ident_dmlCode = $MergedCSDBModel->CSDBObject->document->getElementsByTagName('dmlCode')[0];
    $ident_dmlCode->setAttribute('dmlType', 's');
    $dmlContent = $MergedCSDBModel->CSDBObject->document->getElementsByTagName('dmlContent')[0];
    foreach($this->validated('sourceModels') as $sourceModel){
      $sourceDmlEntry = $sourceModel->CSDBObject->document->getElementsByTagName('dmlEntry');
      if(count($sourceDmlEntry) < 1) continue;
      while($sourceDmlEntry->nextElementSibling){
        $newEntry = $sourceDmlEntry->nextElementSibling->cloneNode(true);
        $newEntry = $MergedCSDBModel->CSDBObject->document->importNode($newEntry, true);
        $dmlContent->appendChild($newEntry);
      }      
    }
    
    $this->merge([
      'MergedCSDBModel' => $MergedCSDBModel,
    ]);
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
