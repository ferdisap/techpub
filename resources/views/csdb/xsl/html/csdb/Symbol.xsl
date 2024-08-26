<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <xsl:template match="symbol">
    <!-- <div> -->
      <!-- <xsl:copy-of select="unparsed-entity-uri(@infoEntityIdent)"/> -->
      <!-- <xsl:copy-of select="unparsed-entity-uri('ICN-0001Z-00010-001-01')"/> -->
      <!-- <xsl:copy-of select="php:function('preg_replace','/[\s\S]+(?=\/ICN)/', '', unparsed-entity-uri(@infoEntityIdent))"/> -->
    <!-- </div> -->
    <xsl:variable name="infoEntityIdent">
      <xsl:value-of select="$icnPath"/>
      <!-- <xsl:text>/images/</xsl:text> -->
      <xsl:value-of select="php:function('preg_replace','/[\s\S]+(?=\/ICN)/', '', unparsed-entity-uri(@infoEntityIdent))"/>
    </xsl:variable>
    <img src="{$infoEntityIdent}">
      <xsl:call-template name="cgmark"/>
      <xsl:if test="@reproductionWidth">
        <xsl:attribute name="width"><xsl:value-of select="@reproductionWidth"/></xsl:attribute>
      </xsl:if>
      <xsl:if test="@reproductionHeight">
        <xsl:attribute name="height"><xsl:value-of select="@reproductionHeight"/></xsl:attribute>
      </xsl:if>
    </img>
  </xsl:template>
  
</xsl:stylesheet>