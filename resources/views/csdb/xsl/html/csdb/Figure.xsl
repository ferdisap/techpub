<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl">

  <xsl:output method="xml" omit-xml-declaration="yes" />

  <!-- <xsl:param name="absolute_path_csdbInput"></xsl:param> -->

  <xsl:template match="figure">
    <xsl:variable name="current" select="." />
    <xsl:variable name="figTitle">
      <xsl:value-of select="title" />
    </xsl:variable>
    <xsl:variable name="qtyGra">
      <xsl:value-of select="count((graphic))" />
    </xsl:variable>
    <xsl:variable name="index">
      <xsl:for-each select="//figure">
        <xsl:if test=". = $current">
          <xsl:value-of select="position()" />
        </xsl:if>
      </xsl:for-each>
    </xsl:variable>
    <xsl:variable name="figId">
      <xsl:value-of select="@id" />
    </xsl:variable>

    <div style="text-align:center;page-break-inside: avoid;" id="{$figId}">
      <xsl:call-template name="id" />
      <xsl:call-template name="cgmark" />


      <xsl:for-each select="graphic">
        <xsl:variable name="graIndex"><xsl:number /></xsl:variable>
        <xsl:variable name="infoEntityIdent">
          <xsl:value-of select="$icnPath"/>
          <xsl:value-of select="php:function('preg_replace','/[\s\S]+(?=\/ICN)/', '', unparsed-entity-uri(@infoEntityIdent))"/>
        </xsl:variable>
        
        <table style="text-align:center;margin-top:11pt">
          <tr>
            <td>
              <div class="flex justify-center">
                <img src="{$infoEntityIdent}" class="map">
                  <xsl:attribute name="v-on_click">References.icnDetail('<xsl:value-of select="$infoEntityIdent" />')</xsl:attribute>
                  <xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
                  <xsl:call-template name="cgmark" />
                </img>
              </div>
              <!-- untuk hotspot -->
              <!-- <xsl:call-template name="transformICNMetaFile">
                <xsl:with-param name="filename"
                  select="php:function('preg_replace', '/.\w+$/','', string(@infoEntityIdent))" />
              </xsl:call-template> -->
            </td>
          </tr>
          <tr>
            <td style="text-align:right">
              <span style="font-size:6" paddingleft="5">
                <xsl:value-of select="php:function('preg_replace', '/.[\w]+$/', '', string(@infoEntityIdent))" />
              </span>
            </td>
          </tr>
          <tr>
            <td>
              <span>
                <xsl:if test="parent::figure/title/@changeMark = '1'">
                  <xsl:call-template name="cgmark">
                    <xsl:with-param name="changeMark" select="parent::figure/title/@changeMark" />
                    <xsl:with-param name="changeType" select="parent::figure/title/@changeType" />
                    <xsl:with-param name="reasonForUpdateRefIds"
                      select="parent::figure/title/@reasonForUpdateRefIds" />
                  </xsl:call-template>
                </xsl:if>
                <xsl:text>Fig.&#160;</xsl:text>
                <xsl:value-of
                  select="$index" />&#160;<xsl:value-of select="$figTitle" />
              </span>
              <xsl:if test="$qtyGra > 1">
                <span>
                  <xsl:text>&#160;&#40;sheet&#160;</xsl:text>
                  <xsl:value-of select="$graIndex" />
                  <xsl:text>&#160;of&#160;</xsl:text>
                  <xsl:value-of select="$qtyGra" />
                  <xsl:text>&#41;</xsl:text>
                </span>
              </xsl:if>
            </td>
          </tr>
        </table>

        <!-- <xsl:apply-templates select="hotspot" /> -->
      </xsl:for-each>

    </div>
    <br />
  </xsl:template>

  <xsl:template name="transformICNMetaFile">
    <xsl:param name="filename" />
    <xsl:for-each select="php:function('Ptdi\Mpub\Main\CSDBStatic::document',/, string(@infoEntityIdent))//icnMetadataFile">
      <div class="icnMetadataFile hidden">
        <xsl:attribute name="id">
          <xsl:text>imf-</xsl:text>
        <xsl:value-of select="$filename" />
        </xsl:attribute>

        <!-- identAndStatusSection -->
        <div class="imfIdentAndStatusSection">
          <span class="imfIdent">
            <xsl:text>IMF-</xsl:text>
            <xsl:value-of select="//imfIdent/imfCode/@imfIdentIcn" />
            <xsl:text>_</xsl:text>
            <xsl:value-of select="//imfIdent/issueInfo/@issueNumber" />
            <xsl:text>-</xsl:text>
            <xsl:value-of select="//imfIdent/issueInfo/@inWork" />
          </span>
          <span class="title">
            <xsl:value-of select="//icnTitle" />
          </span>
          <span class="responsiblePartnerCompany">
            <xsl:value-of select="//responsiblePartnerCompany/enterpriseName" />
          </span>
          <span class="originator">
            <xsl:value-of select="//originator/enterpriseName" />
          </span>
        </div>

        <!-- content -->
        <div class="imfContent">
          <xsl:for-each select="//imfContent/icnVariation">
            <xsl:variable name="mapName">
              <xsl:value-of select="$filename" />
          <xsl:if test="@fileExtension">
                <xsl:text>.</xsl:text>
            <xsl:value-of select="@fileExtension" />
              </xsl:if>
            </xsl:variable>

        <xsl:for-each
              select="icnInfoItemGroup/icnInfoItem">
              <span class="{@icnInfoItemType}">
                <xsl:apply-templates select="simplePara" />
              </span>
            </xsl:for-each>

        <map
              name="{$mapName}">
              <xsl:for-each select="icnContents/icnObjectGroup/icnObject">
                <!-- sementara semua children icnObject diabaikan dulu -->
          <area
                  id="{@icnObjectIdent}" name="{@icnObjectName}" type="{@icnObjectType}"
                  alt="{@icnObjectTitle}" title="{@icnObjectTitle}" description="objectDescr"
                  shape="poly" coords="{@objectCoordinates}" />
              </xsl:for-each>
            </map>

        <xsl:for-each
              select="icnSupportingFiles">
              <xsl:apply-templates select="icnSourceFiles/icnSupportingFilesDescr" />
          <xsl:apply-templates
                select="icnSourceFiles/refs" />
          <xsl:apply-templates
                select="icnResourceFiles/icnSupportingFilesDescr" />
          <xsl:apply-templates
                select="icnResourceFiles/refs" />
            </xsl:for-each>

          </xsl:for-each>
        </div>
      </div>
    </xsl:for-each>
  </xsl:template>

  <!-- product illusration -->
  <xsl:template match="graphic[parent::productIllustration]">
    <xsl:variable name="infoEntityIdent">
      <xsl:value-of select="$icnPath"/>
      <xsl:value-of select="php:function('preg_replace','/[\s\S]+(?=\/ICN)/', '', unparsed-entity-uri(@infoEntityIdent))"/>
    </xsl:variable>
    <img class="graphic">
      <xsl:attribute name="src">
        <xsl:text>/icn/</xsl:text><xsl:value-of select="string($infoEntityIdent)" />
      </xsl:attribute>
    </img>
  </xsl:template>

</xsl:stylesheet>