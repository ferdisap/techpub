<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:template match="frontMatter">
    <div class="frontMatter">
      <xsl:if test="child::frontMatterList or child::frontMatterTableOfContent">
        <h1 class="title">
          <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmTitle', //identAndStatusSection/dmAddress/descendant::dmTitle)" />
        </h1>
        <h2 class="issueInfo">
          <xsl:text>Issue </xsl:text>
          <xsl:value-of select="//identAndStatusSection/dmAddress/descendant::dmIdent/issueInfo/@issueNumber" />
        </h2>
      </xsl:if>
      <xsl:apply-templates/>
    </div>
  </xsl:template>
  

</xsl:transform>