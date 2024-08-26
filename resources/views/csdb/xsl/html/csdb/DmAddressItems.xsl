<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:param name="filename" />

  <xsl:template match="dmAddressItems">
    <div class="dmAddressItems"> 
      <p>Below is Address document: </p>
      <div class="flex space-x-2">
        <div class="border p-2">
          <b>Issue Date</b>
          <ul>
            <li>day: <xsl:value-of select="issueDate/@day"/></li>
            <li>month: <xsl:value-of select="issueDate/@month"/></li>
            <li>year: <xsl:value-of select="issueDate/@year"/></li>
          </ul>  
        </div>
        <div class="border p-2">
          <b>Title</b>
          <ul>
            <li>Tech Name: <xsl:value-of select="dmTitle/techName"/></li>
            <li>Info Name: <xsl:value-of select="dmTitle/infoName"/></li>
            <li>Info Name Variant: <xsl:value-of select="dmTitle/infoNameVariant"/></li>
          </ul>
        </div>
      </div>
    </div>
  </xsl:template>

</xsl:transform>