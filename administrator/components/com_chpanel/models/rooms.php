<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Rooms Model
 */
class CHPanelModelRooms extends JModelList
{

	/**
	 * Text prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEMS';

	/**
	 * Constructor
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.id',
				'a.created',
				'a.title',
				'a.state',
				'a.ordering',
				'h.title',
				'l.title'
			);
		}
		parent::__construct($config);
	}

	/**
	 * State
	 */
	protected function populateState($ordering = null, $direction = null)
	{

		$filters = array('search', 'state', 'hotel', 'language');

		foreach ($filters as $filter)
		{
			$var = $this->getUserStateFromRequest($this->context . '.filter.' . $filter, 'filter_' . $filter);
			$this->setState('filter.' . $filter, $var);
		}

		parent::populateState('a.ordering', 'asc');
	}

	/**
	 * Filters
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.hotel');
		$id .= ':' . $this->getState('filter.language');
		return parent::getStoreId($id);
	}

	/**
	 * Query
	 */
	protected function getListQuery()
	{

		// main query
		$query = $this->_db->getQuery(true);
		$query->select('a.*');
		$query->from('#__chpanel_rooms AS a');

		// joins
		$query->select('h.title AS hotel')->join('LEFT', '#__chpanel_hotels AS h ON h.id = a.hotel_id');
		$query->select('l.title AS language_title')->join('LEFT', '#__languages AS l ON l.lang_code = h.language');

		// checked out
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out_time');

		// state filter
		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}
		else if ($state != '*')
		{
			$query->where('a.state IN (0,1)');
		}

		// search filter
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $this->_db->Quote('%' . $this->_db->escape($search, true) . '%');
				$query->where('(a.title LIKE ' . $search . ')');
			}
		}

		// other standard filters
		foreach (array('hotel') as $filter_name)
		{
			$filter_value = $this->getState('filter.' . $filter_name);
			if (is_numeric($filter_value))
			{
				$query->where('a.' . $filter_name . '_id = ' . $filter_value);
			}
		}

		// ordering clause
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol == 'a.ordering')
		{
			$query->order($this->_db->escape("h.title $orderDirn, a.ordering $orderDirn"));
		}
		else
		{
			$query->order($this->_db->escape("$orderCol $orderDirn"));
		}

		return $query;
	}

}
