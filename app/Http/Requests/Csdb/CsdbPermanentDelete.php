<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CsdbPermanentDelete extends FormRequest
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
      'filename' => ['required', 'array' ,function(string $attribute, mixed $filename, Closure $fail){
        if(is_array($filename)){
          $l = count($filename);
          for ($k=0; $k < $l; $k++) { 
            if(!($this->CSDBModelArray[$k]) && ($this->CSDBModelArray[$k]->filename != $filename[$k])){ // jika null, maka $fail. Tidak mungkin !isset karena sudah di set di prepare
              $fail('There is no such $filename[$k] or you are not authorize to delete.');
            }
          }
        } else {
          if(!($this->CSDBModelArray[0])) $fail('There is no such $filename[$k] or you are not authorize to delete.');
        }
      }],
      'CSDBModelArray' => function(string $attribute, mixed $CSDBModelArray, Closure $fail){
        $l = count($CSDBModelArray);
        $f = [];
        for ($i=0; $i < $l; $i++) { 
          if(!$CSDBModelArray[$i]) $f[] = $CSDBModelArray[$i]->filename;
        }
        if(count($f) > 0) $fail("You are not authorize or to permanent delete " . join(", ", $f) . ".");
      }
    ];
  }

  /**
   * Prepare the data for validation.
   */
  protected function prepareForValidation(): void
  {
    $CSDBModelArray = [];
    $filename = $this->filename;
    if(!$filename) throw new HttpResponseException(response(['message' => "filename is required."],412,['content-type' => 'application/json']));
    if(is_array($filename) || ($filename = explode(",",$filename))){
      foreach($filename as $i => $f){
        // $m = Csdb::with(['object'])->where('filename',$f)->where('initiator_id',$this->user()->id)->first();
        $m = Csdb::getCsdb($f,['code' => ['CSDB-DELL']])->first();
        $CSDBModelArray[$i] = $m;
      }
    } else {
      // $m = Csdb::where('filename',$filename)->where('initiator_id',$this->user()->id)->first();
      $m = Csdb::getCsdb($filename,['code' => ['CSDB-DELL']])->first();
      array_push($CSDBModelArray, $m);
      $filename = [$filename];
    }

    $this->merge([
      'CSDBModelArray' => $CSDBModelArray,
      'filename' => $filename,
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