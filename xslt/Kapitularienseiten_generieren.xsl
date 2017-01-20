<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet
    version="2.0"
    exclude-result-prefixes="tei"
    xmlns="http://www.tei-c.org/ns/1.0"
    xmlns:tei="http://www.tei-c.org/ns/1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output encoding="UTF-8" method="xml" indent="yes"/>

  <xsl:template match="//tei:item[@xml:id]">
    <xsl:variable name="url">
      <xsl:value-of select="tei:name/@ref"/>
    </xsl:variable>

    <xsl:if test="normalize-space ($url)">
      <xsl:result-document href="capit/{$url}.xml">

        <TEI xml:lang="ger" corresp="{$url}">
          <teiHeader>
            <fileDesc>
              <titleStmt>
                <title type="main" xml:lang="ger">
                  <xsl:text>"</xsl:text>
                  <xsl:value-of select="tei:name" />
                  <xsl:text>" [</xsl:text>
                  <xsl:value-of select="replace (@xml:id, '_', ' ')" />
                  <xsl:text>]</xsl:text>
                </title>
                <respStmt>
                  <persName key="KU">
                    <forename>Karl</forename>
                    <surname>Ubl</surname>
                  </persName>
                  <resp xml:lang="ger">Projektleitung</resp>
                  <resp xml:lang="eng">Project lead</resp>
                </respStmt>
                <respStmt>
                  <persName key="DT">
                    <forename>Dominik</forename>
                    <surname>Trump</surname>
                  </persName>
                  <resp xml:lang="ger">Aufbau der Kapitularienliste</resp>
                  <resp xml:lang="eng">Aggregation of list</resp>
                </respStmt>
                <respStmt>
                  <persName key="DS">
                    <forename>Daniela</forename>
                    <surname>Schulz</surname>
                  </persName>
                  <resp xml:lang="ger">Weiterverarbeitung und Transformation</resp>
                  <resp xml:lang="eng">transformation</resp>
                </respStmt>
                <funder>
                  Akademie der Wissenschaften und Künste Nordrhein-Westfalen
                </funder>
              </titleStmt>

              <publicationStmt>
                <publisher>
                  <persName>Karl Ubl</persName>
                  <orgName xml:lang="ger">
                    Historisches Institut, Lehrstuhl für Geschichte des Mittelalters, Universität zu
                    Köln
                  </orgName>
                  <orgName xml:lang="eng">
                    History Department, Chair for Medieval History, Cologne University
                  </orgName>
                  <address xml:lang="ger">
                    <addrLine>Albertus-Magnus-Platz</addrLine>
                    <addrLine>50923 <settlement>Köln</settlement></addrLine>
                    <addrLine>Philosophikum Raum 4.009</addrLine>
                    <addrLine>0221-470 2717</addrLine>
                    <country>Deutschland</country>
                  </address>
                  <address xml:lang="eng">
                    <addrLine>Albertus-Magnus-Platz</addrLine>
                    <addrLine>50923 <settlement>Cologne</settlement></addrLine>
                    <addrLine>Philosophikum Room 4.009</addrLine>
                    <addrLine>+49 221-470 2717</addrLine>
                    <country>Germany</country>
                  </address>
                </publisher>
                <availability>
                  <p xml:lang="ger">
                    Die Inhalte sind frei zugänglich und nicht-kommerziell. Einige Digitalisate und
                    Texte sind aus rechtlichen Gründen jedoch Mitarbeitern vorbehalten.
                  </p>
                  <p xml:lang="eng">
                    Content is freely available on a non-commercial basis. Some digital images and
                    texts are only available to staff due to copyright issues.
                  </p>
                </availability>
                <date when="2015-10-07">07.10.2015</date>
              </publicationStmt>
              <sourceDesc>
                <p>born digital</p>
              </sourceDesc>
            </fileDesc>

            <encodingDesc>
              <projectDesc>
                <p xml:lang="ger">
                  Capitularia. Edition der fränkischen Herrschererlasse: <ptr type="trl"
                  target="http://capitularia.uni-koeln.de/projekt/ueber-das-projekt/" />
                </p>
                <p xml:lang="eng">
                  Capitularia. Edition of the Frankish Capitularies: <ptr type="trl"
                  target="http://capitularia.uni-koeln.de/projekt/ueber-das-projekt/" />
                </p>
              </projectDesc>
            </encodingDesc>

            <revisionDesc>
              <change when="2015-09-13" who="Daniela Schulz">
                Erstellt aus der Kapitularienliste
              </change>
            </revisionDesc>
          </teiHeader>

          <text>
            <body>
              <div>
                <xsl:apply-templates select="tei:name" />
                <xsl:copy-of select="tei:note[@type='annotation']" />
                <xsl:copy-of select="tei:note[@type='titles']" />
                <xsl:copy-of select="tei:note[@type='date']" />
                <xsl:copy-of select="tei:list[@type='transmission']" />
                <xsl:copy-of select="tei:listBibl[@type='literature']" />
                <xsl:copy-of select="tei:listBibl[@type='translation']" />
              </div>
            </body>
          </text>
        </TEI>

      </xsl:result-document>
    </xsl:if>
  </xsl:template>

  <xsl:template match="tei:name">
    <head>
      <xsl:value-of select="replace (ancestor::tei:item/@xml:id, '_', ' Nr. ')" />
      <xsl:text>: </xsl:text>
      <xsl:apply-templates />
    </head>
  </xsl:template>

</xsl:stylesheet>
