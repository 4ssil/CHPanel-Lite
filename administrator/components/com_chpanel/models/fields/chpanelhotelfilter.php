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
 * KitHotelFilter
 */
class JFormFieldCHPanelHotelFilter extends JFormFieldList
{

	/**
	 * Filter name
	 */
	protected $type = 'CHPanelHotelFilter';

	/**
	 * Get the options
	 */
	public function getOptions()
	{

		// query
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id AS value, a.title AS text');
		$query->from('#__chpanel_hotels AS a');
		$query->where('a.state IN (0,1)');
		$query->order('a.title');

		$options = $db->setQuery($query)->loadObjectList();

		return array_merge(parent::getOptions(), $options);
	}

}
