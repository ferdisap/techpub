<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:template name="applicRefId">
    <xsl:if test="@applicRefId">
      <xsl:attribute name="applicRefId">
        <xsl:value-of select="@applicRefId"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <!-- ini mungkin nanti harus dibuat. Saat ini belum diperlukan -->
  <!-- <xsl:template name="applic"></xsl:template> -->

  <xsl:template name="get_applicability">
    <xsl:param name="keepOneByOne" select="boolean(0)"/>
    <xsl:param name="useDisplayName" select="boolean(1)"/>
    <xsl:param name="useDisplayText" select="number(2)"/>
    <xsl:variable name="applicRefId"><xsl:value-of select="@applicRefId"/></xsl:variable>
    <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', //applic[@id = $applicRefId], $keepOneByOne, $useDisplayName, $useDisplayText)"/>
  </xsl:template>
</xsl:transform>