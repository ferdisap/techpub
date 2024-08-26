<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:template match="dataRestrictions">
    <div class="dataRestriction">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates select="restrictionInfo"/>
      <xsl:apply-templates select="restrictionInstructions"/>
    </div>
  </xsl:template>

  <xsl:template match="restrictionInfo">
    <div class="restrictionInfo">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="copyright">
    <div class="copyright">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <p class="copyrightPara">
        <xsl:apply-templates/>
      </p>
    </div>
  </xsl:template>

  <xsl:template match="policyStatement">
    <p class="policyStatement">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates/>
    </p>
  </xsl:template>

  <xsl:template match="dataConds">
    <p class="dataConds">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates/>
    </p>
  </xsl:template>

  <xsl:template match="restrictionInstructions">
    <div class="restrictionInstructions">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="dataDistribution">
    <div class="dataDistribution">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="exportControl">
    <div class="exportControl">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="dataHandling">
    <div class="dataHandling">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="dataHandling">
    <div class="dataHandling">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="dataDisclosure">
    <div class="dataDisclosure">
      <xsl:call-template name="cgmark"/>
      <xsl:call-template name="id"/>
      <xsl:call-template name="sc"/>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

</xsl:transform>