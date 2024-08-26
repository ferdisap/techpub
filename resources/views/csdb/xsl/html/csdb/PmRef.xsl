<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  
  <xsl:template match="pmRef[ancestor::para]">
    <xsl:variable name="ident">
      <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDB::resolve_pmIdent', ., 'PMC-', '')"/> 
    </xsl:variable>
    <a>
      <xsl:call-template name="cgmark"/>
      <xsl:attribute name="href"><xsl:value-of select="$ident"/>,<xsl:value-of select="@referredFragment"/></xsl:attribute>
      <xsl:value-of select="$ident"/>
      <xsl:text>&#160;</xsl:text>
      <xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_issueDate', pmRefAddressItems/issueDate)"/> 
    </a>
    <!-- jika ancestor bukan para, masih ada elemen security, title, responsible company, short pm title, dll -->
    <!-- <xsl:apply-templates select="dmRefAddressItems/pmTitle"/> -->
  </xsl:template>

</xsl:stylesheet>