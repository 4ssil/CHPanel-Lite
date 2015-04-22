<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

// load helper
JFormHelper::loadFieldClass('list');

/**
 * Room
 */
class JFormFieldCHPanelRoom extends JFormFieldList
{

	/**
	 * Filter name
	 */
	protected $type = 'CHPanelRoom';

	/**
	 * Get the options
	 */
	public function getOptions()
	{

		// db
		$db = JFactory::getDbo();

		// get the hotel_id
		$id = JRequest::getInt('id', 0);
		if ($id)
		{
			$hotel_id = $db->setQuery("SELECT hotel_id FROM #__chpanel_bookings WHERE id = $id")->loadResult();
		}
		else
		{
			$filters = JFactory::getApplication()->getUserState('com_chpanel.bookings.filter');
			$hotel_id = $filters['hotel'];
		}
		if (!$hotel_id)
		{
			jexit('JFormFieldCHPanelRoom error');
		}

		// query
		$query = $db->getQuery(true);
		$query->select('a.id AS value, a.title AS text');
		$query->from('#__chpanel_rooms AS a');
		$query->where('a.hotel_id = ' . $hotel_id);
		$query->where('a.state IN (0,1)');
		$query->order('a.ordering');
		$options = $db->setQuery($query)->loadObjectList();

		// build options
		return array_merge(parent::getOptions(), $options);
	}

}
