<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <xsl:template match="title[parent::levelledPara]">
    <!-- <xsl:param name="prefix"/> -->
    <xsl:param name="parentName" select="name(parent::*)"/>

    <xsl:for-each select="..">
      <xsl:if test="name()=$parentName">

        <!-- get the prefix (numberedPar) and determine the level title para -->
        <xsl:variable name="numberedPar">
          <xsl:call-template name="checkParent"/>
          <xsl:number/>
        </xsl:variable>

        <xsl:variable name="strLength">
          <xsl:variable name="l" select="php:function('preg_replace', '/\w+/', '?', $numberedPar)"/>
          <xsl:variable name="s" select="php:function('preg_replace', '/\./', '', $l)"/>
          <xsl:value-of select="string-length($s)"/>
        </xsl:variable>

        <xsl:variable name="h">
          <xsl:choose>
            <xsl:when test="$strLength = 1">h1</xsl:when>
            <xsl:when test="$strLength = 2">h2</xsl:when>
            <xsl:when test="$strLength = 3">h3</xsl:when>
            <xsl:when test="$strLength = 4">h4</xsl:when>
            <xsl:when test="$strLength = 5">h5</xsl:when>
            <xsl:otherwise>span</xsl:otherwise>
          </xsl:choose>
        </xsl:variable>

        <xsl:for-each select="child::title">
          <xsl:element name="{$h}">
            <xsl:call-template name="cgmark"/>
            <xsl:call-template name="controlAuthorityRefIds"/>
            <xsl:call-template name="sc"/>
            <xsl:attribute name="class">title</xsl:attribute>

            <!-- applying text -->
            <xsl:value-of select="$numberedPar"/>
            <xsl:text>.</xsl:text>
            <!-- <xsl:text>&#160;&#160;&#160;</xsl:text> -->
            <xsl:text>   </xsl:text>
            <xsl:apply-templates/>

          </xsl:element>
        </xsl:for-each>
      </xsl:if>
    </xsl:for-each>

  </xsl:template>
</xsl:stylesheet>