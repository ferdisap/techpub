<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind" xmlns:v-on="https://vuejs.org/on" xmlns:v-show="https://vuejs.org/show">

  <xsl:output method="html" media-type="text/html" omit-xml-declaration="yes"/>
  <xsl:param name="filename"/>
  
  <xsl:template match="dml">
    <xsl:apply-templates select="identAndStatusSection"/>
    <xsl:apply-templates select="dmlContent"/>
    <SearchDialog v-show="showDialog" v-bind:callback="dialogCallback"/>
  </xsl:template>

  <xsl:template match="identAndStatusSection">
    <div class="dmlIdentAndStatusSection">
      <h1>IDENTIFICATION AND STATUS SECTION</h1>
      <table>
        <!-- dmlAddress -->
        <tr>
          <td>DML Code:</td>
          <td><xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_dmlCode', //dmlCode)"/></td>
        </tr>
        <tr>
          <td>Issue Number:</td>
          <td><xsl:value-of select="//dmlIdent/issueInfo/@issueNumber"/></td>
        </tr>
        <tr>
          <td>InWork Number:</td>
          <td><xsl:value-of select="//dmlIdent/issueInfo/@inWork"/></td>
        </tr>
        <tr>
          <td>Issue Date:</td>
          <td><xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_issueDate', //issueDate)"/></td>
        </tr>
      
        <!-- dmlStatus -->
        <tr>
          <td>Security Classification:</td>
          <td>
            <input name="ident-securityClassification" value="{dmlStatus/security/@securityClassification}"/>
          </td>
        </tr>
        <tr>
          <td>Brex DM Ref:</td>
          <td>
            <input name="ident-brexDmRef" value="{php:function('Ptdi\Mpub\CSDB::resolve_dmIdent', //dmlStatus/descendant::brexDmRef/dmRef/dmRefIdent)}"/>
          </td>
        </tr>
        <tr>
          <td>Remarks:</td>
          <td>
            <xsl:variable name="remarks">
              <xsl:for-each select="//dmlStatus/remarks/simplePara">
                <xsl:value-of select="string(.)"/>
                <xsl:text>\r\n</xsl:text>
              </xsl:for-each>
            </xsl:variable>
            <textarea name="ident-remarks">
              <xsl:value-of select="php:function('trim', $remarks, '\n\r')"/>
            </textarea>
          </td>
        </tr>
      </table>
    </div>
  </xsl:template>

  <xsl:template match="dmlContent">
    <hr/>
    <h1>DML CONTENT</h1>
    <table class="dmlContent">
      <tr>
        <th> Ident Code <Sort emitname="dmlEntry_sorted"/> </th>
        <th> Type <Sort emitname="dmlEntry_sorted"/> | Issue Type <Sort emitname="dmlEntry_sorted"/> </th>
        <th> Security <Sort emitname="dmlEntry_sorted"/> </th>
        <th> Resposible Company <br/> (name <Sort emitname="dmlEntry_sorted"/> | code <Sort emitname="dmlEntry_sorted"/>)</th>
        <th> Answer <Sort emitname="dmlEntry_sorted"/> </th>
        <th> Remarks <Sort emitname="dmlEntry_sorted"/> </th>
      </tr>
      <DmlEntryForm>
      <xsl:for-each select="dmlEntry">
        <tr class="dmlEntry">
          <td class="dmlEntry-ident">
            <textarea name="entryIdent[]" class="w-full" v-on:click="$parent.searchDialog($event,true)">
              <xsl:apply-templates select="dmRef | pmRef | infoEntityRef | commentRef | dmlRef"/>
            </textarea>
            <div class="text-red-600 text-sm error">
              <xsl:attribute name="v-html">store.error('entryIdent.<xsl:value-of select="position()-1"/>')</xsl:attribute>
            </div>
          </td>
          <td>
            <input class="dmlEntry-dmlEntryType w-2/5" name="dmlEntryType[]" value="{@dmlEntryType}"/> | <input class="dmlEntry-issueType w-2/5" name="issueType[]" value="{@issueType}"/>
            <div class="text-red-600 text-sm error">
              <xsl:attribute name="v-html">store.error('dmlEntryType.<xsl:value-of select="position()-1"/>', 'issueType.<xsl:value-of select="position()-1"/>')</xsl:attribute>
            </div>
          </td>
          <td>
            <input class="dmlEntry-securityClassification w-full" name="securityClassification[]" value="{security/@securityClassification}"/>
            <div class="text-red-600 text-sm error">
              <xsl:attribute name="v-html">store.error('securityClassification.<xsl:value-of select="position()-1"/>')</xsl:attribute>
            </div>
          </td>
          <td class="responsibleCompany">
            <input class="dmlEntry-enterpriseName w-2/5" name="enterpriseName[]" value="{responsiblePartnerCompany/enterpriseName}"/> | <input class="dmlEntry-enterpriseCode w-2/5" name="enterpriseCode[]" value="{responsiblePartnerCompany/@enterpriseCode}"/>
            <div class="text-red-600 text-sm error">
              <xsl:attribute name="v-html">store.error('enterpriseName.<xsl:value-of select="position()-1"/>', 'enterpriseCode.<xsl:value-of select="position()-1"/>')</xsl:attribute>
            </div>
          </td>
          <td>-</td>
          <td>
            <textarea name="remarks[]" class="w-full">
              <xsl:apply-templates select="remarks/simplePara"/>
            </textarea>
            <div class="text-red-600 text-sm error">
              <xsl:attribute name="v-html">store.error('remarks.<xsl:value-of select="position()-1"/>')</xsl:attribute>
            </div>
          </td>
        </tr>
      </xsl:for-each>  
      </DmlEntryForm>
    </table>
    <div class="text-red-600 text-sm error">
      <xsl:attribute name="v-html">store.error('entryIdent')</xsl:attribute>
    </div>
    <div class="add-remove_button_container">
      <button class="material-icons" type="button" v-on:click="emitter.emit('add_dmlEntry')">add</button>
      <button class="material-icons" type="button" v-on:click="emitter.emit('add_comment')">add_comment</button>
      <button class="material-icons" type="button" v-on:click="emitter.emit('remove_dmlEntry')">delete</button>
    </div>
  </xsl:template>

  <xsl:template match="dmRef">
    <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', dmRefIdent, null, 'DMC-', '')"/>
  </xsl:template>
  
  <xsl:template match="pmRef">
    <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmIdent', pmRefIdent, null, 'PMC-', '')"/>
  </xsl:template>

  <xsl:template match="infoEntityRef">
    <xsl:value-of select="@infoEntityRefIdent"/>
  </xsl:template>

  <xsl:template match="commentRef">
    <!-- <span>Belum ada fungsi untuk resolve commentRefIdent</span> -->
  </xsl:template>

  <xsl:template match="dmlRef">
    <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmlIdent', dmlRefIdent, null, 'DML-', '' )"/>
  </xsl:template>
</xsl:transform>