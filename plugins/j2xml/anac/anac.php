<?php
/**
 * @version		3.0.3 plugins/j2xml/anac/anac.php
 * 
 * @package		J2XML
 * @subpackage	plg_j2xml_anac
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2016 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License 
 * or other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

use Joomla\Registry\Registry;

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');
jimport('eshiol.j2xml.version');

class plgJ2XMLAnac extends JPlugin
{
	var $_params = null;
	/**
	 * CONSTRUCTOR
	 * @param object $subject The object to observe
	 * @param object $config  The object that holds the plugin parameters
	 */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);		

		// Get the parameters.
		// TODO: merge $this->params and $config['params']
		if (isset($config['params']))
		{
			if ($config['params'] instanceof Registry)
			{
				$this->_params = $config['params'];
			}
			else
			{
				$this->_params = (version_compare(JPlatform::RELEASE, '12', 'ge') ? new Registry : new JRegistry);
				$this->_params->loadString($config['params']);
			}
		}
		
		$lang = JFactory::getLanguage();
		$lang->load('plg_j2xml_anac', JPATH_SITE, null, false, false)
			|| $lang->load('plg_j2xml_anac', JPATH_ADMINISTRATOR, null, false, false)
			|| $lang->load('plg_j2xml_anac', JPATH_SITE, null, true)
			|| $lang->load('plg_j2xml_anac', JPATH_ADMINISTRATOR, null, true);	

		JLog::addLogger(array('text_file' => 'j2xml.php', 'extension' => 'plg_j2xml_anac'), JLog::ALL, array('plg_j2xml_anac'));
		JLog::addLogger(array('logger' => 'messagequeue', 'extension' => 'plg_j2xml_anac'), JLOG::ALL & ~JLOG::DEBUG, array('plg_j2xml_anac'));
	}

	/**
	 * Method is called by 
	 *
	 * @access	public
	 */
	public function onBeforeImport($context, &$xml)
	{
		JLog::add(new JLogEntry(__METHOD__,JLOG::DEBUG,'plg_j2xml_anac'));
		JLog::add(new JLogEntry($context,JLOG::DEBUG,'plg_j2xml_anac'));
		JLog::add(new JLogEntry(print_r($this->_params, true),JLOG::DEBUG,'plg_j2xml_anac'));
		
		if (get_class($xml) != 'SimpleXMLElement')
			return false;

		$error = false;
		if (!class_exists('XSLTProcessor'))
		{
			JLog::add(new JLogEntry(JText::_('PLG_J2XML_ANAC').' '.JText::_('PLG_J2XML_ANAC_MSG_REQUIREMENTS_XSL')),JLOG::WARNING,'plg_j2xml_anac');
			$error = true;
		}
		
		if (version_compare(J2XMLVersion::getShortVersion(), '13.8.3') == -1)
		{
			JLog::add(new JLogEntry(JText::_('PLG_J2XML_ANAC').' '.JText::_('PLG_J2XML_ANAC_MSG_REQUIREMENTS_LIB')),JLOG::WARNING,'plg_j2xml_anac');
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
		$xsl = file_get_contents(JPATH_ROOT.'/plugins/j2xml/anac/anac.xsl');
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
		JLog::add(new JLogEntry('catid: '.$catid,JLOG::DEBUG,'plg_j2xml_anac'));
		$categoryTable = JTable::getInstance('Category');
		$categoryTable->load($catid);
		JLog::add(new JLogEntry('category: '.$categoryTable->path,JLOG::DEBUG,'plg_j2xml_anac'));
		$xsl = str_replace(
			'<catid>uncategorised</catid>',
			'<catid>'.$categoryTable->path.'</catid>',
			$xsl
			);
		JLog::add(new JLogEntry($xsl,JLOG::DEBUG,'plg_j2xml_anac'));
		$xslfile->loadXML($xsl);		
		$xslt->importStylesheet($xslfile);
		$xml = $xslt->transformToXML($xml);
		$xml = simplexml_load_string($xml);
		return true;
	}
}
