<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <xsl:output method="xml"/>
  
  <xsl:template match="sequentialList">
    <br/>
    <xsl:if test="title">
      <span><br/><b><xsl:value-of select="title"/></b></span>
    </xsl:if>
    <ol class="sequentialList">
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates />
    </ol>         
  </xsl:template> 

  <xsl:template match="randomList">
    <br/>
    <xsl:if test="title">
      <span><br/><b><xsl:value-of select="title"/></b></span>
    </xsl:if>
    <ul class="randomList">
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </ul>         
  </xsl:template> 
  
  <xsl:template match="listItem">
    <li class="listItem">
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </li>
  </xsl:template>
  
  <xsl:template match="definitionList">
    <br/>
    <dl class="definitionList">
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates select="definitionListItem"/>
    </dl>
  </xsl:template>

  <xsl:template match="definitionListItem">
    <div class="definitionListItem">
      <dt>
        <b><xsl:apply-templates select="listItemTerm"/></b>
        <br/>
      </dt>
      <dd>
        <xsl:apply-templates select="listItemDefinition"/>
        <br/>
      </dd>
    </div>
  </xsl:template>



</xsl:stylesheet>