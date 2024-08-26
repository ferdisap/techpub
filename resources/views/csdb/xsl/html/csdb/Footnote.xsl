<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml" omit-xml-declaration="yes" />
  
  <!-- NOTE:
    1. element <footnoteRemarks> ini seperti element <para> yang ada textContentnya dan bisa mengandung <footnoteRef> yang didalamnya hanya ada attribute target.
    2. target nya <footnoteRef> adalah elemen <footnote>[@id].
    3. Pada frontmatterList, ada elemen <footnote> untuk ditaruh di bawah page. 
    4. di schema lain, element <footnote> juga bisa ditaruh didalam element <para>, tapi text nya tidak di render inline dengan para tapi tetap di render di bawah page
  -->

  <!-- <xsl:param name="dmOwner"/> -->
  <!-- ganti dmOwner ini nanti menjadi fungsi php -->

  <xsl:template match="footnote">
    <xsl:param name="usefootnote" select="'yes'"/>
    <xsl:choose>
      <xsl:when test="$usefootnote = 'no'">
        <xsl:variable name="fnt" select="."/>
        <xsl:for-each select="ancestor::table/descendant::footnote">
          <xsl:if test="child::* = $fnt/child::*">
            <a class="footnote" style="text-decoration:none">
              <xsl:attribute name="href"><xsl:text>#</xsl:text><xsl:value-of select="@id"/></xsl:attribute>
              <xsl:call-template name="id"/>
              <xsl:call-template name="applicRefId"/>
              <xsl:call-template name="cgmark"/>
              <xsl:attribute name="footnoteMark">
                <xsl:value-of select="@footnoteMark"/>
              </xsl:attribute>
              <xsl:call-template name="controlAuthorityRefs"/>
              <xsl:call-template name="sc"/>
              <sup>
                <xsl:call-template name="cgmark"/><xsl:value-of select="position()"/>
              </sup>
            </a>
          </xsl:if>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <!-- lupa tampilan pada PDF. Jika di ietm, span > p maka harus di set font-sizenya kecil (mungkin 6pt dan valign:text-bottom -->
        <span isfootnote="true" style="font-size:6;text-align:justify" class="footnote">
          <xsl:attribute name="id">
            <xsl:choose>
              <xsl:when test="@id">
                <xsl:value-of select="@id"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="generate-id()"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
          <xsl:call-template name="id"/>
          <xsl:call-template name="applicRefId"/>
          <xsl:call-template name="cgmark"/>
          <xsl:attribute name="footnoteMark">
            <xsl:value-of select="@footnoteMark"/>
          </xsl:attribute>
          <xsl:call-template name="controlAuthorityRefs"/>
          <xsl:call-template name="sc"/>
          <xsl:apply-templates />
        </span>
      </xsl:otherwise>
    </xsl:choose>

    <!-- <div class="footnote">
      <xsl:call-template name="id"/>
      <xsl:call-template name="applicRefId"/>
      <xsl:call-template name="cgmark"/>
      <xsl:attribute name="footnoteMark">
        <xsl:value-of select="@footnoteMark"/>
      </xsl:attribute>
      <xsl:call-template name="controlAuthorityRefs"/>
      <xsl:call-template name="sc"/>

      <xsl:apply-templates/>
    </div> -->
  </xsl:template>

  <xsl:template match="footnoteRemarks">
    <div class="footnoteRemarks">
      <xsl:call-template name="applicRefId"/>
      <xsl:call-template name="controlAuthorityRefs"/>
      <xsl:apply-templates/>
    </div>
  </xsl:template>
  
  <xsl:template match="footnoteRef">
    <xsl:param name="internalRefId" select="@internalRefId"/>
    <a class="footnote" style="text-decoration:none">
      <xsl:attribute name="href"><xsl:text>#</xsl:text><xsl:value-of select="$internalRefId"/></xsl:attribute>
      <xsl:variable name="pos">
        <xsl:for-each select="//footnote[@id = $internalRefId]">
          <xsl:number/>
        </xsl:for-each>
      </xsl:variable>
      <sup><xsl:value-of select="$pos"/></sup>
    </a>
  </xsl:template>

  <!-- ini sepertinya untuk PDF -->
  <!-- <xsl:template match="footnote">
    syaratnya, jangan tambah line-height di footnote ini, karena akan berdampak ke text
    selanjutnya yang bukan footnote
    Footnote text tidak bisa melebihi page height
      <xsl:param name="usefootnote" select="'yes'"/>
      <xsl:choose>
        <xsl:when test="$usefootnote = 'no'">
          <xsl:variable name="fnt" select="."/>
          <xsl:for-each select="ancestor::table/descendant::footnote">
            <xsl:if test="child::* = $fnt/child::*">
            <a style="text-decoration:none">
              <xsl:attribute name="href"><xsl:value-of select="'#dmOwner'"/>,<xsl:value-of select="@id"/></xsl:attribute>
              <sup>
                <xsl:call-template name="cgmark"/>
                <xsl:text>[</xsl:text><xsl:value-of select="position()"/><xsl:text>]&#160;</xsl:text>
              </sup>
            </a>
            </xsl:if>
          </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
          <span isfootnote="true" style="font-size:6;text-align:justify">
            <xsl:attribute name="id">
              <xsl:choose>
                <xsl:when test="@id">
                  <xsl:value-of select="@id"/>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:value-of select="generate-id()"/>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:attribute>
            <xsl:call-template name="id" />
            <xsl:call-template name="cgmark" />
            <xsl:apply-templates />
        </span>
        </xsl:otherwise>
      </xsl:choose>

  </xsl:template>

  <xsl:template match="footnoteRef">
    <xsl:param name="internalRefId" select="@internalRefId"/>
    <a style="text-decoration:none">
      <xsl:attribute name="href"><xsl:value-of select="'#dmOwner'"/>,<xsl:value-of select="$internalRefId"/></xsl:attribute>
      <xsl:variable name="pos">
        <xsl:for-each select="//footnote[@id = $internalRefId]">
          <xsl:number/>
        </xsl:for-each>
      </xsl:variable>
      <sup>[<xsl:value-of select="$pos"/>]</sup>
    </a>
  </xsl:template> -->

</xsl:stylesheet>