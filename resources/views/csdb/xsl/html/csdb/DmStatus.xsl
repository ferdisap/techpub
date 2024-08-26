<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:param name="filename" />

  <xsl:template match="dmStatus">
    <div class="dmStatus">
      <p>Below the status document: </p>
      <div class="flex space-x-2">
        <div class="border p-2">
          <b>Status</b>
          <ul>
            <li>Security: <xsl:value-of select="security/@securityClassification" /></li>
            <li>Responsible Company: <xsl:value-of select="responsiblePartnerCompany/enterpriseName" />-<xsl:value-of select="responsiblePartnerCompany/@enterpriseCode" /></li>
            <li>Originator Company: <xsl:value-of select="originator/enterpriseName" />-<xsl:value-of
                select="originator/@enterpriseCode" /></li>
            <li>Applicability Document: <a href="#">
                <xsl:value-of
                  select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', applicCrossRefTableRef/descendant::dmRefIdent, 'DMC-', '')" />
              </a>
            </li>
            <li>Applicability for: 
              <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', applic)"/>
            </li>
            <li>BREX Document: <a href="#">
                <xsl:value-of
                  select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', brexDmRef/descendant::dmRefIdent, 'DMC-', '')" />
              </a>
            </li>
            <li>
              Quality Assurance: <xsl:call-template name="getQualityAssurance" select="/"/>
            </li>
            <li>
              Remarks: <xsl:call-template name="getRemarks" select="/"/>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <!-- <div class="dmAddressItems"> 
      <p>Below is Address document: </p>
      <div class="flex space-x-2">
        <div class="border p-2">
          <b>Issue Date</b>
          <ul>
            <li>day: <xsl:value-of select="issueDate/@day"/></li>
            <li>month: <xsl:value-of select="issueDate/@month"/></li>
            <li>year: <xsl:value-of select="issueDate/@year"/></li>
          </ul>  
        </div>
        <div class="border p-2">
          <b>Title</b>
          <ul>
            <li>Tech Name: <xsl:value-of select="dmTitle/techName"/></li>
            <li>Info Name: <xsl:value-of select="dmTitle/infoName"/></li>
            <li>Info Name Variant: <xsl:value-of select="dmTitle/infoNameVariant"/></li>
          </ul>
        </div>
      </div>
    </div> -->
  </xsl:template>

  <xsl:template name="getQualityAssurance">
    <xsl:for-each select="qualityAssurance">
      <span>
        <xsl:value-of select="@applicRefId" />
      </span>
      <xsl:text>|</xsl:text>
      <span>
        <xsl:value-of select="name(child::*)" />
      </span>
      <xsl:text>|</xsl:text>
      <span>
        <xsl:value-of select="child::*/@verificationType" />
      </span>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="getRemarks">
    <xsl:variable name="remarks">
      <xsl:for-each select="remarks/simplePara">
          <xsl:value-of select="string(.)"/>
          <xsl:text>\r\n</xsl:text>
        </xsl:for-each>
      </xsl:variable>
      <xsl:value-of select="php:function('trim', $remarks, '\n\r')"/>    
  </xsl:template>

</xsl:transform>