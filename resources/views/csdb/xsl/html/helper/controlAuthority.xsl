<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:template name="controlAuthorityRefs">
    <xsl:if test="@controlAuthorityRefs">
      <xsl:attribute name="controlAuthorityRefs">
        <xsl:value-of select="@controlAuthorityRefs"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template name="controlAuthorityRefIds">
    <xsl:if test="@controlAuthorityRefIds">
      <xsl:attribute name="controlAuthorityRefIds">
        <xsl:value-of select="@controlAuthorityRefIds"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>
  
  <!-- 
    kurang lengkap. Disni belum dibuat untuk controlAuthorityGroup.
    Ini semacam applicability dimana attribute controlAuthorityRefs akan mencetak controlAuthority ref Id
   -->

</xsl:stylesheet>