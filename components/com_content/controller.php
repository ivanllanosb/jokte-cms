<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 ***************************************************************************************
 * Warning: Some modifications and improved were made by the Community Juuntos for
 * the latinamerican Project Jokte! CMS
 ***************************************************************************************
 */

defined('_JEXEC') or die;

/**
 * Content Component Controller
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since		1.5
 */
class ContentController extends JControllerLegacy
{
	function __construct($config = array())
	{
		// Article frontpage Editor pagebreak proxying:
		if (JRequest::getCmd('view') === 'article' && JRequest::getCmd('layout') === 'pagebreak') {
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}
		// Article frontpage Editor article proxying:
		elseif(JRequest::getCmd('view') === 'articles' && JRequest::getCmd('layout') === 'modal') {
			JHtml::_('stylesheet', 'system/adminlist.css', array(), true);
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		parent::__construct($config);
	}

	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$cachable = true;

		JHtml::_('behavior.caption');

		// Set the default view name and format from the Request.
		// Note we are using a_id to avoid collisions with the router and the return page.
		// Frontend is a bit messier than the backend.
		$id		= JRequest::getInt('a_id');
		$vName	= JRequest::getCmd('view', 'categories');
		JRequest::setVar('view', $vName);

		$user = JFactory::getUser();

		if ($user->get('id') ||
			($_SERVER['REQUEST_METHOD'] == 'POST' &&
				(($vName == 'category' && JRequest::getCmd('layout') != 'blog') || $vName == 'archive' ))) {
			$cachable = false;
		}

		$safeurlparams = array('catid'=>'INT', 'id'=>'INT', 'cid'=>'ARRAY', 'year'=>'INT', 'month'=>'INT', 'limit'=>'UINT', 'limitstart'=>'UINT',
			'showall'=>'INT', 'return'=>'BASE64', 'filter'=>'STRING', 'filter_order'=>'CMD', 'filter_order_Dir'=>'CMD', 'filter-search'=>'STRING', 'print'=>'BOOLEAN', 'lang'=>'CMD');

		// Check for edit form.
		if ($vName == 'form' && !$this->checkEditId('com_content.edit.article', $id)) {
			// Somehow the person just went to the form - we don't allow that.
			return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
		}

		parent::display($cachable, $safeurlparams);

		return $this;
	}
	
	/*
	 * Nuevo Jokte v1.2.2
	 * Función para bloquear el robo de descargas (Anti Leech)
	 */
	public function download() 
	{
		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		$file = JRequest::getVar('filename');
		
		// Anti leech sanitize URL
		$url = JURI::base( false );
		list($domain,$stuff2) = preg_split('//',$url,2);
		
		// Referente
		$refr = getenv("HTTP_REFERER");
		// Sanitize
		list($home,$stuff) = preg_split('//',$refr,2);
		$home = str_replace('www.', '', $home); 
		
		// Revisar leeching
		$blocking = false; 
		if ($home != $domain) {
			return JError::raiseWarning( 100, JText::_( 'COM_CONTENT_ANTELEECH MESSAGE' ));
		}
		
		$basePath = JPATH_SITE.DS.'images';
		$abspath = str_replace(DS, '/', JPath::clean($basePath.DS.$file));		
		if (!JFile::exists($abspath)) {
			return;
		}
		$size 	= filesize($abspath);
		$ext 	= strtolower(JFile::getExt($file));
		if(ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}
		switch( $ext ) {
			case "pdf":	
				$ctype = "application/pdf"; 
				break;
			case "exe":	
				$ctype="application/octet-stream"; 
				break;
			case "rar":
			case "zip": 
				$ctype = "application/zip";	
				break;
			case "txt":	
				$ctype = "text/plain"; 
				break;
			case "doc":	
				$ctype = "application/msword"; 
				break;
			case "xls":	
				$ctype = "application/vnd.ms-excel"; 
				break;
			case "ppt":	
				$ctype = "application/vnd.ms-powerpoint"; 
				break;
			case "gif":	
				$ctype = "image/gif"; 
				break;
			case "png":	
				$ctype = "image/png"; 
				break;
			case "jpeg":
			case "jpg":	
				$ctype = "image/jpg"; 
				break;
			case "mp3":	
				$ctype = "audio/mpeg"; 
				break;
			default: 
				$ctype = "application/force-download";
		}
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: $ctype");		
		header("Content-Disposition: attachment; filename=\"".$file."\";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$size);
		readfile($abspath);		
		$app->close();
	}
}
