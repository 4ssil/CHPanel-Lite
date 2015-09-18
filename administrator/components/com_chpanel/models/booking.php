<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Booking Model
 */
class CHPanelModelBooking extends JModelAdmin
{

	/**
	 * Text prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEM';

	/**
	 * Get the table
	 */
	public function getTable($type = 'Booking', $prefix = 'CHPanelTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * get the form
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_chpanel.booking', 'booking', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	/**
	 * Get the data that should be injected to the form
	 */
	protected function loadFormData()
	{

		$data = JFactory::getApplication()->getUserState('com_chpanel.edit.booking.data', array());

		if (empty($data))
		{

			$data = $this->getItem();

			// default values
			if ($this->getState('booking.id') == 0)
			{
				$filters = JFactory::getApplication()->getUserState('com_chpanel.bookings.filter');
				$data->set('hotel_id', $filters['hotel']);
			}

			// hotel
			$hotel_table = JTable::getInstance('Hotel', 'CHPanelTable');
			$hotel_table->load($data->hotel_id);
			$data->set('hotel', $hotel_table->title);
		}

		return $data;
	}

}
