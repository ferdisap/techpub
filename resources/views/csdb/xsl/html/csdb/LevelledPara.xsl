<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <!-- <xsl:include href="para.xsl"/> -->
  <!-- <xsl:include href="title.xsl"/> -->
  <!-- <xsl:include href="figure.xsl"/> -->
  <!-- <xsl:include href="table.xsl"/> -->

  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <xsl:template match="levelledPara">
    <div class="levelledPara">
    <!-- <div style="border:1px solid red"> -->
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="applicRefId"/>
      <xsl:call-template name="controlAuthorityRefIds"/>
      <xsl:call-template name="sc"/>

      <xsl:variable name="numberedPar">
        <xsl:call-template name="checkParent"/>
        <xsl:number/>
      </xsl:variable>
      <xsl:variable name="level">
          <xsl:variable name="l" select="php:function('preg_replace', '/\w+/', '?', $numberedPar)"/>
          <xsl:variable name="s" select="php:function('preg_replace', '/\./', '', $l)"/>
          <xsl:value-of select="string-length($s)"/>
      </xsl:variable>
      <xsl:attribute name="level">
        <xsl:value-of select="$level"/>
      </xsl:attribute>
      <xsl:apply-templates/>
    </div>
  </xsl:template>


</xsl:stylesheet>