<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on">

  <xsl:template match="dmodule | pm | dml">
    <xsl:if test="$configuration = 'ForIdentStatusVue'">
      <xsl:apply-templates select="identAndStatusSection"/>
    </xsl:if>
    <xsl:if test="$configuration = 'ContentPreview'">
      <html>
        <head>
          <title>Module</title>
          <meta name="csrf-token">
            <xsl:attribute name="content">
              <xsl:value-of select="$csrf_token"/>
            </xsl:attribute>
          </meta>
          <xsl:text disable-output-escaping="yes">
            {{ 
              $vite = Vite::useBuildDirectory(env('VITE_BUILD_DIR', 'build'))
                      ->withEntryPoints(['resources/css/csdb.css'])
            }}
          </xsl:text>
        </head>
        <body>
          <xsl:apply-templates select="content"/>
        </body>
      </html>
    </xsl:if>
  </xsl:template>


</xsl:transform>