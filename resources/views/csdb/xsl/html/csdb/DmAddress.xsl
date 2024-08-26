<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:include href="./DmIdent.xsl" />
  <xsl:include href="./DmAddressItems.xsl" />

  <xsl:template match="dmAddress">
    <div class="dmAddress">
      <xsl:apply-templates select="dmIdent"/>
      <xsl:apply-templates select="dmAddressItems"/>
    </div>
  </xsl:template>


</xsl:transform>