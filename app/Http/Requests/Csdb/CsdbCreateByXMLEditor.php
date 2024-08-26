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
        if(!in_array($value[0]->document->doctype->nodeName,['dmodule', 'pm', 'icnMetadataFile'])) return $fail('Document type must be dmodule, pm, or icnMetadataFile.'); // harus return agar script dibawah tidak di eksekusi
        if($this->xsi_validate) {
          $CSDBValidator = new XSIValidator($value[0]);
          if(!$CSDBValidator->validate()) $fail("Fail to validate by XSI. ".join(", ",CSDBError::getErrors(true, 'validateBySchema')));
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
    ]);
  }
}
