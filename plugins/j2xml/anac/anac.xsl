<?xml version="1.0" encoding="UTF-8"?>
<!--
/** 
 * @package		J2XML
 * @subpackage	plg_j2xml_anac
 * 
 * @version		3.8.3
 * @since		3.0
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2016 - 2019 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML  is free software. This version may have been modified pursuant to the
 * GNU  General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open 
 * source software licenses.
 */
-->
<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:legge190="legge190_1_0"
	>
<xsl:output
	method="xml"
	cdata-section-elements="title alias introtext fulltext attribs metadata name language"
	encoding="UTF-8"
	indent="yes"
	/>

<xsl:template match="/">
<j2xml version="15.9.0">
<content>
	<id>0</id>
	<title><xsl:value-of select="/legge190:pubblicazione/metadata/abstract"/></title>
	<catid>uncategorised</catid>
	<alias><xsl:call-template name="normalize-alias"><xsl:with-param name="s" select="/legge190:pubblicazione/metadata/abstract"></xsl:with-param></xsl:call-template></alias>
	<introtext>
&lt;div id="anac_toc">
&lt;table>
&lt;thead>&lt;tr>
&lt;th class="anac_toc_oggetto">Oggetto&lt;/th>
&lt;th class="anac_toc_cig">CIG&lt;/th>
&lt;th class="anac_toc_importo">Importo agg.&lt;/th>
&lt;th class="anac_toc_durata">Durata lavori&lt;/th>
&lt;th class="anac_toc_modalita">Modalita affidamento&lt;/th>
&lt;/tr>
&lt;/thead>
&lt;tbody>
<xsl:apply-templates select="/legge190:pubblicazione/data/lotto" mode="toc"/>
&lt;/tbody>
&lt;/table>
&lt;/div>
<xsl:apply-templates select="/legge190:pubblicazione/data/lotto" mode="content"/>
	</introtext>
	<fulltext/>
	<state>0</state>
	<created>
		<xsl:choose>
			<xsl:when test="legge190:pubblicazione/metadata/dataPubbicazioneDataset">
				<xsl:value-of select="legge190:pubblicazione/metadata/dataPubbicazioneDataset"/>
			</xsl:when>
			<xsl:when test="legge190:pubblicazione/metadata/dataPubblicazioneDataset">
				<xsl:value-of select="legge190:pubblicazione/metadata/dataPubblicazioneDataset"/>
			</xsl:when>
			<xsl:otherwise>
				0000-00-00 00:00:00
			</xsl:otherwise>
		</xsl:choose>
	</created>
	<created_by/>
	<created_by_alias><xsl:value-of select="legge190:pubblicazione/metadata/entePubblicatore"/></created_by_alias>
	<modified><xsl:value-of select="legge190:pubblicazione/metadata/dataUltimoAggiornamentoDataset"/></modified>
	<modified_by/>
	<publish_up>0000-00-00 00:00:00</publish_up>
	<publish_down>0000-00-00 00:00:00</publish_down>
	<images><![CDATA[{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}]]></images>
	<urls><![CDATA[{"urla":null,"urlatext":"dataset","targeta":"","urlb":null,"urlbtext":"","targetb":"","urlc":null,"urlctext":"","targetc":""}]]></urls>
	<attribs>{}</attribs>
	<version>1</version>
	<ordering>0</ordering>
	<metakey></metakey>
	<metadesc></metadesc>
	<access>1</access>
	<hits>0</hits>
	<metadata><![CDATA[{"robots":"","author":"","rights":"","xreference":""}]]></metadata>
	<language><![CDATA[*]]></language>
	<xreference></xreference>
	<featured>0</featured>
	<rating_sum>0</rating_sum>
	<rating_count>0</rating_count>
</content>
</j2xml>
</xsl:template>

<xsl:template match="lotto" mode="toc">
&lt;tr>
	&lt;td class="anac_toc_oggetto">&lt;a href="#" onclick="jQuery('#anac_toc').hide();jQuery('#anac_<xsl:value-of select='generate-id(.)'/>').show();">
	<xsl:value-of select="oggetto"/>&lt;/a>&lt;/td>
	&lt;td class="anac_toc_cig"><xsl:value-of select="cig"/>&lt;/td>
	&lt;td class="anac_toc_importo"><xsl:value-of select="importoAggiudicazione"/>&lt;/td>
	&lt;td class="anac_toc_durata">
		<xsl:value-of select="tempiCompletamento/dataInizio"/> - <xsl:value-of select="tempiCompletamento/dataUltimazione"/>
	&lt;/td>
	&lt;td class="anac_toc_modalita"><xsl:value-of select="sceltaContraente"/>&lt;/td>
&lt;/tr>
</xsl:template>

<xsl:template match="lotto" mode="content">
&lt;div class="anac_content" style="display:none" id="anac_<xsl:value-of select='generate-id(.)'/>">
&lt;a href="#" onclick="jQuery('#anac_toc').show();jQuery('#anac_<xsl:value-of select='generate-id(.)'/>').hide();">Chiudi&lt;/a>
&lt;table>
&lt;tr>
	&lt;td class="anac_content_title">CIG&lt;/td>
	&lt;td class="anac_content_value"><xsl:value-of select="cig"/>&lt;/td>
&lt;/tr>
&lt;tr>
	&lt;td class="anac_content_title">Struttura proponente&lt;/td>
	&lt;td class="anac_content_value">
		<xsl:value-of select="strutturaProponente/denominazione"/>&lt;br/>
		<xsl:value-of select="strutturaProponente/codiceFiscaleProp"/>
	&lt;/td>
&lt;/tr>
&lt;tr>
	&lt;td class="anac_content_title">Oggetto del bando&lt;/td>
	&lt;td class="anac_content_value">
		<xsl:value-of select="oggetto"/>
	&lt;/td>
&lt;/tr>
&lt;tr>
	&lt;td class="anac_content_title">Procedura di scelta del contraente&lt;/td>
	&lt;td class="anac_content_value">
		<xsl:value-of select="sceltaContraente"/>
	&lt;/td>
&lt;/tr>
&lt;tr>
	&lt;td class="anac_content_title">Importo di aggiudicazione&lt;/td>
	&lt;td class="anac_content_value">
		<xsl:value-of select="importoAggiudicazione"/>
	&lt;/td>
&lt;/tr>
&lt;tr>
	&lt;td class="anac_content_title">Data di effettivo inizio&lt;/td>
	&lt;td class="anac_content_value">
		<xsl:value-of select="tempiCompletamento/dataInizio"/>
	&lt;/td>
&lt;/tr>
&lt;tr>
	&lt;td class="anac_content_title">Data di ultimazione&lt;/td>
	&lt;td class="anac_content_value">
		<xsl:value-of select="tempiCompletamento/dataUltimazione"/>
	&lt;/td>
&lt;/tr>
&lt;tr>
	&lt;td class="anac_content_title">Importo delle somme liquidate&lt;/td>
	&lt;td class="anac_content_value">
		<xsl:value-of select="importoSommeLiquidate"/>
	&lt;/td>
&lt;/tr>
&lt;/table>

<xsl:if test="count(partecipanti/partecipante) &gt; 0">
&lt;table class="anac_content_partecipanti">
&lt;caption>Partecipanti&lt;/caption>
<xsl:for-each select="partecipanti/partecipante">
&lt;tr>
	&lt;td class="anac_content_ragioneSociale"><xsl:value-of select="ragioneSociale"/>&lt;/td>
	&lt;td class="anac_content_codiceFiscale"><xsl:value-of select="codiceFiscale"/>&lt;/td>
&lt;/tr>
</xsl:for-each>
&lt;/table>
</xsl:if>

<xsl:if test="count(aggiudicatari/aggiudicatario) &gt; 0">
&lt;table class="anac_content_aggiudicatari">
&lt;caption>Aggiudicatari&lt;/caption>
<xsl:for-each select="aggiudicatari/aggiudicatario">
&lt;tr>
	&lt;td class="anac_content_ragioneSociale"><xsl:value-of select="ragioneSociale"/>&lt;/td>
	&lt;td class="anac_content_codiceFiscale"><xsl:value-of select="codiceFiscale"/>&lt;/td>
&lt;/tr>
</xsl:for-each>
&lt;/table>
</xsl:if>
&lt;a href="#" onclick="jQuery('#anac_toc').show();jQuery('#anac_<xsl:value-of select='generate-id(.)'/>').hide();">Chiudi&lt;/a>
&lt;/div>
</xsl:template>

<xsl:variable name="lowercase" select="'abcdefghijklmnopqrstuvwxyz '" />
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ '" />
<xsl:variable name="aliascase" select="'abcdefghijklmnopqrstuvwxyz-'" />

<xsl:template name="left-trim">
	<xsl:param name="s" />
	<xsl:choose>
		<xsl:when test="substring($s, 1, 1) = ''">
			<xsl:value-of select="$s"/>
		</xsl:when>
		<xsl:when test="normalize-space(substring($s, 1, 1)) = ''">
			<xsl:call-template name="left-trim">
				<xsl:with-param name="s" select="substring($s, 2)" />
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$s" />
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="right-trim">
	<xsl:param name="s" />
	<xsl:choose>
		<xsl:when test="substring($s, 1, 1) = ''">
			<xsl:value-of select="$s"/>
		</xsl:when>
		<xsl:when test="normalize-space(substring($s, string-length($s))) = ''">
			<xsl:call-template name="right-trim">
				<xsl:with-param name="s" select="substring($s, 1, string-length($s) - 1)" />
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$s" />
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="trim">
	<xsl:param name="s" />
	<xsl:call-template name="right-trim">
		<xsl:with-param name="s">
			<xsl:call-template name="left-trim">
				<xsl:with-param name="s" select="$s" />
			</xsl:call-template>
		</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="normalize-alias">
	<xsl:param name="s" />
	<xsl:param name="a">
		<xsl:call-template name="right-trim">
			<xsl:with-param name="s">
				<xsl:call-template name="left-trim">
					<xsl:with-param name="s" select="translate($s, '-.', '  ')" />
				</xsl:call-template>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:param>
	<xsl:value-of select="translate(normalize-space($a), $uppercase, $aliascase)" />
</xsl:template>

<xsl:template name="normalize-alias2">
	<xsl:param name="s" />
	<xsl:param name="a">
		<xsl:call-template name="trim">
			<xsl:with-param name="s" select="translate($s, '-.', '  ')" />
		</xsl:call-template>
	</xsl:param>
	<xsl:value-of select="translate(normalize-space($a), $uppercase, $aliascase)" />
</xsl:template>

</xsl:stylesheet>
