<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl">

  <xsl:output method="xml" />

  <xsl:template match="note|warning|caution">
    <div>
      <xsl:attribute name="class">
        <xsl:value-of select="name()"/>
      </xsl:attribute>
      <xsl:call-template name="applicRefId"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="controlAuthorityRefIds"/>
      <xsl:call-template name="sc"/>
      <xsl:call-template name="internalRefId"/>
      <xsl:call-template name="vitalWarningFlag"/>
      <!-- di sini bisa pnaggil template 'gotoRef' untuk mencetak attribute v-on:click. Masukkan $internalRefTargetType dan @internalRefId -->
      <xsl:call-template name="warningType"/>
      <xsl:call-template name="cautionType"/>
      <xsl:call-template name="noteType"/>

      <div class="container">
        <div class="symbol">
          <xsl:choose>
            <xsl:when test="symbol/@infoEntityIdent">
              <xsl:apply-templates select="symbol"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:if test="name() = 'waring'">WARNING</xsl:if>
              <xsl:if test="name() = 'caution'">CAUTION</xsl:if>
              <xsl:if test="name() = 'note'">NOTE</xsl:if>
            </xsl:otherwise>
          </xsl:choose>
        </div>
        <xsl:apply-templates select="*[name() != 'symbol']"/>
      </div>
    </div>
  </xsl:template>

  <xsl:template match="notePara|warningAndCautionPara">
    <p>
      <xsl:attribute name="class">
        <xsl:value-of select="name()"/>
      </xsl:attribute>
      <xsl:call-template name="applicRefId"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="controlAuthorityRefIds"/>
      <xsl:call-template name="sc"/>

      <xsl:apply-templates/>
    </p>
  </xsl:template>

  <xsl:template name="warningType">
    <xsl:if test="@warningType">
      <xsl:attribute name="warningType">
        <xsl:value-of select="@warningType"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template name="cautionType">
    <xsl:if test="@cautionType">
      <xsl:attribute name="cautionType">
        <xsl:value-of select="@cautionType"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template name="noteType">
    <xsl:if test="@noteType">
      <xsl:attribute name="noteType">
        <xsl:value-of select="@noteType"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template name="vitalWarningFlag">
    <xsl:if test="@vitalWarningFlag">
      <xsl:attribute name="vitalWarningFlag">
        <xsl:value-of select="@vitalWarningFlag"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>