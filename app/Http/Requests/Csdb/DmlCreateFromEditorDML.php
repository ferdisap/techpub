<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use App\Models\Csdb\Dml;
use App\Rules\Csdb\BrexDmRef;
use App\Rules\Csdb\DmlType;
use App\Rules\Csdb\Path;
use App\Rules\Csdb\SecurityClassification;
use App\Rules\Csdb\SeqNumber;
use App\Rules\EnterpriseCode;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Ptdi\Mpub\Main\CSDBObject;

class DmlCreateFromEditorDML extends FormRequest
{
  public $fail = [];
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

      // ident
      'modelIdentCode' => 'required',
      'yearOfDataIssue' => '',
      'dmlType' => [new DmlType],
      'originator' => [new EnterpriseCode(true)],
      'securityClassification' => [new SecurityClassification],
      'seqNumber' => [new SeqNumber(true, 'dml')],
      'inWork' => '', 
      'day' => '', 
      'month' => '', 

      // status
      'brexDmRef' => ['required', new BrexDmRef],
      'dmlRef' => 'array',
      'remarks' => 'array',
    ];
  }

  /**
   * Prepare the data for validation.
   */
  protected function prepareForValidation(): void
  {
    $originator = $this->get('originator') ?? ($this->user()->work_enterprise->code->name ?? '');
    $dmlType = $this->get('dmlType') ?? 'P';
    $yearOfDataIssue = date('Y');
    $unCompletedName = "DML-{$this->modelIdentCode}-{$originator}-{$dmlType}-{$yearOfDataIssue}-"; 
    if(!($seqNumber = Csdb::where('filename', 'like', "{$unCompletedName}%")->where('storage_id', $this->user()->id)->orderBy('filename','desc')->first())){
      $seqNumber = '00001';
    } else {
      $l = strlen($unCompletedName);
      $seqNumber = substr($seqNumber->filename,$l,5);
      $seqNumber++;
      $seqNumber = str_pad($seqNumber, 5, '0', STR_PAD_LEFT);
    }

    $this->merge([
      'path' => $this->path ?? 'CSDB/DML',      

      // ident
      'modelIdentCode' => $this->get('modelIdentCode'),
      'yearOfDataIssue' => $yearOfDataIssue,
      'dmlType' => $dmlType,
      'originator' => $originator,
      'securityClassification' => $this->get('securityClassification'),
      'seqNumber' => $seqNumber,
      'inWork' => '01', 
      'day' => date('d'), 
      'month' => date('m'), 

      // status
      'brexDmRef' => $this->get('brexDmRef'),
      'dmlRef' => $this->get('dmlRef') ?? [],
      'remarks' => preg_split("/<br\/>|<br>|&#10;/m",$this->remarks),
    ]);
  }


  /**
   * Handle a passed validation attempt.
   * Bukan mengubah validated data
   */
  protected function passedValidation()
  {
    // $otherOptions = [];
    $DMLModel = new Dml();
    $DMLModel->create_xml($this->user()->storage, $this->validated());

    $this->merge([
      // harus array atau scalar, entah kenapa
      // Expected a scalar, or an array as a 2nd argument to \"Symfony\\Component\\HttpFoundation\\InputBag::set()\", \"Ptdi\\Mpub\\Main\\CSDBObject\" given.
      'CSDBObject' => [$DMLModel->CSDBObject], 
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
