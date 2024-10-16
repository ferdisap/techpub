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
use Ptdi\Mpub\Validation\CSDBValidatee;
use Ptdi\Mpub\Validation\CSDBValidator;
use Ptdi\Mpub\Validation\Validator\Brex;
use Ptdi\Mpub\Validation\Validator\Xsi;

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

        if(!in_array($value[0]->initial,['dm', 'pm', 'imf'])) return $fail('Document type must be dmodule, pm, or icnMetadataFile.'); // harus return agar script dibawah tidak di eksekusi
        
        // xsi validation
        if($this->xsi_validate) {
          $xsi = new Xsi($value[0]->document);
          $xsi->validate();
          if(!$xsi->result()) $fail("Fail to validate by XSI. ".join(", ", $xsi->errors->get('xsi_validation')));
        }

        if(!($this->oldCSDBModel[0])) return $fail("Failed to construct CSDB object");
        $new_filename = $value[0]->filename;
        if(!($new_filename === $this->oldCSDBModel[0]->filename)) $fail("You didn't allow to change element document ident.");
        
        // brex validation
        if($this->brex_validate) {
          $brex = new Brex(
            new CSDBValidator($value[0]->getBrexDm()),
            new CSDBValidatee($value[0])
          );
          $brex->validate();
          if(empty($brex->result())) {
            $fail("Fail to validate by BREX.");
          }
        }
      }],
    ];
  }

  /**
   * Prepare the data for validation.
   * sengaja route di binding agar di route file nya sudah di definisikan jika missing 
   */
  protected function prepareForValidation(): void
  {
    $CSDBObject = new CSDBObject("5.0");
    if($this->xmleditor) $CSDBObject->loadByString($this->xmleditor); // biar ga error ditambah if

    $this->merge([
      'path' => $this->path ?? 'CSDB',
      'xmleditor' => [$CSDBObject], // harus array atau scalar
      'xsi_validate' => $this->xsi_validate,
      'brex_validate' => $this->brex_validate,
      'oldCSDBModel' => [Csdb::getCsdb($this->route('CSDBModel')->filename,["exception" => ['CSDB-DELL','CSDB-PDEL']])->first()],
    ]);
  }
}
