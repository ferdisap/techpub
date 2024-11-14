<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Ptdi\Mpub\Main\CSDBObject;

class XsiValidation extends FormRequest
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
      'CSDBObjects.*' => [function($a, $v, Closure $fail){
        if(!$v) {
          $this->errors['filename'][] = ('csdb filename is required or it cannot be recognized as CSDB object.');
          return;
        };
        if(!($v->document) || !($v->document instanceof \DOMDocument)) {
          $this->errors['filename'][] = "cannot process {$v->filename}";
          return;
        };
      }]
    ];
  }

  protected function prepareForValidation(): void
  {
    $filenames = $this->route('filename') ?? $this->get('filename');
    if(!is_array($filenames)) $filenames = explode(',',$filenames);
    $l = count($filenames);
    $CSDBObjects = [];
    for ($i=0; $i < $l; $i++) { 
      $CSDBModel = Csdb::getCsdb($filenames[$i], ['exception' => ['CSDB-DELL', 'CSDB-PDEL']], $this->user()->id)->first();
      if($CSDBModel->loadCSDBObject()){
        $CSDBObjects[] = $CSDBModel->CSDBObject;
      } else {
        $CSDBObjects[] = new CSDBObject();
      }
    }
    
    $this->merge([
      'CSDBObjects' => $CSDBObjects,
    ]);
  }

  protected function passedValidation()
  {
    if(!empty($this->errors['filenames'])){
      return $this->failedValidation(null);
    }
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
