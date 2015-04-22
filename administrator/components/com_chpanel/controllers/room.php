<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Room controller
 */
class CHPanelControllerRoom extends JControllerForm
{

	/**
	 * Prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEM';

	/**
	 * Rebuild data on save
	 */
	protected function postSaveHook()
	{

		CHPanelHelperData::buildDataObject();
	}

	/**
	 * Extend parent edit to check if the new room has a hotel defined
	 */
	public function edit($key = null, $urlVar = null)
	{

		// check hotel 
		$id = JRequest::getInt('id', 0);
		$filters = JFactory::getApplication()->getUserState('com_chpanel.rooms.filter');
		$hotel = $filters['hotel'];
		if (!$id && !$hotel)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_chpanel&view=rooms', false), JText::_('COM_CHPANEL_ANY_ERROR_SELECT_HOTEL'), 'notice');
			return;
		}

		parent::edit($key = null, $urlVar = null);
	}

}
