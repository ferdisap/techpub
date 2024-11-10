<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use App\Rules\Csdb\Path;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
// use Ptdi\Mpub\Main\CSDBError;
use Ptdi\Mpub\Main\CSDBStatic;
// use Ptdi\Mpub\Main\CSDBValidator;


/**
 * NOTE
 * - part?int
 * - total?int
 */
// revisi selanjutnya validasi ditambahkan $CSDBModel->CSDBObject->document->getFileinfo()['mime_type'], if(!isset(['mime_type'])) $fail('...')
class UploadICN extends FormRequest
{
  public bool $isUpdate = false;
  public array $fail = [];

  public bool $chunk = false;
  public bool $end = true;
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    if(isset($this->fail['checkOldCsdb'])) return false;
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
        $decodedFilename = CSDBStatic::decode_infoEntityIdent($value);
        // $infoEntityIdent = $decodedF
        if(!isset($decodedFilename['prefix']) || ($decodedFilename['prefix'] !== 'ICN-')){
          $fail("Filename shall be prefixed by 'ICN-");
        }
        if(!$decodedFilename['extension']){
          $fail("Extension shall be existed.");
          return;
        }
        if(!(count($decodedFilename['infoEntityIdent']) === 9 OR count($decodedFilename['infoEntityIdent']) === 4)){
          $fail("Naming file '". $decodedFilename['prefix'] . join("-", $decodedFilename['infoEntityIdent']). $decodedFilename['extension'] . "' is uncomply.");
        }
        // CSDBError::$processId = 'ICNFilenameValidation';
        // $validator = new CSDBValidator('ICNName', ["validatee" => $value]);
        // $validator->setStoragePath(CSDB_STORAGE_PATH . "/" . $this->user()->storage);
        // if (!$validator->validate()) $fail(join(", ", CSDBError::getErrors(true, 'ICNFilenameValidation')));
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

  // jika !part, tapi isUpdate maka history akan akan update;
  // elseif part/total < total, chunk true. Jika chunk maka tidak akan membuat history. 
  protected function prepareForValidation(): void
  {
    $oldCSDBModel = Csdb::getCsdb($this->filename,[], $this->user()->id)->first();

    // jika part/total < total maka chunk
    if($this->part){
      $this->chunk = true;
      if(((int)$this->part) < ((int)$this->total)){
        $this->end = false;
      }
    }

    if($oldCSDBModel){
      if(($oldCSDBModel->storage_id != $this->user()->id)){
        $this->fail['checkOldCsdb'] = "You are not authorize to update the " . $oldCSDBModel->filename . ".";
        return;
      }
      elseif((($code = ($oldCSDBModel->lastHistory->code)) === 'CSDB-DELL')||($code === 'CSDB-PDEL')){
        $this->fail['checkOldCsdb'] = $oldCSDBModel->filename . " has been deleted({$code}).";
        return;
      } 
      // jika bukan chunk upload
      $this->isUpdate = true;
    } 
    else {
      $oldCSDBModel = new Csdb();
    }

    $this->merge([
      'path' => $this->path ?? 'CSDB/ICN',
      'oldCSDBModel' => $oldCSDBModel
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
