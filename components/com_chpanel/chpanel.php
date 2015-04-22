<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

// load language files
JFactory::getLanguage()->load('com_chpanel', null, 'en-GB', true);
JFactory::getLanguage()->load('com_chpanel');

// load helpers
require_once __DIR__ . '/helpers/chpanel.php';
require_once __DIR__ . '/models/search.php';
require_once __DIR__ . '/models/booking.php';

/**
 * CHPanel Helper
 */
class CHPanel
{

	/**
	 * Get availability
	 */
	static function getHotels($vars)
	{

		$search = new CHPanelSearch($vars);
		$data = $search->getData();

		return $data;
	}

	/**
	 * Insert new booking
	 */
	static function newBooking($vars)
	{

		$booking = new CHPanelBooking($vars);
		$id = $booking->newBooking($vars);

		return $id;
	}

	/**
	 * Get booking data
	 */
	static function getBooking($id)
	{

		$booking = new CHPanelBooking();
		$data = $booking->getBooking($id);

		return $data;
	}

	/**
	 * Update a booking
	 */
	static function updateBooking($vars)
	{

		$booking = new CHPanelBooking();
		$id = $booking->updateBooking($vars);

		return $id;
	}

	/**
	 * Get My Booking booking Id
	 */
	static function myBooking($vars)
	{

		$booking = new CHPanelBooking();
		$id = $booking->myBooking($vars);

		return $id;
	}

}
