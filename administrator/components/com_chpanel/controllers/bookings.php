<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Bookings Controller
 */
class CHPanelControllerBookings extends JControllerAdmin
{

	/**
	 * Prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEMS';

	/**
	 * Get the model
	 */
	public function getModel($name = 'Booking', $prefix = 'CHPanelModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

}
