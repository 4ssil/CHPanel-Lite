<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * CHPanel Main Controller
 */
class CHPanelController extends JControllerLegacy
{

	/**
	 * Update #__hotel_data tables with Lite data
	 */
	public function build()
	{

		$build = CHPanelHelperData::buildDataObject();
		if (!$build)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_CHPANEL_BUILD_ERROR'), 'warning');
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_CHPANEL_BUILD_OK'));
		}

		$this->setRedirect('index.php?option=com_chpanel&view=panel');
	}

	/**
	 * Display the view
	 */
	public function display($cachable = false, $urlparams = false)
	{

		// set the default view
		JRequest::setVar('view', JRequest::getCmd('view', 'panel'));

		// display the view
		parent::display($cachable = false, $urlparams = false);
	}

}
