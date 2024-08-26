<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use App\Models\Csdb\Ddn;
use App\Models\User;
use App\Rules\Csdb\BrexDmRef;
use App\Rules\Csdb\Path;
use App\Rules\Csdb\SecurityClassification;
use App\Rules\Csdb\SeqNumber;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Ptdi\Mpub\Main\CSDBStatic;

class DdnCreate extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    $dispatchFromPersonEmail = $this->request->get('dispatchFromPersonEmail');
    return (Auth::user()->email === $dispatchFromPersonEmail && $this->user()->email === $dispatchFromPersonEmail) ?
      true : false;
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
      
      'seqNumber' => [new SeqNumber(true, 'ddn')],
      'modelIdentCode' => ['required'],
      'senderIdent' => 'required',
      'receiverIdent' => 'required',
      
      'securityClassification' => ['required',new SecurityClassification], // nanti harus divalidasi valuenya harus dua digit dan nanti keynya harus dibedakan antara DDN dan COM jika dibuat dalam satu request yang sama
      'authorization' => 'required',
      'brexDmRef' => ['required', new BrexDmRef],
      'remarks' => '',
      
      'dispatchTo_enterpriseName' => 'required',
      'dispatchTo_division' => '',
      'dispatchTo_enterpriseUnit' => '',
      'dispatchTo_lastName' => 'required',
      'dispatchTo_firstName' => '',
      'dispatchTo_jobTitle' => '',
      'dispatchTo_department' => '',
      'dispatchTo_street' => '',
      'dispatchTo_postOfficeBox' => '',
      'dispatchTo_postalZipCode' => '',
      'dispatchTo_city' => 'required',
      'dispatchTo_country' => 'required',
      'dispatchTo_state' => '',
      'dispatchTo_province' => '',
      'dispatchTo_building' => '',
      'dispatchTo_room' => '',
      'dispatchTo_phoneNumber' => '',
      'dispatchTo_faxNumber' => '',
      'dispatchTo_email' => '',
      'dispatchTo_internet' => '',
      'dispatchTo_SITA' => '',

      'dispatchFrom_enterpriseName' => 'required',
      'dispatchFrom_division' => '',
      'dispatchFrom_enterpriseUnit' => '',
      'dispatchFrom_lastName' => 'required',
      'dispatchFrom_firstName' => '',
      'dispatchFrom_jobTitle' => '',
      'dispatchFrom_department' => '',
      'dispatchFrom_street' => '',
      'dispatchFrom_postOfficeBox' => '',
      'dispatchFrom_postalZipCode' => '',
      'dispatchFrom_city' => 'required',
      'dispatchFrom_country' => 'required',
      'dispatchFrom_state' => '',
      'dispatchFrom_province' => '',
      'dispatchFrom_building' => '',
      'dispatchFrom_room' => '',
      'dispatchFrom_phoneNumber' => '',
      'dispatchFrom_faxNumber' => '',
      'dispatchFrom_email' => '',
      'dispatchFrom_internet' => '',
      'dispatchFrom_SITA' => '',

      'deliveryListItemsFilename' => 'array',
      'deliveryListItemsFilename.*' => [function(string $attribute, mixed $filename,  Closure $fail){
        $CSDBModel = Csdb::where('filename', $filename)->first();
        if($CSDBModel){
          if(!Storage::disk('csdb')->exists($this->user()->storage."/".$filename)){
            $fail("$filename doesn't exist in storage.");
          }
        } else {
          $fail("$filename doest't available in CSDB record.");
        }
      }],
    ];
  }

  /**
   * Prepare the data for validation.
   */
  protected function prepareForValidation(): void
  {
    // $seqNumber = DB::table(env('DB_TABLE_DDN', 'ddn'))->select('seqNumber')->orderBy('seqNumber', 'desc')->first()->seqNumber ?? '00000';
    $previous_csdb_ddn = DB::table(env('DB_TABLE_CSDB', 'csdb'))->where('filename',  'like', 'DDN-%')->orderBy('filename', 'desc')->first();
    if($previous_csdb_ddn && $previous_csdb_ddn->filename){
      $seqNumber = CSDBStatic::decode_ddnIdent($previous_csdb_ddn->filename)['ddnCode']['seqNumber'];
    } else $seqNumber = '00000';
    $seqNumber++;
    $seqNumber = str_pad($seqNumber, 5, '0', STR_PAD_LEFT);

    $dispatchFromPersonModel = $this->user(); // lihat di fungsi authorize calss ini, bisa apaki this request atau Auth::
    $dispatchFromEnterpriseModel = $dispatchFromPersonModel->work_enterprise;
    $senderIdent = $dispatchFromEnterpriseModel->code->name;
    
    if($dispatchToPersonModel = User::where('email', $this->get('dispatchToPersonEmail'))->first()){
      $dispatchToEnterpriseModel = $dispatchToPersonModel->work_enterprise;
      $receiverIdent = $dispatchToEnterpriseModel->code->name;
    }
    
    if($brexDmRef = $this->get('brexDmRef')){
      $brexDmRefDecoded = CSDBStatic::decode_dmIdent($brexDmRef);
      $modelIdentCode = $brexDmRefDecoded['dmCode']['modelIdentCode'] ?? '';
    }
    
    $remarks = $this->get('remarks');
    if(!is_array($remarks) AND is_string($remarks)){
      $rand = rand(0,99999);
      $remarks = preg_replace("/[\r\n]+/", $remarks, $rand);
      $remarks = explode($rand,$remarks);
    }

    // $dispatchToEnterpriseAddress = $dispatchToEnterpriseModel->address ?? [];
    // $dispatchToEnterpriseRemarks = $dispatchToEnterpriseModel->remarks ?? [];

    $this->merge([
      'path' => $this->path ?? "CSDB/DDN",

      'seqNumber' => $seqNumber ?? '',
      'modelIdentCode' => $modelIdentCode ?? '',
      'senderIdent' => $senderIdent ?? '',
      'receiverIdent' => $receiverIdent ?? '',
      
      'securityClassification' => $this->get('securityClassification'),
      'authorization' => $this->get('authorization') ?? 'undefined',
      'brexDmRef' => $brexDmRef,
      'remarks' => $remarks,
      
      // untuk <address> diisi dengan address enterprise
      'dispatchTo_enterpriseName' => $dispatchToEnterpriseModel->name ?? '',
      'dispatchTo_division' => $dispatchToEnterpriseModel->remarks['division'] ?? '',
      'dispatchTo_enterpriseUnit' => $dispatchToEnterpriseModel->remarks['enterpriseUnit'] ?? '',
      'dispatchTo_lastName' => $dispatchToPersonModel->last_name ?? '',
      'dispatchTo_firstName' => $dispatchToPersonModel->first_name ?? '',
      'dispatchTo_jobTitle' => $dispatchToPersonModel->job_title ?? '',
      'dispatchTo_department' => $dispatchToEnterpriseModel->address['department'] ?? '',
      'dispatchTo_street' => $dispatchToEnterpriseModel->address['street'] ?? '',
      'dispatchTo_postOfficeBox' => $dispatchToEnterpriseModel->address['postOfficeBox'] ?? '',
      'dispatchTo_postalZipCode' => $dispatchToEnterpriseModel->address['postalZipCode'] ?? '',
      'dispatchTo_city' => $dispatchToEnterpriseModel->address['city'] ?? '',
      'dispatchTo_country' => $dispatchToEnterpriseModel->address['country'] ?? '',
      'dispatchTo_state' => $dispatchToEnterpriseModel->address['state'] ?? '',
      'dispatchTo_province' => $dispatchToEnterpriseModel->address['province'] ?? '',
      'dispatchTo_building' => $dispatchToEnterpriseModel->address['building'] ?? '',
      'dispatchTo_room' => $dispatchToEnterpriseModel->address['room'] ?? '',
      'dispatchTo_phoneNumber' => $dispatchToEnterpriseModel->address['phoneNumber'] ?? [],
      'dispatchTo_faxNumber' => $dispatchToEnterpriseModel->address['faxNumber'] ?? [],
      'dispatchTo_email' => $dispatchToEnterpriseModel->address['email'] ?? [],
      'dispatchTo_internet' => $dispatchToEnterpriseModel->address['internet'] ?? [],
      'dispatchTo_SITA' => $dispatchToEnterpriseModel->address['SITA'] ?? '',

      // untuk <address> diisi dengan address enterprise
      'dispatchFrom_enterpriseName' => $dispatchFromEnterpriseModel->name,
      'dispatchFrom_division' => $dispatchFromEnterpriseModel->remarks['division'] ?? '',
      'dispatchFrom_enterpriseUnit' => $dispatchFromEnterpriseModel->remarks['enterpriseUnit'] ?? '',
      'dispatchFrom_lastName' => $dispatchFromPersonModel->last_name,
      'dispatchFrom_firstName' => $dispatchFromPersonModel->first_name,
      'dispatchFrom_jobTitle' => $dispatchFromPersonModel->job_title,
      'dispatchFrom_department' => $dispatchFromEnterpriseModel->address['department'] ?? '',
      'dispatchFrom_street' => $dispatchFromEnterpriseModel->address['street'] ?? '',
      'dispatchFrom_postOfficeBox' => $dispatchFromEnterpriseModel->address['postOfficeBox'] ?? '',
      'dispatchFrom_postalZipCode' => $dispatchFromEnterpriseModel->address['postalZipCode'] ?? '',
      'dispatchFrom_city' => $dispatchFromEnterpriseModel->address['city'] ?? '',
      'dispatchFrom_country' => $dispatchFromEnterpriseModel->address['country'] ?? '',
      'dispatchFrom_state' => $dispatchFromEnterpriseModel->address['state'] ?? '',
      'dispatchFrom_province' => $dispatchFromEnterpriseModel->address['province'] ?? '',
      'dispatchFrom_building' => $dispatchFromEnterpriseModel->address['building'] ?? '',
      'dispatchFrom_room' => $dispatchFromEnterpriseModel->address['room'] ?? '',
      'dispatchFrom_phoneNumber' => $dispatchFromEnterpriseModel->address['phoneNumber'] ?? [],
      'dispatchFrom_faxNumber' => $dispatchFromEnterpriseModel->address['faxNumber'] ?? [],
      'dispatchFrom_email' => $dispatchFromEnterpriseModel->address['email'] ?? [],
      'dispatchFrom_internet' => $dispatchFromEnterpriseModel->address['internet'] ?? [],
      'dispatchFrom_SITA' => $dispatchFromEnterpriseModel->address['SITA'] ?? '',

      'deliveryListItemsFilename' => $this->get('deliveryListItemsFilename')
    ]);
  }

  protected function passedValidation()
  {
    $DDNModel = new Ddn();
    $DDNModel->create_xml($this->user()->storage, $this->validated());

    $this->merge([
      // harus array atau scalar, entah kenapa
      // Expected a scalar, or an array as a 2nd argument to \"Symfony\\Component\\HttpFoundation\\InputBag::set()\", \"Ptdi\\Mpub\\Main\\CSDBObject\" given.
      'CSDBObject' => [$DDNModel->CSDBObject],
    ]);
  }
}
