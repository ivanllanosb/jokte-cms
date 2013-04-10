<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_whosonline
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 ***************************************************************************************
 * Warning: Some modifications and improved were made by the Community Juuntos for
 * the latinamerican Project Jokte! CMS
 ***************************************************************************************
 */

// no direct access
defined('_JEXEC') or die;

// Include the whosonline functions only once
require_once dirname(__FILE__).'/helper.php';

$showmode = $params->get('showmode', 0);
$usermode = $params->get('usermode', 0);

if ($showmode == 0 || $showmode == 2) {
	$count	= modWhosonlineHelper::getOnlineCount();
}

if ($showmode > 0) {
	$names	= modWhosonlineHelper::getOnlineUserNames($params);
}

$linknames = $params->get('linknames', 0);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_whosonline', $params->get('layout', 'default'));
