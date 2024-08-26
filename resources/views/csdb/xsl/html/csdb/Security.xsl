<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:template match="security">
    <div class="security">
      <xsl:apply-templates select="@securityClassification"/>
      <xsl:text>  </xsl:text>
    </div>   
  </xsl:template>

  <xsl:template match="@securityClassification[parent::security]">
    <xsl:variable name="number">
      <xsl:value-of select="."/>      
    </xsl:variable>
    <span>
      <xsl:attribute name="class">
        <xsl:text>securityClassification-</xsl:text>
        <xsl:value-of select="$number"/>
      </xsl:attribute>
      <xsl:choose>
        <xsl:when test="$number = '05'">Top Secret</xsl:when>
        <xsl:when test="$number = '04'">Secret</xsl:when>
        <xsl:when test="$number = '03'">Confidential</xsl:when>
        <xsl:when test="$number = '02'">Restricted</xsl:when>
        <xsl:when test="$number = '01'">Unclassified</xsl:when>
      </xsl:choose>
    </span>
  </xsl:template>

</xsl:transform>