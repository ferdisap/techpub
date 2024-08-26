<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <!-- cuma ROOT (container.xsl) yang bisa include -->

  <!-- <xsl:include href="./DmAddress.xsl"/> -->
  <!-- <xsl:include href="./DmStatus.xsl"/> -->


  <xsl:template match="identAndStatusSection">
    <div class="identAndStatusSection">
      <!-- Ident and Status Section -->
      <xsl:apply-templates select="dmAddress"/>
      <xsl:apply-templates select="dmStatus"/>
    </div>    
  </xsl:template>

</xsl:transform>