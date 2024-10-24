<?php

namespace App\Http\Requests\Csdb;

use App\Models\Csdb;
use App\Models\Csdb\Comment;
use App\Models\User;
use App\Rules\Csdb\BrexDmRef;
use App\Rules\Csdb\CommentRefs;
use App\Rules\Csdb\CommentType;
use App\Rules\Csdb\Language;
use App\Rules\Csdb\Path;
use App\Rules\Csdb\S1000DConfigurableAttributeValue;
use App\Rules\Csdb\SecurityClassification;
use App\Rules\Csdb\SeqNumber;
use App\Rules\EnterpriseCode;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ptdi\Mpub\Main\CSDBStatic;
use Illuminate\Support\Str;

/**
 * Dalam pembuatan comment, maximum comment dengan commentType = 'i' hanya 99x karena 3digit pertama adalah parentComment ('q') dan 2 digit terakhir adalah sequential
 * 
 * NOTE
 * - modelIdentCode didapat dari $commentRefs[0], atau $parentCommentFilename, atau $brexDmRef, atau null
 * - senderIdent didapat dari EnterpriseModel request user
 * - seqNumber digenerate otomatis
 * - commentType didapat dari $parentCommentFilename, atau client request
 * - languageIsoCode dan countryIsoCode didapat dari commentRefs[0], atau client request
 * - securityClassification didapat dari client request
 * - commentPriorityCode didapat dari client request
 * - responseType didapat dari client request
 * - brexDmRef didapat dari client request atau dari request csdb berisi filename, path, storage
 * - commentRefs didapat dari client request
 * 
 */
class CommentCreate extends FormRequest
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
      // ident
      'modelIdentCode' => 'required',
      'senderIdent' => [new EnterpriseCode(true)],
      // 'seqNumber' => [new SeqNumber(true, 'comment')], 
      'seqNumber' => [new SeqNumber(true, 'comment')], 
      'commentType' => ['required', new CommentType($this->parentCommentFilename)],
      'yearOfDataIssue' => '',
      'languageIsoCode' => ['required', new Language],
      'countryIsoCode' => ['required', new Language],

      // address
      'commentTitle' => 'max:50',
      'enterpriseName' => 'required',
      'division' => '',
      'enterpriseUnit' => '',
      'lastName' => 'required',
      'firstName' => '',
      'jobTitle' => '',
      'department' => '',
      'street' => '',
      'postOfficeBox' => '',
      'postalZipCode' => '',
      'city' => 'required',
      'country' => 'required',
      'state' => '',
      'province' => '',
      'building' => '',
      'room' => '',
      'phoneNumber' => '',
      'faxNumber' => '',
      'email' => '',
      'internet' => '',
      'SITA' => '',

      // status
      'securityClassification' => ['required', new SecurityClassification],
      'commentPriorityCode' => ['required', new S1000DConfigurableAttributeValue('cp')],
      'responseType' => ['required', new S1000DConfigurableAttributeValue('rt')],
      'brexDmRef' => ['required', new BrexDmRef],
      // 'brexDmRef' => ['required'], // untuk tes saja
      'commentRefs' => ['required', new CommentRefs],
      'commentRemarks' => ['array'],

      // content
      'commentContentSimplePara' => ['array'],
    ];
  }

  /**
   * Prepare the data for validation.
   */
  protected function prepareForValidation(): void
  {
    $brexDmRef = $this->get('brexDmRef');
    $commentCreator = $this->user();
    $creatorEnterpriseModel = $commentCreator->work_enterprise;
    $senderIdent = $creatorEnterpriseModel->code->name;

    // $brexModel = Csdb::getObject($this->get('brexDmRef'),['exception' => ['CSDB-DELL', 'CSDB-PDEL']])->first();
    // $modelIdentCode = $brexModel ? $brexModel->modelIdentCode : null;

    if (!$this->commentRefs || str_contains($this->commentRefs, 'noReferences')) $commentRefs = ['noReferences'];
    else {
      $commentRefs = explode(',', $this->commentRefs);
      array_walk($commentRefs, (fn (&$v) => $v = trim($v)));
      $commentRefs = array_unique($commentRefs);

      $objectReference = $commentRefs[0];
      $objectReferenceDecoded = CSDBStatic::decode_ident($objectReference);
      // first_key = ; //commentCode, dmCode, pmCode, etc
      $modelIdentCode = $objectReferenceDecoded[array_key_first($objectReferenceDecoded)]['modelIdentCode'];
      $languageIsoCode = $objectReferenceDecoded['language']['languageIsoCode'] ?? $this->get('languageIsoCode');
      $countryIsoCode = $objectReferenceDecoded['language']['countryIsoCode'] ?? $this->get('countryIsoCode');
    }

    $parentCommentFilename = $this->get('parentCommentFilename');
    $commentType = $this->get('commentType');
    $position = $this->get('position');
    // dd($parentCommentFilename, $commentType, $position);
    if ($parentCommentFilename && ($commentType === 'i' | $commentType === 'r')) {
      $parentCommentDecoded = CSDBStatic::decode_commentIdent($parentCommentFilename);
      $threeDigitFirst_seqNumber = substr($parentCommentDecoded['commentCode']['seqNumber'], 0, 3);
      $twoDigitLast_seqNumber = substr($parentCommentDecoded['commentCode']['seqNumber'], 3);
      if ($position) ($twoDigitLast_seqNumber = ((int) $twoDigitLast_seqNumber + (int) $position));
      $twoDigitLast_seqNumber = str_pad($twoDigitLast_seqNumber, 2, '0', STR_PAD_LEFT);
      $seqNumber = $threeDigitFirst_seqNumber . $twoDigitLast_seqNumber;
      $modelIdentCode = $modelIdentCode ?? $parentCommentDecoded['commentCode']['modelIdentCode'];
    } else {
      $seqNumber = DB::table(env('DB_TABLE_COM', 'comment'))->select('seqNumber')->orderBy('seqNumber', 'desc')->first()->seqNumber ?? '00000';
      $threeDigitFirst_seqNumber = substr($seqNumber,0,3);
      $threeDigitFirst_seqNumber++;
      $seqNumber = $threeDigitFirst_seqNumber . '00';
      $seqNumber = str_pad($seqNumber, 5, '0', STR_PAD_LEFT);
      $commentType = $this->get('commentType') ?? 'q';
    }
    
    if(!$brexDmRef && $this->csdb){
      $CSDBModel = Csdb::where('filename', $this->csdb['filename'])->where('path', $this->csdb['path'])->where('storage_id', User::where('storage', $this->csdb['storage'])->first(['id'])->id)->first();
      if($CSDBModel){
        $brexDmRef = $CSDBModel->object->brexDmRef;
      }
    }

    if(!isset($modelIdentCode) && $brexDmRef){
      $brexDecoded = CSDBStatic::decode_ident($brexDmRef);
      $modelIdentCode = $brexDecoded[array_key_first($brexDecoded)]['modelIdentCode'];
    }

    $this->merge([
      'path' => $this->get('path') ?? 'CSDB/COMMENTS',
      // ident
      'modelIdentCode' => $modelIdentCode ?? null,
      'senderIdent' => $senderIdent,
      'seqNumber' => $seqNumber,
      'commentType' => $commentType,
      'yearOfDataIssue' => date("Y"),
      'languageIsoCode' => $languageIsoCode ?? ($this->get('languageIsoCode') ?? null),
      'countryIsoCode' => $countryIsoCode ?? ($this->get('countryIsoCode') ?? null),

      // address
      'commentTitle' => $this->get('commentTitle'),
      'enterpriseName' => $creatorEnterpriseModel->name,
      'division' => $creatorEnterpriseModel->remarks['division'] ?? '',
      'enterpriseUnit' => $creatorEnterpriseModel->remarks['enterpriseUnit'] ?? '',
      'lastName' => $commentCreator->last_name,
      'firstName' => $commentCreator->first_name,
      'jobTitle' => $commentCreator->jobTitle,
      'department' => $creatorEnterpriseModel->address['department'] ?? '',
      'street' => $creatorEnterpriseModel->address['street'] ?? '',
      'postOfficeBox' => $creatorEnterpriseModel->address['postOfficeBox'] ?? '',
      'postalZipCode' => $creatorEnterpriseModel->address['postalZipCode'] ?? '',
      'city' => $creatorEnterpriseModel->address['city'] ?? '',
      'country' => $creatorEnterpriseModel->address['country'] ?? '',
      'state' => $creatorEnterpriseModel->address['state'] ?? '',
      'province' => $creatorEnterpriseModel->address['province'] ?? '',
      'building' => $creatorEnterpriseModel->address['building'] ?? '',
      'room' => $creatorEnterpriseModel->address['room'] ?? '',
      'phoneNumber' => $creatorEnterpriseModel->address['phoneNumber'] ?? [],
      'faxNumber' => $creatorEnterpriseModel->address['faxNumber'] ?? [],
      'email' => $creatorEnterpriseModel->address['email'] ?? [],
      'internet' => $creatorEnterpriseModel->address['internet'] ?? [],
      'SITA' => $creatorEnterpriseModel->address['SITA'] ?? '',

      // status
      'securityClassification' => $this->get('securityClassification'),
      'commentPriorityCode' => $this->get('commentPriorityCode'),
      'responseType' => $this->get('responseType'),
      'brexDmRef' => $brexDmRef,
      'commentContentSimplePara' => preg_split("/<br\/>|<br>|&#10;/m", $this->remarks),
      'commentRefs' => $commentRefs,
      'remarks' => $this->get('commentRemarks'),

      // content
      'commentContentSimplePara' => preg_split("/<br\/>|<br>|&#10;/m", $this->get('commentContentSimplePara')),
    ]);
  }

  protected function passedValidation()
  {
    $COMModel = new Comment();
    $COMModel->create_xml($this->user()->storage, $this->validated());

    $this->merge([
      // harus array atau scalar, entah kenapa
      // Expected a scalar, or an array as a 2nd argument to \"Symfony\\Component\\HttpFoundation\\InputBag::set()\", \"Ptdi\\Mpub\\Main\\CSDBObject\" given.
      'CSDBObject' => [$COMModel->CSDBObject],
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
