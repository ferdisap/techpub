<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use App\Rules\Csdb\Path;
use BREXValidator;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Ptdi\Mpub\Main\CSDBError;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\XSIValidator;

class CsdbUpdateByXMLEditor extends FormRequest
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
      'path' => [new Path],
      'oldCSDBModel' => ['required', function(string $attribute, mixed $oldCSDBModel, Closure $fail){
        if(!$oldCSDBModel) $f[] = $fail("You are not authorize to update ". $oldCSDBModel->filename . ".");
      }],
      'xmleditor' => ['required', function(string $attribute, mixed $value, Closure $fail){
        if(!($value[0]->document instanceof \DOMDocument)) return $fail('Document must be in XML form.'); // harus return agar script dibawah tidak di eksekusi
        if(!$value[0]->document) $fail('Fail to recognize xml file as CSDB object.');

        // validate with old
        $oldModel = Csdb::where('filename', $value[0]->filename)->first();
        $oldXMLString = Storage::disk('csdb')->get($this->user()->storage."/".$value[0]->filename);
        if(
          $oldXMLString == $value[0]->document->saveXML()
          && $this->path == $oldModel->path
        ) $fail('There is no changes');

        if(!in_array($value[0]->document->doctype->nodeName,['dmodule', 'pm', 'icnMetadataFile'])) return $fail('Document type must be dmodule, pm, or icnMetadataFile.'); // harus return agar script dibawah tidak di eksekusi
        if($this->xsi_validate) {
          $CSDBValidator = new XSIValidator($value[0]);
          if(!$CSDBValidator->validate()) $fail("Fail to validate by XSI. ".join(", ",CSDBError::getErrors(true, 'validateBySchema')));
        }
        if(!($this->oldCSDBModel[0])) return $fail("Failed to construct CSDB object");
        $new_filename = $value[0]->filename;
        if(!($new_filename === $this->oldCSDBModel[0]->filename)) $fail("You didn't allow to change element document ident.");
        if($this->brex_validate) {
          $CSDBValidator = new BREXValidator($value[0], $value[0]->getBrexDm());
          if(!$CSDBValidator->validate()) $fail("Fail to validate by BREX. ".join(", ",CSDBError::getErrors(true, 'validateByBrex')));
        }
      }],
    ];
  }

  /**
   * Prepare the data for validation.
   */
  protected function prepareForValidation(): void
  {
    $CSDBObject = new CSDBObject("5.0");
    $CSDBObject->loadByString($this->xmleditor);
    $this->merge([
      'path' => $this->path ?? 'CSDB',
      'xmleditor' => [$CSDBObject], // harus array atau scalar
      'xsi_validate' => $this->xsi_validate,
      'brex_validate' => $this->brex_validate,
      // 'oldCSDBModel' => [Csdb::where('filename', Route::current()->parameter('filename'))->first()],
      'oldCSDBModel' => [Csdb::getCsdb(Route::current()->parameter('filename'),["exception" => ['CSDB-DELL','CSDB-PDEL']])->first()],
    ]);
  }
}
