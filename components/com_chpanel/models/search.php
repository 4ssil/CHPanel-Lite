<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Search Model
 */
class CHPanelSearch
{

	/**
	 * Constructor
	 */
	public function __construct($vars = array())
	{

		// component params
		$this->chpanel_params = JComponentHelper::getParams('com_chpanel');
		$this->hotel_params = JComponentHelper::getParams('com_hotel');

		// no quote requests
		if (!$vars)
		{
			return;
		}

		// quote vars
		$this->quote = new stdClass();
		$this->quote->ok = true;
		$this->quote->errors = array();
		$this->quote->limitstart = $vars['limitstart'];
		$this->quote->limit = $vars['limit'];
		$this->quote->promotion = $vars['promotion'];
		$this->quote->lang = substr($vars['lang'], 0, 2);
		$this->quote->filters = array();
		$this->quote->curr = '';
		$this->quote->hotel = isset($vars['hotel']) ? $vars['hotel'] : 0;

		// checkin & checkout
		$checkin = JFactory::getDate($vars['checkin']);
		$checkout = JFactory::getDate($vars['checkout']);
		$this->quote->nights = ceil(($checkout->toUnix() - $checkin->toUnix()) / 86400);
		if ($this->quote->nights > 0)
		{
			$this->quote->checkin = $checkin->format('Y-m-d');
			$this->quote->checkout = $checkout->format('Y-m-d');
		}
		else
		{
			$this->quote->errors[] = '102. ' . (JText::_('COM_HOTELS_ERROR_102'));
		}
		$this->quote->release = ($checkin->toUnix() - JFactory::getDate(JFactory::getDate()->format('Y-m-d'))->toUnix()) / 86400;

		// quote date
		$today = JFactory::getDate();
		$this->quote->date = $today->format('Y-m-d');
		if ($today->format('Ymd') > $checkin->format('Ymd'))
		{
			$this->quote->errors[] = '105. ' . (JText::_('COM_HOTELS_ERROR_105'));
		}

		// board
		$this->quote->board = false;

		// rooms
		$this->quote->rooms = array();
		$rooms = JRequest::getInt('rooms');
		if ($rooms > 1)
		{
			$this->quote->errors[] = '103. ' . (JText::_('COM_HOTELS_ERROR_103'));
		}
		else
		{

			$room = new stdClass();
			$room->adults = (int) $vars['adults'];
			$room->childs = (int) $vars['childs'];
			$room->babys = (int) $vars['babys'];
			$this->quote->rooms = array($room);

			if (!$room->adults)
			{
				$this->quote->errors[] = '104. ' . (JText::_('COM_HOTELS_ERROR_103'));
			}
		}

		// filters 
		$this->quote->cats = array();

		// check quote
		if (!count($this->quote->errors))
		{
			$this->quote->ok = 1;
		}
	}

	/**
	 * Returns the main object with hotels availability and rates
	 */
	public function getData()
	{

		// Get the data
		$this->data = new stdClass();
		$this->data->errors = array();
		$this->data->quote = $this->quote;
		$this->data->map = new stdClass();
		$this->data->calendar = array();

		// get the items and pagination
		$items = $this->getItems();
		$this->data->pagination = new stdClass();
		$this->data->pagination->total = count($items);
		$this->data->pagination->start = $this->data->quote->limitstart;
		$this->data->pagination->limit = $this->data->quote->limit;
		$this->data->total_hotels = count($items);
		$this->data->items = array_slice($items, $this->data->quote->limitstart, $this->data->quote->limit);

		// map & calendar
		$this->buildMapObject();
		$this->buildCalendarObject();

		return $this->data;
	}

	/**
	 * get the items
	 */
	private function getItems()
	{

		// expire old processing state bookings
		$this->expireBookings();

		// get available hotels
		$results = $this->getResults();

		// reorder items by amount_curr
		array_values($results);
		usort($results, array('CHPanelSearch', 'reorderResults'));

		return $results;
	}

	/**
	 * Reorder results
	 */
	private function reorderResults($a, $b)
	{
		if ($a->amount_curr == $b->amount_curr)
		{
			return 0;
		}
		return ($a->amount_curr < $b->amount_curr) ? -1 : 1;
	}

	/**
	 * Expire old processing state bookings
	 */
	private function getResults()
	{

		// get active hotels
		$hotels = $this->getHotels();
		if (!$hotels)
		{
			$this->data->errors[] = '201. ' . JText::_('COM_HOTELS_ERROR_201');
			return array();
		}

		// get adequate rooms ids
		$rooms = $this->getRooms();
		if (!$rooms)
		{
			$this->data->errors[] = '206. ' . JText::sprintf('COM_HOTELS_ERROR_206', 1);
			return array();
		}

		// get rooms inventory
		$inventory = $this->getInventory();
		if (!$inventory)
		{
			$this->data->errors[] = '208. ' . JText::_('COM_HOTELS_ERROR_208');
			return array();
		}

		// buid CloudHotelier hotels data object
		$items = $this->buildItemsObject();

		return $items;
	}

	/**
	 * Buid CloudHotelier hotels data object
	 */
	private function buildItemsObject()
	{

		$items = array();
		$ids = array();

		// default board
		$default_board = $this->chpanel_params->get('board', 'bb');

		foreach ($this->hotels as $hotel)
		{

			// prepare new item
			$item = new stdClass();
			$item->id = $hotel->id;
			$item->title = $hotel->title;

			// slug <> alias
			$item->slug = $hotel->alias;

			// hotel default curr
			$item->currency = $this->chpanel_params->get('currency', 'EUR');
			$item->curr = 1;

			// other info fields
			foreach (array('info', 'text', 'conditions', 'lat', 'lng', 'zoom', 'email', 'video', 'phone', 'street', 'city', 'state', 'zip') as $v)
			{
				$item->$v = $hotel->$v;
			}
			$item->state = $hotel->region;

			// categories
			$item->cats = array();
			$params = json_decode($hotel->params);
			foreach (array(1, 2, 3, 4, 5, 6) as $c)
			{
				$cat = 'cat' . $c . '00';
				if (!is_array($params->$cat))
				{
					$item->cats[] = $params->$cat;
				}
				else
				{
					foreach ($params->$cat as $ca)
					{
						$item->cats[] = $ca;
					}
				}
			}

			// item boards
			$item->boards = new stdClass();
			foreach (array('ro', 'bb', 'hb', 'fb', 'ai') as $board)
			{
				$item->boards->$board = ($default_board == $board) ? 1 : 0;
			}
			$item->boards_rates = new stdClass();
			$rates = array();
			for ($i = 0; $i < $this->quote->nights; $i++)
			{
				$rates[] = 0;
			}
			$item->boards_rates->$default_board = $rates;

			// add photos
			$item->photos = $this->getPhotos($item, 0);

			// packs and promos
			$item->packs = array();
			$item->promos = array();

			// amounts
			$item->amount = 999999999999;
			$item->amount_discount = 0;
			$item->amount_curr = 999999999999;
			$item->amount_curr_discount = 0;

			// item rooms
			$item->rooms = array();
			$item->rooms[0] = array();

			// prepare error messages
			$error_release = false;
			$error_minstay = false;
			$error_inventory = false;

			// rooms
			foreach ($this->rooms as $room)
			{

				// availability checks
				$release_ok = 1;
				$minstay_ok = 1;
				$inventory_ok = 1;

				// add the hotel rooms
				if ($hotel->id == $room->hotel_id)
				{

					// 1. check room release
					if ($room->release > $this->quote->release)
					{
						$release_ok = false;
						$error_release = true;
					}

					// 2. check room minstay
					if ($room->minstay > $this->quote->nights)
					{
						$minstay_ok = false;
						$error_minstay = true;
					}

					// 3. check room inventory
					if (!isset($this->inventory[$room->id]))
					{
						$inventory_ok = false;
						$error_inventory = true;
					}
					else
					{

						// set inventory var
						$inventory = $this->inventory[$room->id];

						// check inventory is correct 
						if ($inventory->count < $this->quote->nights)
						{
							$inventory_ok = false;
							$error_inventory = true;
						}

						// check room availability
						if (!$inventory->availability)
						{
							$inventory_ok = false;
							$error_inventory = true;
						}
					}

					// add the room to the hotel
					if ($release_ok && $minstay_ok && $inventory_ok)
					{

						// prepare new room item
						$item_room = new stdClass();
						$item_room->id = $room->id;
						$item_room->title = $room->title;

						// other info fields
						foreach (array('info', 'text', 'video') as $v)
						{
							$item_room->$v = $room->$v;
						}

						// add photos
						$item_room->photos = $this->getPhotos($item, $room);

						// build CH room object
						$item_room->smoking = 0;
						$item_room->bed = 0;
						$item_room->conditions = 0;
						$item_room->capacity = new stdClass();
						$item_room->capacity->standard = $room->capacity;
						$item_room->capacity->max = new stdClass();
						$item_room->capacity->max->adult = $room->max_adult;
						$item_room->capacity->max->child = $room->max_child;
						$item_room->capacity->max->baby = $room->max_baby;
						$item_room->availability = $inventory->availability;
						$item_room->discount = null;
						$item_room->amounts = new stdClass();
						$item_room->amounts->$default_board = $inventory->amount;
						$item_room->amounts_curr = $item_room->amounts;
						$item_room->amounts_discount = new stdClass();
						$item_room->amounts_discount->$default_board = 0;
						$item_room->amounts_curr_discount = $item_room->amounts_discount;
						$item_room->rates = $inventory->rates;
						$item_room->extras = array();

						// lowest rate
						if ($inventory->amount < $item->amount)
						{
							$item->amount = $inventory->amount;
							$item->amount_curr = $inventory->amount;
						}

						// add the room to the item
						$item->rooms[0][] = $item_room;
					}
				}
			}

			if ($item->rooms[0])
			{

				// add the item to the available hotels list
				$items[] = $item;
				$ids[] = $item->id;
			}
			else
			{

				// set availability errors
				if ($error_release)
				{
					$this->data->errors[] = '202. ' . JText::sprintf('COM_HOTELS_ERROR_202', $hotel->title);
				}
				if ($error_minstay)
				{
					$this->data->errors[] = '203. ' . JText::sprintf('COM_HOTELS_ERROR_203', $hotel->title);
				}
				if ($error_inventory)
				{
					$this->data->errors[] = '208. ' . JText::sprintf('COM_HOTELS_ERROR_208', $hotel->title);
				}
			}
		}

		// apply translations
		if ($ids)
		{
			$items = $this->loadTranslations($items, $ids);
		}

		return $items;
	}

	/**
	 * Get the translations and override strings
	 */
	private function loadTranslations($items, $ids)
	{

		// get the translations
		$qids = implode(',', $ids);
		$db = JFactory::getDbo();
		$lang = $db->quote(JFactory::getLanguage()->getTag());
		$query_translation = $db->getQuery(true)->select('*')->from('#__chpanel_translations')->where("lang = $lang")->where("hotel_id IN ($qids)");
		$translations = $db->setQuery($query_translation)->loadObjectList('hotel_id');

		if ($translations)
		{
			foreach ($items as $item)
			{
				if (isset($translations[$item->id]))
				{
					$item = CHPanelHelper::applyTranslation($item, $translations[$item->id]);
				}
			}
		}

		return $items;
	}

	/**
	 * Get Inventory rows
	 */
	private function getInventory()
	{

		// get inventory rows
		$db = JFactory::getDbo();
		$rooms_ids_q = implode(',', $this->rooms_ids);
		$query = $db->getQuery(true);
		$query->select('a.room_id, a.date, a.rate, a.availability')
			->from('#__chpanel_inventory AS a')
			->where("a.room_id IN ($rooms_ids_q)")
			->where('a.date >= ' . $db->quote($this->quote->checkin))
			->where('a.date < ' . $db->quote($this->quote->checkout))
			->order('a.room_id, a.date');
		$rows = $db->setQuery($query)->loadObjectList();

		if (!$rows)
		{
			return false;
		}

		// get the results
		$inventory = array();

		// loop through inventory rows
		foreach ($rows as $row)
		{

			// group by room
			if (!isset($inventory[$row->room_id]))
			{
				$inventory[$row->room_id] = new stdClass();
				$inventory[$row->room_id]->count = 0;
				$inventory[$row->room_id]->availability = 999999;
				$inventory[$row->room_id]->rates = array();
				$inventory[$row->room_id]->amount = 0;
			}

			// count inventory results
			$inventory[$row->room_id]->count++;

			// get lowest availability
			$inventory[$row->room_id]->availability = $row->availability < $inventory[$row->room_id]->availability ? $row->availability : $inventory[$row->room_id]->availability;

			// rates per day
			$inventory[$row->room_id]->rates[] = $row->rate;

			// amount
			$inventory[$row->room_id]->amount = $inventory[$row->room_id]->amount + $row->rate;
		}

		$this->inventory = $inventory;

		return $this->inventory;
	}

	/**
	 * Get Rooms Info
	 */
	private function getPhotos($hotel, $room)
	{

		if (!isset($this->images))
		{

			// get images
			$db = JFactory::getDbo();
			$hotels_ids_q = implode(',', $this->hotels_ids);
			$query = $db->getQuery(true);
			$query->select('a.*')
				->from('#__chpanel_images AS a')
				->where("a.hotel_id IN ($hotels_ids_q)")
				->where('a.state = 1')
				->order('a.ordering');
			$this->images = $db->setQuery($query)->loadObjectList();
		}

		$photos = array();

		$path_images = JUri::root() . 'images/chpanel/images/';
		$path_main = $room ? JUri::root() . 'images/chpanel/rooms/' : JUri::root() . 'images/chpanel/hotels/';
		$id_main = $room ? $room->id : $hotel->id;

		// main photos
		$photo = new stdClass();
		$photo->id = $hotel->id;
		$photo->title = $hotel->title;
		$photo->info = $hotel->info;
		$photo->img = $path_main . $id_main . '-screen.jpg';
		$photo->img_tiny = $path_main . $id_main . '-tiny.jpg';
		$photo->img_small = $path_main . $id_main . '-small.jpg';
		$photo->img_med = $path_main . $id_main . '-med.jpg';
		$photo->img_big = $path_main . $id_main . '-big.jpg';
		$photos[] = $photo;

		// images
		foreach ($this->images as $image)
		{

			$params = json_decode($image->params);
			$params->tags = isset($params->tags) ? array_keys((array) $params->tags) : array();
			$params->rooms = isset($params->rooms) ? array_keys((array) $params->rooms) : array();

			if ($image->hotel_id == $hotel->id)
			{

				if ((!$room && $params->tags) || in_array($room->id, $params->rooms))
				{

					$photo = new stdClass();
					$photo->id = $image->id;
					$photo->title = $image->title;
					$photo->info = $image->info;
					$photo->img = $path_images . $image->id . '-screen.jpg';
					$photo->img_tiny = $path_images . $image->id . '-tiny.jpg';
					$photo->img_small = $path_images . $image->id . '-small.jpg';
					$photo->img_med = $path_images . $image->id . '-med.jpg';
					$photo->img_big = $path_images . $image->id . '-big.jpg';

					if (!$room)
					{
						$photo->tags = $params->tags;
					}

					$photos[] = $photo;
				}
			}
		}

		return $photos;
	}

	/**
	 * Get Rooms Info
	 */
	private function getRooms()
	{

		// get rooms data
		$db = JFactory::getDbo();
		$room = $this->quote->rooms[0];
		$hotels_ids_q = implode(',', $this->hotels_ids);
		$query = $db->getQuery(true);
		$query->select('a.*')
			->from('#__chpanel_rooms AS a')
			->where("a.hotel_id IN ($hotels_ids_q)")
			->where('(a.max_adult) >= ' . $room->adults)
			->where('(a.max_adult + a.max_child) >= ' . ($room->adults + $room->childs))
			->where('(a.max_adult + a.max_child + a.max_baby) >= ' . ($room->adults + $room->childs + $room->babys))
			->where('a.state = 1')
			->order('a.ordering');
		$this->rooms = $db->setQuery($query)->loadObjectList();

		// check rooms
		if (!$this->rooms)
		{
			return false;
		}

		// build rooms ids
		$this->rooms_ids = array();
		foreach ($this->rooms as $room)
		{
			$this->rooms_ids[] = $room->id;
		}

		return $this->rooms;
	}

	/**
	 * Get Hotels info
	 */
	private function getHotels()
	{

		// get the hotels
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*')->from('#__chpanel_hotels AS a')->where('a.state = 1');

		// single hotel quote
		if ($this->quote->hotel)
		{
			$query->where('a.id = ' . $this->quote->hotel);
		}

		// get the hotels list
		$this->hotels = $db->setQuery($query)->loadObjectList();

		// check hotels
		if (!$this->hotels)
		{
			return false;
		}

		// build hotels ids
		$this->hotels_ids = array();
		foreach ($this->hotels as $hotel)
		{
			$this->hotels_ids[] = $hotel->id;
		}

		return $this->hotels;
	}

	/**
	 * Buid CloudHotelier hotels data object
	 */
	private function buildMapObject()
	{

		// no map
		if (!$this->data->items)
		{
			return;
		}

		// map data arrays
		$this->data->map->markers = array();
		$this->data->map->markers_array = array();

		foreach ($this->data->items as $i => $item)
		{

			// a new map marker per hotel
			$marker = new stdClass();
			foreach (array('id', 'title', 'info', 'lat', 'lng', 'zoom', 'currency', 'amount_curr') as $v)
			{
				$marker->$v = $item->$v;
			}
			$marker->img = $item->photos[0]->img_tiny;
			$this->data->map->markers[] = $marker;

			// add the id to the array map
			$this->data->map->markers_array[$item->id] = $i;
		}
	}

	/**
	 * Buid CloudHotelier hotels data object
	 */
	private function buildCalendarObject()
	{

		// no calendar for multiple hotels search
		if (!$this->quote->hotel)
		{
			return;
		}

		// 1st month
		$year1 = JFactory::getDate($this->quote->checkin)->format('Y');
		$month1 = JFactory::getDate($this->quote->checkin)->format('m');
		$this->data->calendar[] = $this->getCalendar("$year1-$month1");

		// 2nd month
		$month = $month1 + 1;
		$year2 = $year1;
		if ($month > 12)
		{
			$month = 1;
			$year2++;
		}
		$month2 = str_pad($month, 2, '0', STR_PAD_LEFT);
		$this->data->calendar[] = $this->getCalendar("$year2-$month2");
	}

	/**
	 * Build Calendar Object for a month
	 */
	private function getCalendar($month)
	{

		// prepare db
		$db = JFactory::getDbo();

		// get the hotel rooms
		if (!isset($this->calendar_rooms))
		{
			$hotel_id = $this->hotels_ids[0];
			$query_rooms = $db->getQuery(true);
			$query_rooms->select('a.*')
				->from('#__chpanel_rooms AS a')
				->where("a.hotel_id = $hotel_id")
				->where('a.state = 1')
				->order('a.ordering');
			$this->calendar_rooms = $db->setQuery($query_rooms)->loadObjectList();
		}

		// build calendar month object
		$month = JFactory::getDate($month . '-01');
		$calendar = new stdClass();
		$calendar->month = $month->format('m');
		$calendar->year = $month->format('Y');
		$calendar->days = $month->format('t');
		$calendar->start = $calendar->year . '-' . $calendar->month . '-01';
		$calendar->end = $calendar->year . '-' . $calendar->month . '-' . $calendar->days;
		$calendar->day_start = JFactory::getDate($calendar->start)->format('w');

		// get the inventory for the month
		$rooms_ids_q = implode(',', $this->rooms_ids);
		$query = $db->getQuery(true);
		$query->select('a.room_id, a.date, a.rate, a.availability')
			->from('#__chpanel_inventory AS a')
			->where("a.room_id IN ($rooms_ids_q)")
			->where('a.date >= ' . $db->quote($calendar->start))
			->where('a.date <= ' . $db->quote($calendar->end))
			->order('a.room_id, a.date');
		$inventory_rows = $db->setQuery($query)->loadObjectList();

		$cal_rooms = array();
		foreach ($this->calendar_rooms as $j => $room)
		{

			// new row
			$cal_rooms[$j] = new stdClass();
			$cal_rooms[$j]->title = $room->title;
			$cal_rooms[$j]->calendar = new stdClass();

			for ($i = 1; $i <= $calendar->days; $i++)
			{

				// new column
				$cal_rooms[$j]->calendar->$i = new stdClass();
				$cal_rooms[$j]->calendar->$i->rate = $room->rate;
				$cal_rooms[$j]->calendar->$i->available = 0;

				// override inventory
				$row_date = $calendar->year . '-' . $calendar->month . '-' . str_pad($i, 2, "0", STR_PAD_LEFT);
				foreach ($inventory_rows as $row)
				{
					if ($row->room_id == $room->id)
					{
						if ($row->date == $row_date)
						{
							$cal_rooms[$j]->calendar->$i->rate = $row->rate;
							$cal_rooms[$j]->calendar->$i->available = $row->availability;
						}
					}
				}
			}
		}

		$calendar->rooms = $cal_rooms;

		return $calendar;
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
