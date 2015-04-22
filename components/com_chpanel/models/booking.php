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
class CHPanelBooking extends CHPanelSearch
{

	/**
	 * Update a booking
	 */
	public function updateBooking($vars)
	{

		// get the booking
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.status, a.payment_status')->from('#__chpanel_bookings AS a')->where('a.id = ' . $vars['booking_id']);
		$booking = $db->setQuery($query)->loadObject();

		// check booking
		if (!$booking)
		{
			return 0;
		}

		// update the booking
		$booking->status = $vars['state'];
		$booking->payment_status = $vars['payment_state'];
		$booking->payment_ref = $vars['card_ref'];
		$booking->payment_auth = $vars['card_auth'];
		$db->updateObject('#__chpanel_bookings', $booking, 'id');

		return $booking->id;
	}

	/**
	 * Get Booking Id from My Booking request
	 */
	public function myBooking($vars)
	{

		// get booking id
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*')->from('#__chpanel_bookings AS a')
			->where('a.email = ' . $db->quote($vars['email']))
			->where('a.voucher = ' . $db->quote($vars['voucher']));
		$booking = $db->setQuery($query)->loadObject();

		// check booking & booking status
		if (!$booking)
		{
			return 0;
		}
		if (in_array($booking->state, array(201, 202, 203)))
		{
			return 0;
		}

		return $booking->id;
	}

	/**
	 * Get Booking Data
	 */
	public function getBooking($id)
	{

		// get the booking, hotel and room info
		$db = JFactory::getDbo();
		$booking = $db->setQuery('SELECT * FROM `#__chpanel_bookings` WHERE `id` = ' . $id)->loadObject();
		if (!$booking)
		{
			return false;
		}
		$booking_params = json_decode($booking->params);

		// get the hotel
		$hotel = $db->setQuery('SELECT * FROM `#__chpanel_hotels` WHERE `id` = ' . $booking->hotel_id)->loadObject();
		if (!$hotel)
		{
			return false;
		}

		// get the room
		$room = $db->setQuery('SELECT * FROM `#__chpanel_rooms` WHERE `id` = ' . $booking->room_id)->loadObject();
		if (!$room)
		{
			return false;
		}

		// build CH Booking Object
		$data = new stdClass();
		$data->errors = array();
		$data->id = $booking->id;
		$data->website_id = 0;
		$data->time = $booking->created;

		// data fields
		foreach (array('hotel_id', 'checkin', 'checkout', 'board', 'voucher') as $v)
		{
			$data->$v = $booking->$v;
		}

		// amounts
		$data->amount = $booking->amount;
		$data->amount_rooms = $booking->amount;
		$data->amount_extras = 0;
		$data->amount_packs = 0;

		// state & payment
		$data->state = $booking->status;
		$data->payment = $booking->payment;
		$data->payment_state = $booking->payment_status;

		// guest fields
		foreach (array('first_name', 'last_name', 'email', 'phone', 'street', 'city', 'zip', 'country', 'comments') as $v)
		{
			$data->$v = $booking->$v;
		}

		// language
		$data->lang_code = $booking->language;

		// website
		$data->website = new stdClass();
		$data->website->notify = $this->chpanel_params->get('notify', '');

		// conditions
		$data->conditions_website = $booking_params->conditions_website;
		$data->conditions_hotel = $booking_params->conditions_hotel;

		// other info
		$data->arrival = $booking_params->arrival;
		$data->nights = ceil((JFactory::getDate($booking->checkout)->toUnix() - JFactory::getDate($booking->checkin)->toUnix()) / 86400);

		// hotel
		$data->hotel = new stdClass();
		$data->hotel->id = $hotel->id;
		$data->hotel->title = $hotel->title;
		$data->hotel->slug = $hotel->alias;

		// hotel data
		foreach (array('info', 'text', 'conditions', 'lat', 'lng', 'zoom', 'email', 'video', 'phone', 'street', 'city', 'state', 'zip', 'timezone') as $v)
		{
			$data->hotel->$v = $hotel->$v;
		}
		$data->hotel->state = $hotel->region;

		// hotel img
		$path_hotel = JUri::root() . 'images/chpanel/hotels/';
		$data->hotel->img = $path_hotel . $hotel->id . '-screen.jpg';
		$data->hotel->img_tiny = $path_hotel . $hotel->id . '-tiny.jpg';
		$data->hotel->img_small = $path_hotel . $hotel->id . '-small.jpg';
		$data->hotel->img_med = $path_hotel . $hotel->id . '-med.jpg';
		$data->hotel->img_big = $path_hotel . $hotel->id . '-big.jpg';

		// hotel firm
		$data->hotel->firm = new stdClass();
		$hotel->slug = $hotel->alias;
		$hotel->currency = $this->chpanel_params->get('currency', 'EUR');
		$hotel->img = $data->hotel->img;
		$hotel->curr = 1;
		foreach (array('id', 'title', 'slug', 'info', 'email', 'phone', 'currency', 'notify', 'street', 'city', 'zip', 'state', 'img', 'curr') as $v)
		{
			$data->hotel->firm->$v = $hotel->$v;
		}
		$data->hotel->firm->state = $hotel->region;

		// reservation date (website timezone applied)
		$date = JFactory::getDate($data->time);
		$date->setTimezone(new DateTimeZone($hotel->timezone));
		$data->date = $date->format('Y-m-d H:i', true);
		//$data->date = JHtml::date($data->time, 'Y-m-d H:i', $hotel->timezone);

		$data->currency = '';
		$data->amount_curr = $data->amount;

		$data->rooms = array();
		$data->rooms[0] = new stdClass();
		$data->rooms[0]->room_id = $room->id;
		$data->rooms[0]->guest = $data->first_name . ' ' . $data->last_name;
		$data->rooms[0]->smoking = 0;

		// booking fields
		$data->rooms[0]->adult = $booking->adult;
		$data->rooms[0]->child = $booking->child;
		$data->rooms[0]->baby = $booking->baby;
		$data->rooms[0]->amount = $booking->amount;
		$data->rooms[0]->amount_curr = $booking->amount;
		$data->rooms[0]->amount_extras = 0;
		$data->rooms[0]->amount_pack = 0;
		$data->rooms[0]->pack_id = 0;
		$data->rooms[0]->params = new stdClass();
		$data->rooms[0]->params->rates = $booking_params->rates;
		$data->rooms[0]->params->conditions = 0;
		$data->rooms[0]->params->discount = '';
		$data->rooms[0]->params->amount_discount = 0;
		$data->rooms[0]->params->amount_discount_curr = 0;
		$data->rooms[0]->params->smoking = 0;
		$data->rooms[0]->params->bed = 0;
		$data->rooms[0]->params->extras = array();
		$data->rooms[0]->params->pack = array();

		// room fields
		foreach (array('title', 'info', 'capacity', 'max_adult', 'max_child', 'max_baby', 'reference') as $v)
		{
			$data->rooms[0]->$v = $room->$v;
		}

		// room img
		$path_room = JUri::root() . 'images/chpanel/rooms/';
		$data->rooms[0]->img = $path_room . $room->id . '-screen.jpg';
		$data->rooms[0]->img_tiny = $path_room . $room->id . '-tiny.jpg';
		$data->rooms[0]->img_small = $path_room . $room->id . '-small.jpg';
		$data->rooms[0]->img_med = $path_room . $room->id . '-med.jpg';
		$data->rooms[0]->img_big = $path_room . $room->id . '-big.jpg';


		/*
		  echo '<pre>';
		  print_r($data);
		  echo '<pre>';
		 * 
		 */

		return $data;
	}

	/**
	 * Insert a new booking
	 */
	public function newBooking($vars)
	{

		// Abort other bookings in processing state from this user
		$this->abortBookings($vars['session']);

		// Get data to check availability and prepare the booking insert
		$data = $this->getData();
		$hotel = $data->items[0];
		$amount = 0;
		$board = $vars['board'];
		$room = false;
		foreach ($hotel->rooms[0] as $r)
		{
			if ($r->id == $vars['room'])
			{
				$room = $r;
				$amount = $room->amounts->$board;
				break;
			}
		}
		if (!$room)
		{
			return false;
		}

		// prepare new booking
		$booking = new stdClass();

		// assing vars
		$booking->hotel_id = $vars['hotel'];
		$booking->room_id = $vars['room'];
		$booking->session_id = $vars['session'];
		$booking->ip = $vars['ip'];
		$booking->language = $vars['lang'];
		$booking->status = $vars['state'];

		// generate voucher
		jimport('joomla.user.helper');
		$booking->voucher = strtoupper(JUserHelper::genRandomPassword(6));

		// text fields
		foreach (array('first_name', 'last_name', 'email', 'phone', 'street', 'city', 'zip', 'country', 'comments') as $v)
		{
			$booking->$v = $vars[$v];
		}

		// booking config
		$booking->checkin = $vars['checkin'];
		$booking->checkout = $vars['checkout'];
		$booking->board = $board;
		$booking->adult = $vars['adults'];
		$booking->child = $vars['childs'];
		$booking->baby = $vars['babys'];
		$booking->amount = $amount;

		// payment and payment status
		// 0. booking request 
		// 1. online payment 
		// 2. card as guarantee
		$booking->payment_status = $vars['payment_state'];

		// determine payment amount
		$booking->payment = $vars['payment'];
		if ($booking->payment_status == 1)
		{
			// payment based on percentage by default
			$payment = $amount * $vars['payment'] / 100;
			// check payment is not smaller than minimum pay
			$booking->payment = $payment < $vars['payment_min'] ? $vars['payment_min'] : $payment;
			// check payment is not larger than total amount
			$booking->payment = $payment > $booking->amount ? $booking->amount : $payment;
		}

		// joomla admin fields
		$booking->state = 1;
		$booking->created = JFactory::getDate()->toSql();

		// params
		$params = new stdClass();
		$params->arrival = $vars['arrival'];
		$params->conditions_hotel = $hotel->conditions;
		$params->conditions_website = $vars['conditions'];
		$params->rates = $room->rates;
		$params->newsletter = $vars['newsletter'] == 'on' ? 1 : 0;

		// tracking
		$params->tracking = new stdClass();
		foreach (array('findus', 'referer', 'gclid', 'aid', 'said', 'cookie_referer', 'cookie_date', 'cookie_gclid', 'cookie_aid', 'cookie_said') as $v)
		{
			$params->tracking->$v = $vars[$v];
		}

		// store original reservation details in params
		$params->original = $booking;
		$params->original->params = json_encode($params);

		// encode params before insert
		$booking->params = json_encode($params);

		// insert row
		$db = JFactory::getDbo();
		$db->insertObject('#__chpanel_bookings', $booking, 'id');

		// update voucher if based on id
		if ($vars['voucher'] == 'id')
		{
			$booking->voucher = $booking->id;
			$db->updateObject('#__chpanel_bookings', $booking, 'id');
		}

		// update inventory
		$this->updateInventory($booking);

		return $booking->id;
	}

	/**
	 * Abort other bookings being processed by the use
	 */
	private function abortBookings($session)
	{

		// get bookings in processing state from the same user
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.room_id, a.checkin, a.checkout')->from('#__chpanel_bookings AS a');
		$query->where('a.session_id = ' . $db->quote($session));
		$query->where('a.status < 200');
		$bookings = $db->setQuery($query)->loadObjectList();

		// change status to aborted and update availability
		if ($bookings)
		{
			foreach ($bookings as $booking)
			{
				$booking->status = 401;
				$db->updateObject('#__chpanel_bookings', $booking, 'id');
				$this->updateInventory($booking, 'up');
			}
		}
	}

}
