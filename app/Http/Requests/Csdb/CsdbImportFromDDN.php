<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * $this->route('CSDBModel')->object saat run script ini berulang kali, laravel juga tidak akan meng query lagi tapi sudah di cache
 */
class CsdbImportFromDDN extends FormRequest
{
  public $duplicatedCSDBModels = [];

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
    $userId = $this->user()->id;
    return [
      'path' => '',
      'dispatchTo' => ['required', function(string $a, mixed $v, Closure $fail){
        if($this->user()->id != $v) $fail("You are not the receiver of the DDN.");
      }],
      // 'DDNCSDBModel' => ['required', function(string $a, mixed $v, Closure $fail){
      //   if($this->user()->id != $v->object->dispatchTo_id) $fail("The destination dispatch of {$v->filename} is not you.");
      // }],
      'filenames' => ['array'],
      'filenames.*' => ['required', function(string $a, mixed $v, Closure $fail) use($userId){
        if(!in_array($v,  $this->route('CSDBModel')->object->ddnContent)) $fail("{$v} is not covered by the {$this->CSDBModel->filename}");
        if($duplicated = Csdb::getCsdb($v, $userId)->first()){
          $this->duplicatedCSDBModels[] = $duplicated;
        }
      }]
    ];
  }

  protected function prepareForValidation(): void
  {
    if($this->route('CSDBModel')->lastHistory->code === 'CSDB-DELL' || $this->route('CSDBModel')->lastHistory->code === 'CSDB-PDEL'){
      throw new HttpResponseException(response(["message" => $this->route('CSDBModel')->filename . " has been deleted."],404));
    }

    // $previous_storage_user_id = Csdb::$storage_user_id;
    // Csdb::$storage_user_id = null;
    // $DDNCSDBModel = Csdb::getCsdb($this->route()->parameter('filename'),["exception" => ["CSDB-DELL", "CSDB_PDEL"]])->with('object')->first();
    // Csdb::$storage_user_id = $previous_storage_user_id;

    $this->merge([
      'path' => 'CSDB/IMPORTED',
      'dispatchTo' => $this->route('CSDBModel')->object->dispatchTo_id,
    ]);
  }

  // protected function passedValidation()
  // {
  //   $CSDBImportModel = [];

  //   $this->merge([
  //     // harus array atau scalar, entah kenapa
  //     // Expected a scalar, or an array as a 2nd argument to \"Symfony\\Component\\HttpFoundation\\InputBag::set()\", \"Ptdi\\Mpub\\Main\\CSDBObject\" given.
  //     'CSDBImportModel' => $CSDBImportModel,
  //   ]);
  // }

  protected function failedValidation(Validator $validator)
  {
    throw (new HttpResponseException(response([
      'infotype' => 'caution',
      'message' => $validator->errors()->first(),
      'errors' => $validator->errors()->toArray(),
    ],422)));
  }
}
