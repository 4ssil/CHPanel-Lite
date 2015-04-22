<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

// ACL check
if (!JFactory::getUser()->authorise('core.manage', 'com_chpanel'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// load language files
JFactory::getLanguage()->load('com_chpanel', null, 'en-GB', true);
JFactory::getLanguage()->load('com_chpanel');

// load helpers
require_once JPATH_BASE . '/components/com_chpanel/helpers/chpanel.php';
require_once JPATH_BASE . '/components/com_chpanel/helpers/image.php';
require_once JPATH_BASE . '/components/com_chpanel/helpers/langs.php';
require_once JPATH_BASE . '/components/com_chpanel/helpers/data.php';

// load and execute the controller
$controller = JControllerLegacy::getInstance('CHPanel');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
