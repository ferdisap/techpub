<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
   
  <!-- <xsl:include href="part/caption.xsl"/> -->
  <xsl:output method="xml"/>

  <xsl:template match="reducedPara">
    <p class="reducedPara">
      <xsl:apply-templates/>
    </p>
  </xsl:template>

  <xsl:template match="reducedRandomList">
    <ul>
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="applicRefId"/>
      <xsl:call-template name="controlAuthorityRefIds"/>
      <xsl:call-template name="sc"/>
      <xsl:attribute name="listItemPrefix">
        <xsl:value-of select="@listItemPrefix"/>
      </xsl:attribute>

      <xsl:apply-templates select="reducedRandomListItem"/>
      
    </ul>         
  </xsl:template> 

  <xsl:template match="reducedRandomListItem">
    <li class="reducedRandomListItem">
      <xsl:apply-templates select="reducedListItemPara"/>
    </li>
  </xsl:template>

  <xsl:template match="reducedListItemPara">
    <p class="reducedListItemPara">
      <xsl:apply-templates/>
    </p>
  </xsl:template>



</xsl:stylesheet>