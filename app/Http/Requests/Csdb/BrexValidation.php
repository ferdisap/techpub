<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use App\Models\Csdb\Dmc;
use BREXValidator;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Ptdi\Mpub\Validation\CSDBValidatee;
use Ptdi\Mpub\Validation\CSDBValidator;
use Ptdi\Mpub\Validation\Validator\Brex;

class BrexValidation extends FormRequest
{
  private array $errors = ["filename" => []];

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
      'BREXModels.*' => [function($a, $v, Closure $fail){
        if(!$v) {
          $this->errors['filename'][] = ('brexDmRef is required or it cannot be recognized as CSDB object.');
          return;
        };
        if(!($v instanceof Dmc)) {
          $this->errors['filename'][] = "brexDmRef must be a type of DMC.";
          return;
        };
        if($v->infoCode != '022') {
          $this->errors['filenames'][] = ('infoCode of brexDmRef is not correctly.');
          return;
        };
        $v->csdb->loadCSDBObject();
        if(!($v->csdb->CSDBObject) || !($v->csdb->CSDBObject->document)) {
          $this->errors['filename'][] = ('cannot resolve brexDmRef.');
          return;
        };
      }],
      'OBJECTModels.*' => [function($a, $v, Closure $fail){
        if(!$v) {
          $this->errors['filename'][] = ('csdb filename is required or it cannot be recognized as CSDB object.');
          return;
        };
        if($v->csdb->loadCSDBObject()){
          if(!($v->csdb->CSDBObject) || !($v->csdb->CSDBObject->document)) {
            $this->errors['filename'][] = "cannot process {$v->csdb->filename}";
            return;
          };
        } else {
          $this->errors['filename'][] = "cannot process {$v->csdb->filename}";
        }
      }]
    ];
  }

  protected function prepareForValidation(): void
  {
    $filenames = $this->route('filename') ?? $this->get('filename');
    if(!is_array($filenames)) $filenames = explode(',',$filenames);

    $brexFilenames = [];
    $l = count($filenames);
    for ($i=0; $i < $l; $i++) { 
      $filenames[$i] = Csdb::getObject($filenames[$i], ['exception' => ['CSDB-DELL', 'CSDB-PDEL']], $this->user()->id)->first();
      $brexFilenames[] = $filenames[$i]->brexDmRef;
    }

    $brexFilenames = array_unique($brexFilenames);
    $l = count($brexFilenames);

    for ($i=0; $i < $l; $i++) { 
      $brexFilenames[$i] = Csdb::getObject($brexFilenames[$i], ['exception' => ['CSDB-DELL', 'CSDB-PDEL']], $this->user()->id)->first();
    }

    $this->merge([
      'BREXModels' => $brexFilenames,
      'OBJECTModels' => $filenames
    ]);
  }

  protected function passedValidation()
  {
    if(empty($this->errors['filenames'])){
      return $this->failedValidation(null);
    }
    $validateeObject = $this->validated('OBJECTModels');
    array_walk($validateeObject, fn(&$v) => $v = $v->csdb->CSDBObject);

    $validatorObject = $this->validated('BREXModels');
    array_walk($validatorObject, fn(&$v) => $v = $v->csdb->CSDBObject);

    $validatee = new CSDBValidatee($validateeObject);
    $validator = new CSDBValidator($validatorObject);

    $this->brexValidation = new Brex($validator, $validatee);
  }

  protected function failedValidation(Validator|null $validator)
  {
    throw (new HttpResponseException(response([
      'infotype' => 'caution',
      'message' => count($this->errors['filename']) . " error(s) found.",
      'errors' => $this->errors,
    ],422)));
  }
}
