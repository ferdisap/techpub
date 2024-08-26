<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:template match="crew">
    <div class="crew">
      <h1 class="title">
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmTitle', //identAndStatusSection/dmAddress/descendant::dmTitle)" />
      </h1>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  
  <xsl:template match="descrCrew">
    <div class="descrCrew">
      <xsl:apply-templates/>
    </div>
  </xsl:template>
  
  <xsl:template match="crewDrill">
    <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::setLastPositionCrewDrillStep', 0)"/>
    <div>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </div>
  </xsl:template>
  
  <xsl:template match="title[parent::crewDrill]">
    <h4><xsl:apply-templates/></h4>
  </xsl:template>
  
  <!-- saat ini sama dengan <case> -->
  <xsl:template match="subCrewDrill">
  <xsl:variable name="lastCrewDrillStepNumber">
    <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getLastPositionCrewDrillStep')"/>
    </xsl:variable>
    <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::setLastPositionCrewDrillStep', 0)"/>
    <div>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </div>
    <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::setLastPositionCrewDrillStep', $lastCrewDrillStepNumber)"/>
  </xsl:template>
  
  <!-- jika ada if dan else, step selanjutnya akan dinomori sesuai jumlah step terakhir di if atau else -->
  <xsl:template match="crewDrillStep">
    <xsl:param name="memorizeStepFlag">
      <xsl:choose>
        <xsl:when test="@memorizeStepsFlag = '1'">
          <xsl:text>font-weight:bold</xsl:text>
        </xsl:when>
      </xsl:choose>
    </xsl:param>
    <xsl:param name="separatorStyle">
      <xsl:choose>
        <xsl:when test="@separatorStyle = 'line'">
          <xsl:text>- </xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>.</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:param>
    <xsl:param name="orderedStepsFlag">
      <xsl:choose>
        <xsl:when test="ancestor-or-self::*[@orderedStepsFlag][1]">
          <xsl:value-of select="ancestor-or-self::*[@orderedStepsFlag][1]/@orderedStepsFlag"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>0</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:param>
    <xsl:param name="seqnum"/>
    <xsl:variable name="num">
      <xsl:if test="$orderedStepsFlag = '1'">
        <xsl:variable name="qty" select="count(ancestor::*[@orderedStepsFlag = '1'])"/>
        <xsl:variable name="format">
          <xsl:if test="($qty mod 2)">
            <xsl:text>1</xsl:text>
          </xsl:if>
          <xsl:if test="not($qty mod 2)">
            <xsl:text>a</xsl:text>
          </xsl:if>
        </xsl:variable>
  
        <xsl:variable name="position">
          <xsl:choose>
            <xsl:when test="parent::if">
              <xsl:variable name="ifpos" select="count(parent::if/preceding-sibling::crewDrillStep)"/>
              <xsl:variable name="steppos"><xsl:number/></xsl:variable>
              <xsl:number format="{$format}" value="number($ifpos) + number($steppos)"/>
            </xsl:when>
            <xsl:when test="parent::elseIf">
              <xsl:variable name="elseifpos" select="count(parent::elseIf/preceding-sibling::crewDrillStep)"/>
              <xsl:variable name="steppos"><xsl:number/></xsl:variable>
              <xsl:number format="{$format}" value="number($elseifpos) + number($steppos)"/>
            </xsl:when>
            <xsl:when test="name(preceding-sibling::*[1]) = 'elseIf' or name(preceding-sibling::*[1]) = 'if'">
              <xsl:variable name="qtyPrev" select="count(preceding-sibling::*[1]/crewDrillStep)"/>
              <xsl:variable name="pos"><xsl:number/></xsl:variable>
              <xsl:number format="{$format}" value="$qtyPrev + $pos"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:number format="{$format}" value="php:function('Ptdi\Mpub\Main\CSDBObject::getLastPositionCrewDrillStep') + 1"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::setLastPositionCrewDrillStep', $position)"/>
  
        <!-- return -->
        <xsl:value-of select="$position"/>
      </xsl:if>
  
      <xsl:if test="$orderedStepsFlag = '0'">
        <xsl:text>-</xsl:text>
      </xsl:if>
    </xsl:variable>
  
    <table style="width:100%;page-break-inside: avoid;">
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <tr>
        <td style="width:15px;text-align:left"><xsl:value-of select="$num"/></td>
        <td style="text-align:left;">
          <xsl:apply-templates>
            <xsl:with-param name="memorizeStepFlag" select="$memorizeStepFlag"/>
            <xsl:with-param name="separatorStyle" select="$separatorStyle"/>
          </xsl:apply-templates>
        </td>
      </tr>
    </table>
  </xsl:template>
  
  <xsl:template match="if | elseIf">
    <div>
      <xsl:apply-templates/>
    </div>
  </xsl:template>
  
  <xsl:template match="challengeAndResponse">
    <xsl:param name="memorizeStepFlag"/>
    <xsl:param name="separatorStyle"/>
    <table style="width:100%;page-break-inside: avoid;">
      <tr>
        <td style="text-align:left">
            <span challenge="true" separator="{$separatorStyle}" style="{$memorizeStepFlag}">
              <xsl:apply-templates select="challenge"/>
            </span>
            <span response="true">
              <xsl:text>...</xsl:text>
              <xsl:apply-templates select="response"/>
            </span>
            <xsl:text> | </xsl:text>
            <span crewmember="true">
              <xsl:text>&#160;</xsl:text>
              <xsl:apply-templates select="descendant::crewMemberGroup"/>
            </span>
        </td>
      </tr>
    </table>
  </xsl:template>
  
  <xsl:template match="challenge">
    <xsl:param name="separator"/>
    <xsl:apply-templates/>
  </xsl:template>
  
  <xsl:template match="crewMemberGroup">
    <span captionline="true" calign="T" fillcolor="255,255,255" textcolor="0,0,0">
      <xsl:text>[</xsl:text>
      <xsl:value-of select="crewMember/@crewMemberType"/>
      <xsl:text>]</xsl:text>
    </span>
  </xsl:template>
  
  <xsl:template match="case">
    <xsl:variable name="lastCrewDrillStepNumber">
      <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getLastPositionCrewDrillStep')"/>
    </xsl:variable>
    <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::setLastPositionCrewDrillStep', 0)"/>
    <div>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </div>
    <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::setLastPositionCrewDrillStep', $lastCrewDrillStepNumber)"/>
  </xsl:template>
  
  <xsl:template match="caseCond">
    <br/>
    <span>
      <xsl:apply-templates/>
    </span>
  </xsl:template>
  
  <xsl:template match="crewProcedureName">
    <span><xsl:apply-templates/></span>
  </xsl:template>

</xsl:transform>

  
