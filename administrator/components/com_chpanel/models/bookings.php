<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Bookings Model
 */
class CHPanelModelBookings extends JModelList
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
				'a.voucher',
				'guest',
				'hotel',
				'room',
				'a.checkin',
				'a.checkout',
				'a.amount',
				'a.status'
			);
		}
		parent::__construct($config);
	}

	/**
	 * State
	 */
	protected function populateState($ordering = null, $direction = null)
	{

		$filters = array('search', 'state', 'hotel', 'status');

		foreach ($filters as $filter)
		{
			$var = $this->getUserStateFromRequest($this->context . '.filter.' . $filter, 'filter_' . $filter);
			$this->setState('filter.' . $filter, $var);
		}

		parent::populateState('a.created', 'desc');
	}

	/**
	 * Filters
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.hotel');
		$id .= ':' . $this->getState('filter.status');
		return parent::getStoreId($id);
	}

	/**
	 * Query
	 */
	protected function getListQuery()
	{

		// main query
		$query = $this->_db->getQuery(true);
		$query->select('a.*, a.first_name AS title');
		$query->from('#__chpanel_bookings AS a');

		// joins
		$query->select('h.title AS hotel')->join('LEFT', '#__chpanel_hotels AS h ON h.id = a.hotel_id');
		$query->select('r.title AS room, r.reference')->join('LEFT', '#__chpanel_rooms AS r ON r.id = a.room_id');
		$query->select('l.title AS language_title')->join('LEFT', '#__languages AS l ON l.lang_code = h.language');

		// checked out
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out_time');

		// status filter
		$status = $this->getState('filter.status');
		if (is_numeric($status))
		{
			if ($status % 100 == 0)
			{
				$statuses = array($status + 1, $status + 2, $status + 3);
				$query->where('a.status IN (' . implode(',', $statuses) . ')');
			}
			else
			{
				$query->where('a.status = ' . $status);
			}
		}
		else
		{
			$query->where('a.status IN (201,202,203)'); // confirmed bookings
		}

		// state filter
		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}
		else if ($state != '*')
		{
			$query->where('a.state = 1');
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
				$query->where('(a.first_name LIKE ' . $search . ')');
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

		// date filters
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$start = $app->getUserStateFromRequest('com_chpanel.bookings.start', 'start', '', 'string');
		$end = $app->getUserStateFromRequest('com_chpanel.bookings.end', 'end', '', 'string');
		$date = $app->getUserStateFromRequest('com_chpanel.bookings.date', 'date', 'created', 'string');

		if (in_array($date, array('created', 'checkin', 'checkout')))
		{
			if ($start)
			{
				$date_start = new JDate(CHPanelHelper::correctDateFormat($start));
				$query_start = $db->quote($date_start->format('Y-m-d'));
				$query->where("$query_start <=  DATE(a.$date)");
			}
			if ($end)
			{
				$date_end = new JDate(CHPanelHelper::correctDateFormat($end));
				$query_end = $db->quote($date_end->format('Y-m-d'));
				$query->where("DATE(a.$date) <= $query_end");
			}
		}

		// ordering clause
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($this->_db->escape("$orderCol $orderDirn"));

		// echo nl2br(str_replace('#__', 'jos_', $query));

		return $query;
	}

}
