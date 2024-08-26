<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use App\Rules\Csdb\Path;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Ptdi\Mpub\Main\CSDBError;
use Ptdi\Mpub\Main\CSDBValidator;

class UploadICN extends FormRequest
{
  public bool $isUpdate = false;
  public array $fail = [];
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
      "filename" => ['required', function (string $attribute, mixed $value,  Closure $fail) {
        if(isset($this->fail['checkOldCsdb'])) $fail($this->fail['checkOldCsdb']);
        CSDBError::$processId = 'ICNFilenameValidation';
        $validator = new CSDBValidator('ICNName', ["validatee" => $value]);
        $validator->setStoragePath(CSDB_STORAGE_PATH . "/" . $this->user()->storage);
        if (!$validator->validate()) $fail(join(", ", CSDBError::getErrors(true, 'ICNFilenameValidation')));
      }],
      "entity" => ['required', function (string $attribute, mixed $value,  Closure $fail) {
        $ext = strtolower($value->getClientOriginalExtension());
        $mime = strtolower($value->getMimeType());
        if ($ext === 'xml' or str_contains($mime, 'text')) {
          $fail("You should put the non-text file in {$attribute}.");
        }
      }],
      'path' => ['required', new Path],
      'oldCSDBModel' => ''
    ];
  }

  protected function prepareForValidation(): void
  {
    $oldCSDBModel = Csdb::getCsdb($this->filename)->first();
    if($oldCSDBModel){
      if(($oldCSDBModel->storage_id != $this->user()->id)){
        $this->fail['checkOldCsdb'] = "You are not authorize to update the " . $oldCSDBModel->filename . ".";
        return;
      }
      elseif((($code = ($oldCSDBModel->lastHistory->code)) === 'CSDB-DELL')||($code === 'CSDB-PDEL')){
        $this->fail['checkOldCsdb'] = $oldCSDBModel->filename . " has been deleted({$code}).";
        return;
      } 
      $this->isUpdate = true;
    } 
    else {
      $oldCSDBModel = new Csdb();
    }
    $this->merge([
      'path' => $this->path ?? 'CSDB',
      'oldCSDBModel' => $oldCSDBModel
    ]);
  }
}
