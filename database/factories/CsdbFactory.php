<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Csdb>
 */
class CsdbFactory extends Factory
{
  public static array $xmls = [];

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $file = $this->generateFile();
    self::$xmls[$file[0]] = $file[1];
    return $this->predefine(
      filename: $file[0],
      path: 'csdb',
      storage_id: 1,
      initiator_id: 1,
    );
  }

  public function predefine($filename, $path, $storage_id, $initiator_id) :array 
  {
    return [
      "filename" => $filename,
      "path" => $path,
      'storage_id' => $storage_id,
      'initiator_id' => $initiator_id,
    ];
  }

  /**
   * @return array
   */
  public function generateFile()
  {
    $prefix = "DMC";
    $random = fn($start, $end) => rand($start, $end);
    
    if($prefix){
      // DMC-MALE-A-15-00-01-00A-018A-A_000-01_EN-EN.xml
      $modelIdentCode = ["MALE", "N219", "CN235","AKS"][$random(0,3)];
      $systemDiffCode = (['A','B','C','D'])[$random(0,2)];
      $systemCode = str_pad($random(0,99), 2, '0', STR_PAD_LEFT);
      $subSystemCode = $random(0,9);
      $subSubSystemCode = $random(0,9);
      $assyCode = str_pad($random(0,99), 2, '0', STR_PAD_LEFT) . str_pad($random(0,10), 2, '0', STR_PAD_LEFT);
      $disassyCode = str_pad($random(0,99), 2, '0', STR_PAD_LEFT);
      $disassyCodeVariant = (['A','B','C'])[$random(0,2)];
      $infoCode = str_pad($random(0,999), 3, '0', STR_PAD_LEFT);
      $infoCodeVariant = (['A','B','C'])[$random(0,2)];
      $itemLocationCode = (['A','B','C','D'])[$random(0,3)];

      $issueNumber = '000';
      $inWork = '01';

      $languageIsoCode = 'EN';
      $countryIsoCode = 'US';
      
      $filename = $prefix . '-'.
      $modelIdentCode . '-'.
      $systemDiffCode . '-'.
      $systemCode . '-'.
      $subSystemCode . $subSubSystemCode . '-'.
      $assyCode . '-'.
      $disassyCode . $disassyCodeVariant . '-'.
      $infoCode . $infoCodeVariant . '-'.
      $itemLocationCode . "_" .
      $issueNumber . "-" .
      $inWork . "_" .
      $languageIsoCode . "-" .
      $countryIsoCode . '.xml';

      $xml = $this->generateDMCXmlString(
        modelIdentCode: $modelIdentCode,
        systemDiffCode: $systemDiffCode,
        systemCode: $systemCode,
        subSystemCode: $subSystemCode,
        subSubSystemCode: $subSubSystemCode,
        assyCode: $assyCode,
        disassyCode: $disassyCode,
        disassyCodeVariant: $disassyCodeVariant,
        infoCode: $infoCode,
        infoCodeVariant: $infoCodeVariant,
        itemLocationCode: $itemLocationCode,

        issueNumber: $issueNumber,
        inWork: $inWork,

        languageIsoCode: $languageIsoCode,
        countryIsoCode: $countryIsoCode,
      );

      return [$filename, $xml];
    }
  }

  /**
   * @return string
   */
  public function generateDMCXmlString(
    $modelIdentCode = '',
    $systemDiffCode = '',
    $systemCode = '',
    $subSystemCode = '',
    $subSubSystemCode = '',
    $assyCode = '',
    $disassyCode = '',
    $disassyCodeVariant = '',
    $infoCode = '',
    $infoCodeVariant = '',
    $itemLocationCode = '',
    $issueNumber = '000',
    $inWork = '01',
    $languageIsoCode = 'EN',
    $countryIsoCode = 'US',
  )
  {
    return <<<EOL
    <?xml version="1.0" encoding="utf-8"?>
    <!DOCTYPE dmodule []>
    <dmodule xsi:noNamespaceSchemaLocation="http://www.s1000d.org/S1000D_5-0/xml_schema_flat/brex.xsd" xmlns:dc="http://www.purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
      <identAndStatusSection>
        <dmAddress>
          <dmIdent>
            <dmCode modelIdentCode="{$modelIdentCode}" systemDiffCode="{$systemDiffCode}" systemCode="{$systemCode}" subSystemCode="{$subSystemCode}" subSubSystemCode="{$subSubSystemCode}" assyCode="{$assyCode}" disassyCode="{$disassyCode}" disassyCodeVariant="{$disassyCodeVariant}" infoCode="{$infoCode}" infoCodeVariant="{$infoCodeVariant}" itemLocationCode="{$itemLocationCode}"/>
            <language languageIsoCode="{$languageIsoCode}" countryIsoCode="{$countryIsoCode}"/>
            <issueInfo issueNumber="{$issueNumber}" inWork="{$inWork}"/>
          </dmIdent>
          <dmAddressItems>
            <issueDate year="2019" month="06" day="28"/>
            <dmTitle>
              <techName>S1000D</techName>
              <infoName>Business rules exchange</infoName>
            </dmTitle>
          </dmAddressItems>
        </dmAddress>
        <dmStatus issueType="new">
          <security securityClassification="01"/>
          <dataRestrictions>
            <restrictionInstructions>
              <dataDistribution>To be made available to all S1000D users.</dataDistribution>
              <exportControl>
                <exportRegistrationStmt>
                  <simplePara>Export of this data module to all countries that are the residence of
                    organizations that are users of S1000D is permitted. Storage of this data module
                    is to be at the discretion of the organization.</simplePara>
                </exportRegistrationStmt>
              </exportControl>
              <dataHandling>There are no specific handling instructions for this data
                module.</dataHandling>
              <dataDestruction>Users may destroy this data module in accordance with their own local
                procedures.</dataDestruction>
              <dataDisclosure>There are no dissemination limitations that apply to this data
                module.</dataDisclosure>
            </restrictionInstructions>
            <restrictionInfo>
              <copyright>
                <copyrightPara>
                  <emphasis>Copyright (C) 2019</emphasis> by each of the following organizations:
                  <randomList>   
                    <listItem> 
                      <para>AeroSpace and Defence Industries Associations of Europe - ASD.</para> 
                    </listItem> 
                    <listItem> 
                      <para>Ministries of Defence of the member countries of ASD.</para> 
                    </listItem> 
                  </randomList> 
                </copyrightPara> 
                <copyrightPara> 
                  <emphasis>Limitations of liability:</emphasis> 
                </copyrightPara> 
                <copyrightPara> 
                  <randomList> 
                    <listItem> 
                      <para>This material is provided "As is" and neither ASD nor any person who has contributed to the creation, revision or maintenance of the material makes any representations or warranties, express or implied, including but not limited to, warranties of merchantability or fitness for any particular purpose.</para> 
                    </listItem> 
                    <listItem> 
                      <para>Neither ASD nor any person who has contributed to the creation, revision or maintenance of this material shall be liable for any direct, indirect, special or consequential damages or any other liability arising from any use of this material.</para> 
                    </listItem> 
                    <listItem> 
                      <para>Revisions to this document may occur after its issuance. The user is responsible for determining if revisions to the material contained in this document have occurred and are applicable.</para> 
                    </listItem> 
                  </randomList> 
                </copyrightPara> 
              </copyright> 
              <policyStatement>S1000D-SC-2016-017-002-00 Steering Committee TOR</policyStatement> 
              <dataConds>There are no known conditions that would change the data restrictions for, or security classification of, this data module.</dataConds> 
            </restrictionInfo> 
          </dataRestrictions> 
          <responsiblePartnerCompany enterpriseCode="B6865"> 
            <enterpriseName>AeroSpace and Defence Industries Association of Europe - ASD</enterpriseName> 
          </responsiblePartnerCompany> 
          <originator enterpriseCode="B6865"> 
            <enterpriseName>AeroSpace and Defence Industries Association of Europe - ASD</enterpriseName> 
          </originator>
          <applic>
            <displayText>
              <simplePara>All</simplePara>
            </displayText>
          </applic>
          <brexDmRef>
            <dmRef>
              <dmRefIdent>
                <dmCode modelIdentCode="S1000D" systemDiffCode="G" systemCode="04" subSystemCode="1" subSubSystemCode="0" assyCode="0301" disassyCode="00" disassyCodeVariant="A" infoCode="022" infoCodeVariant="A" itemLocationCode="D"/>
                <issueInfo issueNumber="001" inWork="00"/>
              </dmRefIdent>
            </dmRef>
          </brexDmRef>
          <qualityAssurance>
            <firstVerification verificationType="tabtop"/>
          </qualityAssurance>
          <reasonForUpdate id="EPWG3" updateReasonType="urt01" updateHighlight="0">
            <simplePara>EPWG 86.09: Editorial changes.</simplePara>
            <simplePara>Unnecessary markup has been removed.</simplePara>
          </reasonForUpdate>
        </dmStatus>
      </identAndStatusSection>
      <content>
      </content>
    </dmodule>
    
    EOL;
  }
}
