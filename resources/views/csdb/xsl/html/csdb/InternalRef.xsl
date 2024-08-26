<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns:v-on="https://vuejs.org/on">

  <xsl:template name="internalRefId">
    <xsl:if test="@internalRefId">
      <xsl:attribute name="internalRefId">
        <xsl:value-of select="@internalRefId"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template match="internalRef">
    <a href="#">
      <xsl:call-template name="gotoRef">
        <xsl:param name="internalRefTargetType" select="@internalRefTargetType"/>
        <xsl:param name="internalRefId" select="@internalRefId"/>
      </xsl:call-template>
      <!-- <xsl:attribute name="click" namespace="https://vuejs.org/on">
        References.to(
          '<xsl:value-of select="@internalRefTargetType"/>',
          '<xsl:value-of select="@internalRefId"/>',
        )
      </xsl:attribute> -->
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="irtt"/>
    </a>
  </xsl:template>

  <xsl:template name="gotoRef">
    <xsl:param name="internalRefTargetType"/>
    <xsl:param name="internalRefId"/>
    <xsl:attribute name="click" namespace="https://vuejs.org/on">
        References.to(
          '<xsl:value-of select="$internalRefTargetType"/>',
          '<xsl:value-of select="$internalRefId"/>',
        )
    </xsl:attribute>
  </xsl:template>

  <!-- template named irtt di pindah ke irtt.xsl-->

</xsl:stylesheet>