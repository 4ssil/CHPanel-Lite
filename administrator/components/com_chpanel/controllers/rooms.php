<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Rooms Controller
 */
class CHPanelControllerRooms extends JControllerAdmin
{

	/**
	 * Prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEMS';

	/**
	 * Get the model
	 */
	public function getModel($name = 'Room', $prefix = 'CHPanelModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Rebuild data on publish
	 */
	public function publish()
	{

		parent::publish();

		CHPanelHelperData::buildDataObject();
	}

}
