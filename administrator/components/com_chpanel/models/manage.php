<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Manage Model
 */
class CHPanelModelManage extends JModelLegacy
{

	/**
	 * Constructor
	 */
	public function __construct($config = array())
	{

		parent::__construct($config);

		$app = JFactory::getApplication();

		// filter hotel
		$var = $app->getUserStateFromRequest('com_chpanel.manage.filter.hotel', 'filter_hotel');
		$this->setState('filter.hotel', $var);

		// filter month
		$date = new JDate();
		$default = $date->format('m-Y');
		$month = $app->getUserStateFromRequest('com_chpanel.manage.filter.month', 'filter_month', $default);
		$this->setState('filter.month', $month);
	}

	/**
	 * Get the Inventory
	 */
	public function getInventory()
	{

		if (!isset($this->inventory))
		{

			// expire bookings
			$this->expireBookings();

			// get the hotel
			$hotel = $this->getHotel();
			if (!$hotel)
			{
				$this->inventory = false;
				return false;
			}

			// current month dates
			$start = new JDate('01-' . $this->getState('filter.month'));
			$this->start_date = $start->format('Y-m-d');
			$end = new JDate($this->month_days . '-' . $this->getState('filter.month'));
			$this->end_date = $end->format('Y-m-d');

			// get inventory per room
			foreach ($hotel->rooms as $room)
			{
				$this->inventory[$room->id] = $this->getRoomInventory($room->id);
			}
		}

		return $this->inventory;
	}

	/**
	 * Get Room Inventory
	 */
	private function getRoomInventory($room_id)
	{

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*')->from('#__chpanel_inventory AS a');
		$query->where("a.room_id IN($room_id)");
		$query->where("'$this->start_date' <= a.date");
		$query->where("a.date <= '$this->end_date'");
		$query->order('a.date');
		return $db->setQuery($query)->loadObjectList('date');
	}

	/**
	 * Get Bookings In Progress 
	 */
	public function getBookingsInProgress()
	{

		if (!isset($this->bookings))
		{

			// get the hotel
			$hotel = $this->getHotel();
			if (!$hotel)
			{
				$this->bookings = array();
				return array();
			}

			// get the bookings
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.room_id, a.checkin, a.checkout');
			$query->from('#__chpanel_bookings AS a');
			$query->where('a.checkin < a.checkout'); // check booking is ok
			$query->where('a.status < 200'); // processing states
			$query->where('a.hotel_id = ' . $hotel->id);

			// bookings in the month
			$or = array();
			$or[] = "(a.checkin <= '$this->start_date' AND a.checkout >= '$this->start_date')"; // have start intersection
			$or[] = "(a.checkin <= '$this->end_date' AND a.checkout >= '$this->end_date')"; // have end intersection
			$or[] = "(a.checkin >= '$this->start_date' AND a.checkout <= '$this->end_date')"; // completely inside
			$or = '(' . implode(' OR ', $or) . ')';
			$query->where($or);

			// get the list
			$bookings = $db->setQuery($query)->loadObjectList();
			if (!$bookings)
			{
				$this->bookings = array();
				return array();
			}

			$this->bookings = $bookings;
		}

		return $this->bookings;
	}

	/**
	 * Update inventory
	 */
	public function update()
	{

		// get the hotel
		$hotel = $this->getHotel();
		if (!$hotel)
		{
			$this->setError(JText::_('COM_CHPANEL_MANAGE_ERROR_HOTEL'));
			return false;
		}

		// prepare check
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$date_format = CHPanelHelper::getDateFormat(JText::_('COM_CHPANEL_LOCALE'));

		// start
		$start = $app->getUserStateFromRequest('com_chpanel.manage.start', 'start', JFactory::getDate()->format($date_format), 'string');
		$date_start = new JDate(CHPanelHelper::correctDateFormat($start));
		$this->start_date = $date_start->format('Y-m-d');

		// end
		$end = $app->getUserStateFromRequest('com_chpanel.manage.end', 'end', JFactory::getDate('+ 1 day')->format($date_format), 'string');
		$date_end = new JDate(CHPanelHelper::correctDateFormat($end));
		$this->end_date = $date_end->format('Y-m-d');

		// check dates
		if (!$this->start_date || !$this->end_date)
		{
			$this->setError(JText::_('COM_CHPANEL_MANAGE_ERROR_DATES'));
			return false;
		}

		// check period
		if ($date_end->format('Ymd') < $date_start->format('Ymd'))
		{
			$this->setError(JText::_('COM_CHPANEL_MANAGE_ERROR_DATES'));
			return false;
		}

		// check values
		$rids = array();
		foreach ($hotel->rooms as $room)
		{
			if (JRequest::getString('availability_' . $room->id, '', 'post') != '')
			{
				$rids[] = $room->id;
			}
			if (JRequest::getString('rate_' . $room->id, '', 'post') != '')
			{
				$rids[] = $room->id;
			}
		}
		if (!$rids)
		{
			$this->setError(JText::_('COM_CHPANEL_MANAGE_ERROR_INVENTORY'));
			return false;
		}

		// set filter_month state
		JFactory::getApplication()->setUserState('com_chpanel.manage.filter.month', $date_start->format('m-Y'));

		// get the rooms ids 
		$room_ids = array_unique($rids);

		// update inventory per room
		foreach ($room_ids as $room_id)
		{

			// get the room default rate
			foreach ($hotel->rooms as $room)
			{
				if ($room->id == $room_id)
				{
					$default_rate = $room->rate;
				}
			}

			// get the room current inventory
			$inventory_rows = $this->getRoomInventory($room_id);

			// prepare loop dates
			$date = $date_start->toUnix();
			$while_date = $date_start->format('Ymd');
			$while_end = $date_end->format('Ymd');
			$current_date = $date_start->format('Y-m-d');

			// update inventory
			while ($while_date <= $while_end)
			{

				// new object query
				$row = new stdClass();

				// get row values
				if (JRequest::getString('availability_' . $room_id, '', 'post') != '')
				{
					$row->availability = JRequest::getInt('availability_' . $room_id, 0, 'post');
				}
				if (JRequest::getString('rate_' . $room_id, '', 'post') != '')
				{
					$row->rate = JRequest::getFloat('rate_' . $room_id, 0, 'post');
				}

				// update or insert inventory row
				if (isset($inventory_rows[$current_date]))
				{

					// update row
					$row->id = $inventory_rows[$current_date]->id;
					$db->updateObject('#__chpanel_inventory', $row, 'id');
				}
				else
				{

					// default availability, 0 for new rows
					if (!isset($row->availability))
					{
						$row->availability = 0;
					}

					// default rate, room rack rate
					if (!isset($row->rate))
					{
						$row->rate = $default_rate;
					}

					// insert row
					$row->room_id = $room_id;
					$row->date = $current_date;
					$db->insertObject('#__chpanel_inventory', $row);
				}

				// update loop conditions
				$date = strtotime('+1 day', $date);
				$j_date = JFactory::getDate($date);
				$while_date = $j_date->format('Ymd');
				$current_date = $j_date->format('Y-m-d');
			}
		}

		// return ok
		JFactory::getApplication()->enqueueMessage(JText::_('COM_CHPANEL_MANAGE_APPLY_OK'));
		return true;
	}

	/**
	 * Get the Hotel & Rooms
	 */
	public function getHotel()
	{

		if (!isset($this->hotel))
		{

			// no hotel selected
			$id = $this->getState('filter.hotel');
			if (!$id)
			{
				return false;
			}

			// get db
			$db = JFactory::getDbo();

			// get the hotel
			$query_hotel = $db->getQuery(true);
			$query_hotel->select('a.*')->from('#__chpanel_hotels AS a');
			$query_hotel->where('a.id = ' . $id);
			$this->hotel = $db->setQuery($query_hotel)->loadObject();
			if (!$this->hotel)
			{
				$this->hotel = false;
				return false;
			}

			// get the rooms
			$query_rooms = $db->getQuery(true);
			$query_rooms->select('a.*')->from('#__chpanel_rooms AS a');
			$query_rooms->where('a.hotel_id = ' . $id);
			$query_rooms->where('a.state IN (0,1)');
			$query_rooms->order('a.ordering');
			$this->hotel->rooms = $db->setQuery($query_rooms)->loadObjectList();
			if (!$this->hotel->rooms)
			{
				$this->hotel = false;
				return false;
			}
		}

		return $this->hotel;
	}

	/**
	 * Get Hotels
	 */
	public function getHotels()
	{

		if (!isset($this->hotels))
		{

			// hotels query
			$query = $this->_db->getQuery(true);
			$query->select('a.id, a.title')->from('#__chpanel_hotels AS a');
			$query->where('a.state IN (0,1)');
			$query->order('a.title');

			$this->hotels = $this->_getList($query);
		}

		return $this->hotels;
	}

	/**
	 * Get Months
	 */
	public function getMonths()
	{

		if (!isset($this->months))
		{

			// get months list
			$months = array();
			for ($i = 0; $i <= 20; $i++)
			{
				$date = date("d-m-Y", mktime(0, 0, 0, date("m") + $i, 1, date("Y")));
				$month = new JDate($date);
				$months[$i] = new stdClass();
				$months[$i]->id = $month->format('m-Y');
				$months[$i]->title = $month->format('F') . ' ' . $month->format('Y');
				if ($month->format('m-Y') == $this->getState('filter.month'))
				{
					$this->month_start = date("w", mktime(0, 0, 0, date("m") + $i, 1, date("Y")));
					$this->month_16 = date("w", mktime(0, 0, 0, date("m") + $i, 16, date("Y")));
					$this->month_days = date("t", mktime(0, 0, 0, date("m") + $i, 1, date("Y")));
				}
			}

			// set months
			$this->months = $months;

			// start & end
			$date_start = new JDate('01-' . $this->getState('filter.month'));
			$this->start = $date_start->format('Y-m-d');
			$date_end = new JDate($this->month_days . '-' . $this->getState('filter.month'));
			$this->end = $date_end->format('Y-m-d');
		}

		return $this->months;
	}

	/**
	 * Get Month week day (1st)
	 */
	public function getMonthStart()
	{
		return $this->month_start;
	}

	/**
	 * Get Month week day (16th)
	 */
	public function getMonth16()
	{
		return $this->month_16;
	}

	/**
	 * Get Months
	 */
	public function getMonthDays()
	{
		return $this->month_days;
	}

	/**
	 * Expire old processing state bookings
	 */
	private function expireBookings()
	{

		// current time 15 minutes before
		$time = new JDate(strtotime("-15 minutes"));
		$expire_time = $time->toSql();

		// get expired bookings
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.room_id, a.checkin, a.checkout')->from('#__chpanel_bookings AS a');
		$query->where('a.created < ' . $db->quote($expire_time));
		$query->where('a.status < 200');
		$bookings = $db->setQuery($query)->loadObjectList();

		// change status to aborted and update availability
		if ($bookings)
		{
			foreach ($bookings as $booking)
			{
				$booking->status = 402;
				$db->updateObject('#__chpanel_bookings', $booking, 'id');
				$this->updateInventory($booking, 'up');
			}
		}

		return true;
	}

	/**
	 * Update inventory availability
	 */
	protected function updateInventory($booking, $update = 'down')
	{

		// get the inventory rows
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id')->from('#__chpanel_inventory AS a');
		$query->where("a.room_id = $booking->room_id");
		$query->where("'$booking->checkin' <= a.date");
		$query->where("a.date < '$booking->checkout'");
		$inventory_ids = $db->setQuery($query)->loadColumn();

		// update
		$sum = $update == 'down' ? '-1' : '+1';
		$inventory_ids_q = implode(',', $inventory_ids);

		// update availability
		$db->setQuery('UPDATE `#__chpanel_inventory` SET `availability` = (`availability` ' . $sum . ') WHERE `id` IN (' . $inventory_ids_q . ')')->execute();

		return;
	}

}
