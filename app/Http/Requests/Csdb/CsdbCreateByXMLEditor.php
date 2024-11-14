<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use App\Rules\Csdb\Path;
use BREXValidator;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Storage;
use Ptdi\Mpub\Main\CSDBError;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\CSDBValidator;
use Ptdi\Mpub\Main\XSIValidator;
use Ptdi\Mpub\Validation\CSDBValidatee;
use Ptdi\Mpub\Validation\CSDBValidator as ValidationCSDBValidator;
use Ptdi\Mpub\Validation\Validator\Brex;
use Ptdi\Mpub\Validation\Validator\Xsi;

class CsdbCreateByXMLEditor extends FormRequest
{
  protected array $errors = []; // value must be array, key must be string

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
      'path' => ['required', new Path],
      // 'xmleditor' => '',
      'xmleditor' => ['required', function (string $attribute, mixed $value, Closure $fail) {
        // 'xmleditor_x' => [function(string $attribute, mixed $value, Closure $fail){
        if (!($value[0]->document instanceof \DOMDocument)) return $fail('Document must be in XML form.'); // harus return agar script dibawah tidak di eksekusi
        if (!$value[0]->document) $fail('Fail to recognize xml file as CSDB object.');
        if (!$value[0]->document->doctype) return $fail('Document must have a doctype.'); // harus return agar script dibawah tidak di eksekusi
        if ($value[0]->document->doctype->nodeName !== $value[0]->document->documentElement->nodeName) return $fail('Document type must same with root element name.'); // harus return agar script dibawah tidak di eksekusi
        if (!in_array($value[0]->document->doctype->nodeName, ['dmodule', 'pm', 'icnMetadataFile'])) return $fail('Document type must be dmodule, pm, or icnMetadataFile.'); // harus return agar script dibawah tidak di eksekusi

        // xsi validation
        if ($this->xsi_validate) {
          $xsi = new Xsi($value[0]->document);
          $xsi->validate();
          if (!$xsi->result()) $fail("Fail to validate by XSI. " . join(", ", $xsi->errors->get('xsi_validation')));
        }

        try {
          $domXpath = new \DOMXPath($value[0]->document);
          $filename = $value[0]->filename;
          $initial = $value[0]->getInitial();
          $code = preg_replace("/_.+/", '', $filename);
          $collection = Csdb::selectRaw('filename')->whereRaw("filename LIKE '{$code}%'")->get(['filename'])->toArray();
          array_walk($collection, function (&$v) {
            $v = $v['filename'];
          });
          if (empty($collection)) {
            $issueInfo = $domXpath->evaluate("//{$initial}Address/{$initial}Ident/issueInfo")[0];
            $issueInfo->setAttribute('issueNumber', '000');
            $issueInfo->setAttribute('inWork', '01');
          } else {
            $collection_issueNumber = [];
            $collection_inWork = [];
            array_walk($collection, function ($file, $i) use (&$collection_issueNumber, &$collection_inWork) {
              $issueInfo = CSDBStatic::decode_ident($file)['issueInfo'];
              $collection_issueNumber[$i] = $issueInfo['issueNumber'];
              $collection_inWork[$i] = $issueInfo['inWork'];
            });
            $issueInfo = $domXpath->evaluate("//{$initial}Address/{$initial}Ident/issueInfo")[0];
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
        } catch (\Throwable $th) {
          $fail("Failed to determine filename. You must provide the document address correctly");
        }

        $identStatus = $domXpath->evaluate("//{$initial}Status")[0];
        if($identStatus){
          // add/set QA
          $qa = $domXpath->evaluate("//{$initial}Status/qualityAssurance")[0];
          if (!$qa)  $qa = $value[0]->document->createElement('qualityAssurance');
          $identStatus->appendChild($qa);
          while ($qa->firstChild) {
            $qa->firstChild->remove();
          };
          $unverified = $value[0]->document->createElement('unverified');
          $qa->appendChild($unverified);

          // check brex
          if ($this->brex_validate) {
            $brex = new Brex(
              new ValidationCSDBValidator($value[0]->getBrexDm()),
              new CSDBValidatee($value[0])
            );
            $brex->validate();
            if (empty($brex->result())) {
              $fail("Fail to validate by BREX.");
            }
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
    if ($this->xmleditor) $CSDBObject->loadByString($this->xmleditor); // biar ga error ditambah if

    if ($CSDBObject) {
      try {
        $prefix = substr($CSDBObject->filename, 0, 3);
        if($this->path){
          $path = "CSDB\/" . $prefix;
          preg_match("/{$path}/",$this->path,$m);
          if(!$m[0]) {
            $path = null; // jika path dari request user tidak sesuai dengan $path, maka akan di null kan
            $this->errors['path'] = ["The path must be prefixed by 'CSDB/".$prefix."'."];
          } else {
            $path = "CSDB/" . $prefix;
          }
        } else {
          $path = "CSDB/" . $prefix;
        }
      } catch (\Throwable $e) {
      }
    }

    $this->merge([
      'path' => $path ?? null,
      'xmleditor' => [$CSDBObject], // harus array atau scalar
      'xsi_validate' => $this->xsi_validate,
      'brex_validate' => $this->brex_validate,
    ]);
  }

  protected function failedValidation(Validator $validator)
  {
    $errors = $validator->errors()->toArray();

    foreach ($this->errors as $key => $value) {
      $errors[$key] = $errors[$key] ? array_merge($errors[$key], $value) : $value;
    }

    throw (new HttpResponseException(response([
      'infotype' => 'caution',
      'message' => $validator->errors()->first(),
      'errors' => $errors,
    ], 422)));
  }
}
