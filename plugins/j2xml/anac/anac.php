<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  J2XML.anac
 *
 * @author		Helios Ciancio <info (at) eshiol (dot) it>
 * @link		https://www.eshiol.it
 * @copyright	Copyright (C) 2016 - 2020 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License 
 * or other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

use Joomla\Registry\Registry;
use eshiol\J2XML\Version;

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');
jimport('eshiol.j2xml.Version');

/*
 * @version		3.8.2
 */
class plgJ2xmlAnac extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param  object  $subject  The object to observe
	 * @param  array   $config   An array that holds the plugin configuration
	 */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if ($this->params->get('debug') || defined('JDEBUG') && JDEBUG)
		{
			JLog::addLogger(array('text_file' => $this->params->get('log', 'eshiol.log.php'), 'extension' => 'plg_j2xml_anac_file'), JLog::ALL, array('plg_j2xml_anac'));
		}
		if (PHP_SAPI == 'cli')
		{
			JLog::addLogger(array('logger' => 'echo', 'extension' => 'plg_j2xml_anac'), JLOG::ALL & ~JLOG::DEBUG, array('plg_j2xml_anac'));
		}
		else
		{
			JLog::addLogger(array('logger' => (null !== $this->params->get('logger')) ?$this->params->get('logger') : 'messagequeue', 'extension' => 'plg_j2xml_anac'), JLOG::ALL & ~JLOG::DEBUG, array('plg_j2xml_anac'));
			if ($this->params->get('phpconsole') && class_exists('JLogLoggerPhpconsole'))
			{
				JLog::addLogger(['logger' => 'phpconsole', 'extension' => 'plg_j2xml_anac_phpconsole'],  JLOG::DEBUG, array('plg_j2xml_anac'));
			}
		}
		JLog::add(new JLogEntry(__METHOD__, JLog::DEBUG, 'plg_j2xml_anac'));
	}
	
	/**
	 * Method is called by 
	 *
	 * @access	public
	 */
	public function onBeforeImport($context, &$xml)
	{
		JLog::add(new JLogEntry(__METHOD__, JLog::DEBUG, 'plg_j2xml_anac'));
		JLog::add(new JLogEntry($context, JLOG::DEBUG, 'plg_j2xml_anac'));
		JLog::add(new JLogEntry(print_r($this->params, true), JLOG::DEBUG, 'plg_j2xml_anac'));

		if (get_class($xml) != 'SimpleXMLElement')
			return false;

		$error = false;
		if (!class_exists('XSLTProcessor'))
		{
			JLog::add(new JLogEntry(JText::_('PLG_J2XML_ANAC').' '.JText::_('PLG_J2XML_ANAC_MSG_REQUIREMENTS_XSL'), JLog::WARNING, 'plg_j2xml_anac'));
			$error = true;
		}

		if (version_compare(eshiol\J2xml\Version::getShortVersion(), '19.2') == -1)
		{
			JLog::add(new JLogEntry(JText::_('PLG_J2XML_ANAC').' '.JText::_('PLG_J2XML_ANAC_MSG_REQUIREMENTS_LIB'), JLog::WARNING, 'plg_j2xml_anac'));
			$error = true;
		}

		// Check if Joomla Extension plugin is enabled.
		if (JPluginHelper::isEnabled('content', 'htmlpurifier'))
		{
			JLog::add(new JLogEntry(JText::_('PLG_J2XML_ANAC').' '.JText::_('PLG_J2XML_ANAC_MSG_REQUIREMENTS_PLG_CONTENT_HTMLPURIFIER'), JLog::WARNING, 'plg_j2xml_anac'));
			$error = true;
		}
		
		if ($error) return false;
		$namespaces = $xml->getNamespaces(true);
		if (isset($namespaces['legge190']))
			$legge190 = 'legge190';
		elseif (isset($namespaces['ns1']))
			$legge190 = 'ns1';
		else
			return true;

		$xml->registerXPathNamespace($legge190, $namespaces[$legge190]);
		if (!$xml->xpath('/'.$legge190.':pubblicazione'))
			return true;

		$xslt = new XSLTProcessor();
		$xslfile = new DOMDocument();
		//$xslfile->load(JPATH_ROOT.'/plugins/j2xml/anac/anac.xsl');
		$xsl = file_get_contents(JPATH_ROOT.'/plugins/j2xml/anac/'.$this->params->get('xsl', 'anac.xsl'));
		$title = $this->params->get('title', 'year');
		if ($title == 'year')
		{
			$xsl = str_replace(
				'<title><xsl:value-of select="/legge190:pubblicazione/metadata/abstract"/></title>',
				'<title><xsl:value-of select="/legge190:pubblicazione/metadata/titolo"/>'
					.' anno rif. '
					.'<xsl:value-of select="/legge190:pubblicazione/metadata/annoRiferimento"/></title>',
				$xsl
				);
		}
		elseif ($title == 'title')
		{
			$xsl = str_replace(
				'<title><xsl:value-of select="/legge190:pubblicazione/metadata/abstract"/></title>',
				'<title><xsl:value-of select="/legge190:pubblicazione/metadata/titolo"/></title>',
				$xsl
				);
		}
		if ($legge190 != 'legge190')
		{
			$xsl = str_replace(
				'xmlns:legge190="legge190_1_0"',
				'xmlns:'.$legge190.'="legge190_1_0"',
				$xsl
				);
			$xsl = str_replace(
				' select="/legge190:pubblicazione/',
				' select="/'.$legge190.':pubblicazione/',
				$xsl
				);
		}
		$catid = $this->params->get('category_id', 2);
		JLog::add(new JLogEntry('catid: '.$catid, JLOG::DEBUG, 'plg_j2xml_anac'));
		$categoryTable = JTable::getInstance('Category');
		$categoryTable->load($catid);
		JLog::add(new JLogEntry('category: '.$categoryTable->path, JLOG::DEBUG, 'plg_j2xml_anac'));
		$xsl = str_replace(
			'<catid>uncategorised</catid>',
			'<catid>'.$categoryTable->path.'</catid>',
			$xsl
			);
		$accordion = $this->params->get('accordion');
		JLog::add(new JLogEntry('accordion: '.$accordion, JLOG::DEBUG, 'plg_j2xml_anac'));
		if ($accordion)
		{
			$xsl = str_replace(
				'Accordion Accordion--default',
				$accordion,
				$xsl
				);
		}
		JLog::add(new JLogEntry($xsl, JLOG::DEBUG, 'plg_j2xml_anac'));
		$xslfile->loadXML($xsl);
		$xslt->importStylesheet($xslfile);
		$xml = $xslt->transformToXML($xml);
		$xml = simplexml_load_string($xml);
		return true;
	}
}
