<?xml version="1.0" encoding="UTF-8"?>

<!-- dataRestriction belum dibuat -->

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:output method="html" media-type="text/html" omit-xml-declaration="yes" />  

  <xsl:template match="frontMatterTitlePage">
    <div class="frontMatterTitlePage">
      <xsl:apply-templates select="productIntroName"/>
      <xsl:apply-templates select="pmTitle"/>
      <xsl:apply-templates select="shortPmTitle"/>
      <div class="pmAddress">
        <xsl:apply-templates select="pmCode"/>
        <xsl:apply-templates select="issueInfo"/>
        <xsl:apply-templates select="issueDate"/>
        <span>Applicability to: <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', applic)"/></span>
      </div>
      <xsl:apply-templates select="productIllustration"/>
      <xsl:apply-templates select="dataRestrictions"/>
      <div class="externalPubCode">
        External Publication required to read:
        <br/>
        <xsl:for-each select="externalPubCode">
          <xsl:value-of select="@pubCodingScheme"/><xsl:text>:</xsl:text><xsl:apply-templates/> <xsl:text>   </xsl:text>
        </xsl:for-each>
      </div>
      <xsl:apply-templates select="productAndModel"/>
      <!-- dervative classification here -->
      <div class="manufacturer">
        <span>Manufacturer: </span>
        <div class="enterprise">
          <xsl:apply-templates select="enterpriseLogo"/>
          <xsl:apply-templates select="enterpriseSpec"/>
          <xsl:text>  </xsl:text>
        </div>
        <xsl:text>  </xsl:text>
      </div>
      <div class="publisher">
        <xsl:text>  </xsl:text>
        <span>Publisher: </span>
        <div class="enterprise">
          <xsl:apply-templates select="publisherLogo"/>
          <div class="responsiblePartnerCompany">
            <xsl:apply-templates select="responsiblePartnerCompany/enterpriseName"/>
            <xsl:text> </xsl:text>
          </div>
          <xsl:text> </xsl:text>
        </div>
      </div>
      <xsl:apply-templates select="security"/>
      <xsl:apply-templates select="barCode"/>
      <hr/>
      <xsl:apply-templates select="frontMatterInfo"/>
    </div>
  </xsl:template>
  
  <xsl:template match="productIntroName[ancestor::frontMatterTitlePage]">
    <h1 class="productIntroName">
      <xsl:apply-templates/>
    </h1>  
  </xsl:template>

  <xsl:template match="pmTitle[parent::frontMatterTitlePage]">
    <h1 class="pmTitle">
      <xsl:apply-templates/>
    </h1>
  </xsl:template>

  <xsl:template match="shortPmTitle[parent::frontMatterTitlePage]">
    <h2 class="shortPmTitle">
      <xsl:apply-templates/>
    </h2>
  </xsl:template>

  <xsl:template match="pmCode[parent::frontMatterTitlePage]">
    <h3 class="pmCode"><xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmCode', .)"/></h3>
  </xsl:template>

  <xsl:template match="issueInfo[parent::frontMatterTitlePage]">
    <div class="issueInfo">
      <h3>Issue No. <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueInfo', .)"/></h3>
    </div>
  </xsl:template>
  
  <xsl:template match="issueDate[parent::frontMatterTitlePage]">
    <div class="issueDate">
      <h3><xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', .)"/></h3>
    </div>
  </xsl:template>

  <xsl:template match="productAndModel[parent::frontMatterTitlePage]">
    <div class="productAndModel">
      <xsl:if test="productName">
        <h3 class="productName"><xsl:apply-templates class="productName"/></h3>
      </xsl:if>
      <xsl:for-each select="productModel">
        <div class="productModel">
          <span>Model: <xsl:apply-templates select="modelName"/></span>
          <xsl:text> </xsl:text>
          <span class="natoStockNumber">NSN<xsl:apply-templates select="natoStockNumber"/>,</span>
          <xsl:text> </xsl:text>
          <span class="identNumber">
            Manufacture Code: <xsl:apply-templates select="identNumber/manufacturerCode"/><xsl:text> </xsl:text><xsl:apply-templates select="identNumber/partAndSerialNumber"/>-<xsl:apply-templates select="endItemCode"/>
          </span>
          
        </div>
      </xsl:for-each>
    </div>
  </xsl:template>

  <xsl:template match="productIllustration">
    <div class="productIllustration">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="enterpriseSpec">
    <div class="enterpriseSpec">
      <div class="enterpriseName">
        <xsl:attribute name="code">
          <xsl:value-of select="enterpriseIdent/@manufacturerCodeValue"/>
        </xsl:attribute>
        <xsl:apply-templates select="enterpriseName"/>
        <xsl:text> </xsl:text>
      </div>
      <xsl:apply-templates select="businessUnit"/>
      <xsl:text> </xsl:text>
    </div>
  </xsl:template>

  <xsl:template match="businessUnit">
    <div class="businessUnit">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <div class="businessUnitName">
        <xsl:text> </xsl:text>
        <xsl:apply-templates select="businessUnitName"/>
      </div>
      <xsl:apply-templates select="businessUnitAddress"/>
      <div class="contactPerson">
        <xsl:for-each select="contactPerson">
          <xsl:text> </xsl:text>
          <xsl:apply-templates select="lastName"/>
          <xsl:if test="middleName">
            <xsl:text> </xsl:text>
            <xsl:apply-templates select="middleName"/>
          </xsl:if>
          <xsl:if test="firstName">
            <xsl:text> </xsl:text>
            <xsl:apply-templates select="firstName"/>
          </xsl:if>
          <xsl:if test="jobTitle">
            <xsl:text>, </xsl:text>
            <xsl:apply-templates select="jobTitle"/>
            <xsl:text>,</xsl:text>
          </xsl:if>
          <xsl:if test="contactPersonAddress">
            <xsl:text>, </xsl:text>
            <xsl:apply-templates select="contactPersonAddress"/>
          </xsl:if>
          <xsl:text>.</xsl:text>
        </xsl:for-each>
        <xsl:text> </xsl:text>
      </div>
      <xsl:text>   </xsl:text>
    </div>
  </xsl:template>

  <xsl:template match="businessUnitAddress">
    <div class="businessUnitAddress">
      <xsl:variable name="address">
        <xsl:if test="department">
          <xsl:text>Dept. </xsl:text><xsl:value-of select="department"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="street">
          <xsl:text>St. </xsl:text><xsl:value-of select="street"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:value-of select="city"/>
        <xsl:text>, </xsl:text>
        <xsl:value-of select="country"/>
        <xsl:text>, </xsl:text>
        <xsl:if test="state">
          <xsl:value-of select="state"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="province">
          <xsl:value-of select="province"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="building">
          <xsl:value-of select="building"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="room">
          <xsl:value-of select="room"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="postOfficeBox">
          <xsl:value-of select="postOfficeBox"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="postalZipCode">
          <xsl:value-of select="postalZipCode"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="phoneNumber">
          <xsl:text>Phone: </xsl:text>
          <xsl:for-each select="phoneNumber">
            <xsl:value-of select="phoneNumber"/>
            <xsl:text>, </xsl:text>
          </xsl:for-each>
        </xsl:if>
        <xsl:if test="faxNumber">
          <xsl:text>Fax: </xsl:text>
          <xsl:for-each select="faxNumber">
            <xsl:value-of select="faxNumber"/>
            <xsl:text>, </xsl:text>
          </xsl:for-each>
        </xsl:if>
        <xsl:if test="email">
          <xsl:text>Email: </xsl:text>
          <xsl:for-each select="email">
            <xsl:value-of select="email"/>
            <xsl:text>, </xsl:text>
          </xsl:for-each>
        </xsl:if>
        <xsl:if test="internet">
          <xsl:text>Web: </xsl:text>
          <xsl:for-each select="internet">
            <xsl:value-of select="internet"/>
            <xsl:text>, </xsl:text>
          </xsl:for-each>
        </xsl:if>
        <xsl:if test="SITA">
          <xsl:value-of select="SITA"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
      </xsl:variable>
      <xsl:value-of select="php:function('preg_replace', '/,\s?$/','',$address)"/>
      <xsl:text>.</xsl:text>
    </div>
  </xsl:template>

  <xsl:template match="enterpriseLogo">
    <div class="enterpriseLogo">
      <xsl:for-each select="symbol">
        <xsl:apply-templates/>
      </xsl:for-each>
      <xsl:text> </xsl:text>
    </div>
  </xsl:template>

  <xsl:template match="publisherLogo">
    <div class="publisherLogo">
      <xsl:for-each select="symbol">
        <xsl:apply-templates/>
      </xsl:for-each>
      <xsl:text> </xsl:text>
    </div>
  </xsl:template>

  <xsl:template match="barCode">
    <div class="barCode"><i>no barcode here.</i></div>
  </xsl:template>

  <xsl:template match="frontMatterInfo">
    <div>
      <xsl:attribute name="class">
        <xsl:text>frontMatterInfo</xsl:text>
        <xsl:text> </xsl:text>
        <xsl:value-of select="@frontMatterInfoType"/>
      </xsl:attribute>
      <xsl:if test="title">
        <h1 class="title"><xsl:value-of select="title"/></h1>
      </xsl:if>
      <xsl:for-each select="reducedPara">
        <xsl:apply-templates select="."/>
      </xsl:for-each>
    </div>
  </xsl:template>


  

</xsl:transform>