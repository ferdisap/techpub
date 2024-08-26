<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:template match="externalPubRef">
    <span class="externalPubRef"><xsl:apply-templates/></span>
  </xsl:template>

  <xsl:template match="externalPubRefIdent">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="externalPubCode">
    <span class="externalPubCode">
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template match="externalPubTitle[ancestor::frontMatterExternalPubEntry]">
    <span class="externalPubTitle">
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template match="externalPubTitle">
    <h1 class="externalPubTitle">
      <xsl:apply-templates/>
    </h1>
    
  </xsl:template>
  
  <xsl:template match="externalPubIssueInfo">
    <span class="externalPubIssueInfo">
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template match="externalPubIssue">
    <span class="externalPubIssue">
      <xsl:apply-templates/>
    </span>
  </xsl:template>


</xsl:transform>