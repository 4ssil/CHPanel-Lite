<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Panel Model
 */
class CHPanelModelPanel extends JModelLegacy
{

	/**
	 * Get latest Bookings
	 */
	public function getBookings()
	{

		if (!isset($this->bookings))
		{

			// get confirmed bookings
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.*')->from('#__chpanel_bookings AS a');
			$query->select('h.title AS hotel')->join('LEFT', '#__chpanel_hotels AS h ON h.id = a.hotel_id');
			$query->where('a.status > 200');
			$query->where('a.status < 300');
			$query->where('a.state = 1');
			$query->order('a.created');
			$this->bookings = $db->setQuery($query, 0, 5)->loadObjectList();
		}

		return $this->bookings;
	}

	/**
	 * Get latest Bookings
	 */
	public function getCheckins()
	{

		if (!isset($this->checkins))
		{

			// get confirmed bookings
			$db = JFactory::getDbo();
			$today = $db->quote(JFactory::getDate()->format('Y-m-d'));
			$query = $db->getQuery(true);
			$query->select('a.*')->from('#__chpanel_bookings AS a');
			$query->select('h.title AS hotel')->join('LEFT', '#__chpanel_hotels AS h ON h.id = a.hotel_id');
			$query->where('a.status > 200');
			$query->where('a.status < 300');
			$query->where('a.state = 1');
			$query->where('a.checkin >= ' . $today);
			$query->order('a.checkin ASC');
			$this->checkins = $db->setQuery($query, 0, 5)->loadObjectList();
		}

		return $this->checkins;
	}

	/**
	 * Get extension info
	 */
	public function getInfo()
	{

		if (!isset($this->info))
		{

			// info feed
			$url = 'https://secure.cloudhotelier.com/versions/info.json';
			$info = new stdClass();

			// get cached data
			$file = JPATH_CACHE . '/' . md5($url);
			if (file_exists($file))
			{
				$info = json_decode(file_get_contents($file));
			}

			// refresh data cache (check after 30 minutes)
			if (!file_exists($file) || (time() - filemtime($file)) > 1800)
			{
				$response = @file_get_contents($url);
				if ($response)
				{
					file_put_contents($file, $response);
					$info = json_decode($response);
				}
			}

			// get installed version
			$com_chpanel_info = json_decode($this->_db->setQuery("SELECT manifest_cache FROM #__extensions WHERE name = 'com_chpanel'")->loadResult());
			$info->com_chpanel_installed = $com_chpanel_info->version;

			// get installed version
			$com_hotel_info = json_decode($this->_db->setQuery("SELECT manifest_cache FROM #__extensions WHERE name = 'com_hotel'")->loadResult());
			$info->com_hotel_installed = $com_hotel_info->version;

			// check extensions 
			$info->com_chpanel_ok = version_compare($info->com_chpanel_installed, $info->com_chpanel, '<');
			$info->com_hotel_ok = version_compare($info->com_hotel_installed, $info->com_hotel, '<');

			// get lang
			$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);

			// define news
			if (isset($info->news->$lang))
			{
				$info->news = $info->news->$lang;
			}
			else
			{
				$info->news = $info->news->en;
			}

			// define banner
			if (isset($info->banner->$lang))
			{
				$info->banner = $info->banner->$lang;
			}
			else
			{
				$info->banner = $info->banner->en;
			}
		}

		$this->info = $info;

		return $info;
	}

	/**
	 * Get Pro Panel Messages
	 */
	public function getBanner()
	{

		$this->banner = $this->info->banner[rand(0, count($this->info->banner) - 1)];

		// info feed
		if (!JRequest::getString('chpanelbanner', '1', 'cookie'))
		{
			$this->banner = 0;
		}

		return $this->banner;
	}

}
