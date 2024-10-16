<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use App\Rules\Csdb\Path;
use BREXValidator;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Ptdi\Mpub\Main\CSDBError;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBValidator;
use Ptdi\Mpub\Main\XSIValidator;
use Ptdi\Mpub\Validation\CSDBValidatee;
use Ptdi\Mpub\Validation\CSDBValidator as ValidationCSDBValidator;
use Ptdi\Mpub\Validation\Validator\Brex;
use Ptdi\Mpub\Validation\Validator\Xsi;

class CsdbCreateByXMLEditor extends FormRequest
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
   * validate dom, documen type, xsi, issueInfo, QA, brex
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'path' => [new Path],
      'xmleditor' => ['required', function(string $attribute, mixed $value, Closure $fail){
        if(!($value[0]->document instanceof \DOMDocument)) return $fail('Document must be in XML form.'); // harus return agar script dibawah tidak di eksekusi
        if(!$value[0]->document) $fail('Fail to recognize xml file as CSDB object.');
        if(!$value[0]->document->doctype) return $fail('Document must have a type.'); // harus return agar script dibawah tidak di eksekusi
        if($value[0]->document->doctype->nodeName !== $value[0]->document->documentElement->nodeName ) return $fail('Document type must same with root element name.'); // harus return agar script dibawah tidak di eksekusi
        if(!in_array($value[0]->document->doctype->nodeName,['dmodule', 'pm', 'icnMetadataFile'])) return $fail('Document type must be dmodule, pm, or icnMetadataFile.'); // harus return agar script dibawah tidak di eksekusi

        // xsi validation
        if($this->xsi_validate) {
          $xsi = new Xsi($value[0]->document);
          $xsi->validate();
          if(!$xsi->result()) $fail("Fail to validate by XSI. ".join(", ", $xsi->errors->get('xsi_validation')));
        }
        $domXpath = new \DOMXPath($value[0]->document);
        $filename = $value[0]->filename;
        $initial = $value[0]->getInitial();
        $code = preg_replace("/_.+/", '', $filename);
        $collection = Csdb::selectRaw('filename')->whereRaw("filename LIKE '{$code}%'")->get()->toArray();
        array_walk($collection,function(&$v){
          $v = $v['filename'];
        });
        if(empty($collection)){
          $issueInfo = $domXpath->evaluate("//identAndStatusSection/{$initial}Address/{$initial}Ident/issueInfo")[0];
          $issueInfo->setAttribute('issueNumber', '000');
          $issueInfo->setAttribute('inWork', '01');
        } else {
          $collection_issueNumber = [];
          $collection_inWork = [];
          array_walk($collection, function ($file, $i) use (&$collection_issueNumber, &$collection_inWork) {
            $file = explode('_', $file);
            if (isset($file[1])) {
              $issueInfo = explode("-", $file[1]);
              $collection_issueNumber[$i] = $issueInfo[0];
              $collection_inWork[$i] = $issueInfo[1];
            }
          });
          $issueInfo = $domXpath->evaluate("//identAndStatusSection/{$initial}Address/{$initial}Ident/issueInfo")[0];
          $max_in = max($collection_issueNumber);
          $max_in = array_keys(array_filter($collection_issueNumber, fn ($v) => $v == $max_in))[0]; // output key. bukan value array
          $max_in = $collection_issueNumber[$max_in];
          $max_iw = max($collection_inWork);
          $max_iw = array_keys(array_filter($collection_inWork, fn ($v) => $v == $max_iw))[0]; // output key. bukan value array
          $max_iw = $collection_inWork[$max_iw];
          $max_iw++;

          $issueInfo->setAttribute('issueNumber', str_pad($max_in, 3, '0', STR_PAD_LEFT));
          $issueInfo->setAttribute('inWork', str_pad($max_iw, 2, '0', STR_PAD_LEFT));
        }

        $qa = $domXpath->evaluate("//identAndStatusSection/{$initial}Status/qualityAssurance")[0];
        if(!$qa) {
          $qa = $value[0]->document->createElement('qualityAssurance');
          $identStatus = $domXpath->evaluate("//identAndStatusSection/{$initial}Status")[0];
          $identStatus->appendChild($qa);
        }
        $unverified = $value[0]->document->createElement('unverified');
        $qa->appendChild($unverified);
        try {
        } catch (\Throwable $th) {
          $fail("Fail to determining filename.");
        }
        if($this->brex_validate) {
          $brex = new Brex(
            new ValidationCSDBValidator($value[0]->getBrexDm()),
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
    ]);
  }
}
