<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  
  <xsl:template match="para">
    <xsl:param name="usefootnote" select="'yes'"/>
    <!-- <xsl:variable name="tas">
      <xsl:value-of select="php:function('Ptdi\Mpub\CSDB::dump',name(parent::*))"/>
    </xsl:variable> -->
    <xsl:variable name="tagName">
      <xsl:choose>
        <xsl:when test="parent::footnote or parent::crewProcedureName or parent::challenge or parent::response or parent::controlAuthorityText">span</xsl:when>
        <xsl:otherwise>p</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:element name="{$tagName}">
      <xsl:attribute name="class">para</xsl:attribute>
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="applicRefId"/>
      <xsl:call-template name="controlAuthorityRefIds"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates>
        <xsl:with-param name="usefootnote" select="$usefootnote"/>
      </xsl:apply-templates>
    </xsl:element>
    <!-- <p class="para">
    </p> -->
  </xsl:template>

  <!-- <xsl:template match="para[parent::entry]">
    <p class="para">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="applicRefId"/>
      <xsl:call-template name="controlAuthorityRefIds"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates select="*[name(.) != 'footnote']"/>
    </p>
  </xsl:template> -->

  <!-- ini bekas PDF -->
  <!-- <xsl:template match="para">
    <xsl:param name="usefootnote" select="'yes'"/>
    <xsl:choose>
      pakai entity #ln; jika ingin new line
      <xsl:when test="parent::listItem or parent::listItemTerm or parent::listItemDefinition ">
        <span>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </span>
      </xsl:when>
      <xsl:when test="parent::footnote or parent::response or parent::crewProcedureName">
        <span>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </span>      
      </xsl:when>
      <xsl:when test="parent::challenge">
        <span>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </span>      
      </xsl:when>
      <xsl:when test="parent::entry">
        karena div kan tidak ada vertical space
        <div>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates>
            <xsl:with-param name="usefootnote" select="$usefootnote"/>
          </xsl:apply-templates>
        </div>      
      </xsl:when>
      <xsl:when test="parent::controlAuthorityText">
        <span>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </span>      
      </xsl:when>
      <xsl:otherwise>
        <p>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates>
            <xsl:with-param name="usefootnote" select="$usefootnote"/>
          </xsl:apply-templates> 
        </p>        
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template> -->
  
</xsl:stylesheet>