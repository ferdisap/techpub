<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:template name="sc">
    <xsl:if test="@securityClassification">
      <xsl:attribute name="sc">
        <xsl:value-of select="@securityClassification"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:if test="@commercialClassification">
      <xsl:attribute name="cc">
        <xsl:value-of select="@commercialClassification"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:if test="@derivativeClassificationRefId">
      <xsl:attribute name="dc">
        <xsl:value-of select="@derivativeClassificationRefId"/>
      </xsl:attribute>
    </xsl:if>
    <xsl:if test="@caveat">
      <xsl:attribute name="caveat">
        <xsl:value-of select="@caveat"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>