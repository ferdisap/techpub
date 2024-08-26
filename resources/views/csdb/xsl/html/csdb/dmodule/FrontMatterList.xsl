<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:template match="frontMatterList">
    <div class="frontMatterList">
      <xsl:attribute name="frontMatterType">
        <xsl:value-of select="@frontMatterType"/>
      </xsl:attribute>

      <xsl:apply-templates select="reducedPara" />
      <xsl:apply-templates select="frontMatterSubList" />

      <!-- footnote harus disesuaikan lagi dengan yang PDF -->
      <div class="footnote-container">
        <xsl:apply-templates select="footnote" />
      </div>
    </div>
  </xsl:template>

  <!-- fm02 LEODM -->
  <xsl:template match="frontMatterSubList[../@frontMatterType = 'fm02']">
    <div class="frontMatterSubList">
      <xsl:if test="title">
        <h1 class="title">
          <xsl:apply-templates select="title" />
        </h1>
      </xsl:if>
      <xsl:apply-templates select="reducedPara" />
      <table class="frontMatterEntryList">
        <thead>
          <tr>
            <th class="title">Title</th>
            <th class="code">DMC/PMC</th>
            <th class="issueType"></th>
            <th class="issueDate-issueNumber">Issue date/no.</th>
            <th class="numberOfPages">No. of pages</th>
            <th class="applicability">Applicable to</th>
            <th class="remarks">Remarks</th>
          </tr>
        </thead>
        <tbody>
          <xsl:apply-templates select="frontMatterDmEntry | frontMatterPmEntry | frontMatterExternalPubEntry">
            <xsl:with-param name="frontMatterType" select="ancestor::frontMatterList/@frontMatterType"/>
          </xsl:apply-templates>
        </tbody>
      </table>
    </div>
  </xsl:template>

  <!-- fm03 HIGLIGHTS -->
  <xsl:template match="frontMatterSubList[../@frontMatterType = 'fm03']">
    <div class="frontMatterSubList">
      <xsl:if test="title">
        <h1 class="title">
          <xsl:apply-templates select="title" />
        </h1>
      </xsl:if>
      <xsl:apply-templates select="reducedPara" />
      <table class="frontMatterEntryList">
        <thead>
          <tr>
            <th class="code">DMC/PMC</th>
            <th class="reason-for-update">Reason for update</th>
          </tr>
        </thead>
        <tbody>
          <xsl:apply-templates select="frontMatterDmEntry | frontMatterPmEntry | frontMatterExternalPubEntry">
            <xsl:with-param name="frontMatterType" select="ancestor::frontMatterList/@frontMatterType"/>
          </xsl:apply-templates>
        </tbody>
      </table>
    </div>
  </xsl:template>

  <!-- fm04 HIGH and UPDATING INSTRUCTION -->
  <xsl:template match="frontMatterSubList[../@frontMatterType = 'fm04']">
    <xsl:if test="position() = '1'">
      <div class="frontMatterSubList">
        <xsl:if test="title">
          <h1 class="title">
            <xsl:apply-templates select="title" />
          </h1>
        </xsl:if>
        <xsl:apply-templates select="reducedPara" />
        <table class="frontMatterEntryList">
          <thead>
            <tr>
              <th class="code">DMC/PMC</th>
              <th class="reason-for-update">Reason for update</th>
            </tr>
          </thead>
          <tbody>
            <xsl:apply-templates select="frontMatterDmEntry | frontMatterPmEntry | frontMatterExternalPubEntry">
              <xsl:with-param name="frontMatterType" select="ancestor::frontMatterList/@frontMatterType"/>
              <xsl:with-param name="tablename" select="'HIGH'"/>
            </xsl:apply-templates>
          </tbody>
        </table>
      </div>
    </xsl:if>
    <xsl:if test="position() = '2'">
      <div class="frontMatterSubList">
        <xsl:if test="title">
          <h1 class="title">
            <xsl:apply-templates select="title" />
          </h1>
        </xsl:if>
        <xsl:apply-templates select="reducedPara" />
        <table class="frontMatterEntryList">
          <thead>
            <tr>
              <th class="code">DMC/PMC</th>
              <th class="title">Title</th>
              <th class="issueType"></th>
              <th class="issueDate-issueNumber">Issue date/no.</th>
              <th class="numberOfPages">No. of pages</th>
              <th class="applicability">Applicable to</th>
            </tr>
          </thead>
          <tbody>
            <xsl:apply-templates select="frontMatterDmEntry | frontMatterPmEntry | frontMatterExternalPubEntry">
              <xsl:with-param name="frontMatterType" select="ancestor::frontMatterList/@frontMatterType"/>
              <xsl:with-param name="tablename" select="'UPDT'"/>
            </xsl:apply-templates>
          </tbody>
        </table>
      </div>
    </xsl:if>
  </xsl:template>

  <!-- fm0X UPDATING INSTRUCTION -->
  <xsl:template match="frontMatterSubList[../@frontMatterType = 'fm0X']">
    <div class="frontMatterSubList">
      <xsl:if test="title">
        <h1 class="title">
          <xsl:apply-templates select="title" />
        </h1>
      </xsl:if>
      <xsl:apply-templates select="reducedPara" />
      <table class="frontMatterEntryList">
        <thead>
          <tr>
            <th class="code">DMC/PMC</th>
            <th class="title">Title</th>
            <th class="issueType"></th>
            <th class="issueDate-issueNumber">Issue date/no.</th>
            <th class="numberOfPages">No. of pages</th>
            <th class="applicability">Applicable to</th>
          </tr>
        </thead>
        <tbody>
          <xsl:apply-templates select="frontMatterDmEntry | frontMatterPmEntry | frontMatterExternalPubEntry">
            <xsl:with-param name="frontMatterType" select="ancestor::frontMatterList/@frontMatterType"/>
          </xsl:apply-templates>
        </tbody>
      </table>
    </div>
  </xsl:template>

  <xsl:template match="frontMatterDmEntry">
    <xsl:param name="frontMatterType" />
    <xsl:param name="tablename"/> <!-- HIGH or UPDT-->
    <tr class="frontMatterDmEntry">
      <xsl:call-template name="applicRefId" />
      <!-- fm02 -->
      <xsl:if test="$frontMatterType = 'fm02'">
        <td class="title">
          <xsl:value-of
            select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmTitle', descendant::dmRefAddressItems/dmTitle)" />
        </td>
        <td class="code">
          <xsl:value-of
            select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmCode', descendant::dmRefIdent/dmCode)" />
        </td>
        <td class="issueType">
          <xsl:call-template name="issueTypeLEODM" />
        </td>
        <td class="issueDate issueNumber">
          <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', descendant::issueDate)" />, 
          <xsl:value-of select="descendant::issueInfo/@issueNumber" />
        </td>
        <td class="numberOfPages">
          <xsl:apply-templates select="numberOfPages" />
        </td>
        <td class="applicability">
          <xsl:call-template name="get_applicability" />
        </td>
        <td class="remarks">
          <xsl:apply-templates select="footnoteRemarks" />
        </td>
      </xsl:if>
      <!-- fm03 -->
      <xsl:if test="$frontMatterType = 'fm03'">
        <td class="code">
          <xsl:value-of
            select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmCode', descendant::dmRefIdent/dmCode)" />
        </td>
        <td
          class="reason-for-update">
          <xsl:apply-templates select="reasonForUpdate" />
        </td>
      </xsl:if>
      <!-- fm04 HIGH -->
      <xsl:if test="$frontMatterType = 'fm04' and $tablename = 'HIGH'">
        <td class="code">
          <xsl:value-of
            select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmCode', descendant::dmRefIdent/dmCode)" />
        </td>
        <td
          class="reason-for-update">
          <xsl:apply-templates select="reasonForUpdate" />
        </td>
      </xsl:if>
      <!-- fm04 UPDT -->
      <xsl:if test="$frontMatterType = 'fm04' and $tablename = 'UPDT'">
        <td class="code">
          <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmCode', descendant::dmRefIdent/dmCode)" />
        </td>
        <td class="title">
          <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmTitle', descendant::dmRefAddressItems/dmTitle)" />
        </td>
        <td class="issueType">
          <xsl:call-template name="issueTypeUPDT" />
        </td>
        <td class="issueDate issueNumber">
          <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', descendant::issueDate)" />, 
          <xsl:value-of select="descendant::issueInfo/@issueNumber" />
        </td>        
        <td class="numberOfPages">
          <xsl:apply-templates select="numberOfPages" />
        </td>
        <td class="applicability">
          <xsl:call-template name="get_applicability" />
        </td>
      </xsl:if>
    </tr>
  </xsl:template>

  <xsl:template match="frontMatterPmEntry">
    <xsl:param name="frontMatterType" />
    <tr class="frontMatterDmEntry">
      <!-- fm02 -->
      <xsl:if test="$frontMatterType = 'fm02'">
        <td class="title">
          <xsl:value-of
            select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmTitle', descendant::pmRefAddressItems/pmTitle)" />
        </td>
        <td class="code">
          <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmCode', descendant::pmRefIdent/pmCode)" />
          <!-- ini nanti ditambah identExtension if extended publication module code is used -->
        </td>
        <td
          class="issueType">
          <xsl:call-template name="issueTypeLEODM" />
        </td>
        <td class="issueDate issueNumber">
          <xsl:value-of
            select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', descendant::issueDate)" />, <xsl:value-of
            select="descendant::issueInfo/@issueNumber" />
        </td>
        <td class="numberOfPages">
          <xsl:apply-templates select="numberOfPages" />
        </td>
        <td class="applicability">
          <xsl:call-template name="get_applicability" />
        </td>
        <td class="remarks">
          <xsl:apply-templates select="footnoteRemarks" />
        </td>
      </xsl:if>
      <!-- fm03 -->
      <xsl:if test="$frontMatterType = 'fm03'">
        <td class="code">
          <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmCode', descendant::pmRefIdent/pmCode)" />
          <!-- ini nanti ditambah identExtension if extended publication module code is used -->
        </td>
        <td
          class="reason-for-update">
          <xsl:apply-templates select="reasonForUpdate" />
        </td>
      </xsl:if>
      <!-- fm04 -->
      <xsl:if test="$frontMatterType = 'fm04'">
        <td class="code">
          <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmCode', descendant::pmRefIdent/pmCode)" />
          <!-- ini nanti ditambah identExtension if extended publication module code is used -->
        </td>
        <td class="title">
          <xsl:value-of
            select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmTitle', descendant::pmRefAddressItems/pmTitle)" />
        </td>
        <td class="issueType">
          <xsl:call-template name="issueTypeUPDT" />
        </td>
        <td class="issueDate issueNumber">
          <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', descendant::issueDate)" />, 
          <xsl:value-of select="descendant::issueInfo/@issueNumber" />
        </td>        
        <td class="numberOfPages">
          <xsl:apply-templates select="numberOfPages" />
        </td>
        <td class="applicability">
          <xsl:call-template name="get_applicability" />
        </td>
      </xsl:if>
    </tr>
  </xsl:template>

  <!-- external pub entry -->
  <xsl:template match="frontMatterExternalPubEntry">
    <xsl:param name="frontMatterType" />
    <tr class="frontMatterExternalPubEntry">
      <!-- fm02 -->
      <xsl:if test="$frontMatterType = 'fm02'">
        <td class="title">
          <xsl:apply-templates select="externalPubRef/externalPubRefIdent/externalPubTitle" />
        </td>
        <td class="code">
          <xsl:apply-templates select="externalPubRef/externalPubRefIdent/externalPubCode" />
        </td>
        <td
          class="issueType">
          <xsl:call-template name="issueTypeLEODM" />
        </td>
        <td class="issueDate issueNumber">
          <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_externalPubIssueDate', descendant::externalPubIssueDate)" />
        </td>
        <td
          class="numberOfPages">
          <xsl:apply-templates select="numberOfPages" />
        </td>
        <td class="applicability">
          <xsl:call-template name="get_applicability" />
        </td>
        <td class="remarks">
          <xsl:apply-templates select="footnoteRemarks" />
        </td>
      </xsl:if>
      <!-- fm03 -->
      <xsl:if test="$frontMatterType = 'fm03'">
        <td class="code">
          <xsl:apply-templates select="externalPubRef/externalPubRefIdent/externalPubCode" />
        </td>
        <td
          class="reason-for-update">
          <xsl:apply-templates select="reasonForUpdate" />
        </td>
      </xsl:if>
      fm04
      <xsl:if test="$frontMatterType = 'fm04'">
        <td class="code">
          <xsl:apply-templates select="externalPubRef/externalPubRefIdent/externalPubCode" />
        </td>
        <td class="title">
          <xsl:apply-templates select="externalPubRef/externalPubRefIdent/externalPubTitle" />
        </td>
        <td class="issueType">
          <xsl:call-template name="issueTypeUPDT" />
        </td>
        <td class="issueDate issueNumber">
          <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_externalPubIssueDate', descendant::externalPubIssueDate)" />
        </td>
        <td class="numberOfPages">
          <xsl:apply-templates select="numberOfPages" />
        </td>
        <td class="applicability">
          <xsl:call-template name="get_applicability" />
        </td>
      </xsl:if>
    </tr>
  </xsl:template>

  <xsl:template name="issueTypeLEODM">
    <xsl:variable name="issueType"><xsl:value-of select="@issueType" /></xsl:variable>
    <xsl:choose>
      <xsl:when test="$issueType = 'new'">N</xsl:when>
      <xsl:when test="$issueType = 'revised'">C</xsl:when>
      <xsl:when test="$issueType = 'changed'">C</xsl:when>
      <xsl:when test="$issueType = 'status'">C</xsl:when>
      <xsl:when test="$issueType = 'reinstate-status'">N</xsl:when>
      <xsl:when test="$issueType = 'reinstate-changed'">N</xsl:when>
      <xsl:when test="$issueType = 'reinstate-revised'">N</xsl:when>
      <xsl:otherwise><xsl:value-of select="$issueType" /></xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="issueTypeUPDT">
    <xsl:variable name="issueType"><xsl:value-of select="@issueType" /></xsl:variable>
    <!-- <xsl:value-of select="ancestor-or-self::frontMatterDmEntry/@issueType"/> -->
    <xsl:choose>
      <xsl:when test="$issueType = 'new'">I</xsl:when>
      <xsl:when test="$issueType = 'revised'">R&#13;I</xsl:when>
      <xsl:when test="$issueType = 'changed'">R&#13;I</xsl:when>
      <xsl:when test="$issueType = 'deleted'">R</xsl:when>
      <xsl:when test="$issueType = 'status'">R&#13;I</xsl:when>
      <xsl:when test="$issueType = 'reinstate-status'">I</xsl:when>
      <xsl:when test="$issueType = 'reinstate-changed'">I</xsl:when>
      <xsl:when test="$issueType = 'reinstate-revised'">I</xsl:when>
      <xsl:otherwise><xsl:value-of select="$issueType" /></xsl:otherwise>
    </xsl:choose>
  </xsl:template>


</xsl:transform>