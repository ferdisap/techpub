<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use App\Models\Csdb\Dmc;
use BREXValidator;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class BrexValidation extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return false;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'BREXModel' => [function($a, $v, Closure $fail){
        if(!$v) return $fail('brexDmRef is required.');
        if(!($v instanceof Dmc)) return $fail('brexDmRef must be a type of DMC.');
        if($v->infoCode != '022') return $fail ('infoCode of brexDmRef is not correctly.');
        $v->csdb->loadCSDBObject();
        if(!($v->csdb->CSDBObject) || !($v->csdb->CSDBObject->document)) return $fail('cannot resolve brexDmRef.');
      }],
      'OBJECTModel' => [function($a, $v, Closure $fail){
        if(!$v) return $fail('csdb filename is required.');
        $v->csdb->loadCSDBObject();
        if(!($v->csdb->CSDBObject) || !($v->csdb->CSDBObject->document)) return $fail("cannot resolve {$v->csdb->filename}");
      }]
    ];
  }

  protected function prepareForValidation(): void
  {
    $OBJECTModel = Csdb::getObject($this->route('filename') ?? $this->get('filename'), ['exception' => ['CSDB-DELL', 'CSDB-PDEL']])->first();
    $BREXModel = Csdb::getObject($OBJECTModel->brexDmRef, ['exception' => ['CSDB-DELL', 'CSDB-PDEL']])->first();
    $this->merge([
      'BREXModel' => $BREXModel,
      'OBJECTModel' => $$OBJECTModel
    ]);
  }

  protected function passedValidation()
  {
    $BREXValidator = new BREXValidator(
      $this->validated('OBJECTModel')->csdb->CSDBObject,
      $this->validated('BREXModel')->csdb->CSDBObject,
    );

    $BREXValidator->validate();

    dd($BREXValidator);
  }
}
