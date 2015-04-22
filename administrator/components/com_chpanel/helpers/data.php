<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Data Helper
 */
class CHPanelHelperData
{

	/**
	 * Update #__hotel_data tables with Lite data
	 */
	public static function buildDataObject()
	{

		$db = JFactory::getDbo();
		$chpanel_params = JComponentHelper::getParams('com_chpanel');

		// get db info
		$hotels = $db->setQuery('SELECT * FROM #__chpanel_hotels AS a WHERE a.state = 1')->loadObjectList();
		$images = $db->setQuery('SELECT * FROM #__chpanel_images AS a WHERE a.state = 1')->loadObjectList();
		$rooms = $db->setQuery('SELECT * FROM #__chpanel_rooms AS a WHERE a.state = 1')->loadObjectList();
		$languages = CHPanelHelperLangs::getLangs();
		$translations = $db->setQuery('SELECT * FROM #__chpanel_translations AS a')->loadObjectList();

		// check hotels and rooms
		if (!$hotels || !$rooms)
		{
			return false;
		}

		// delete prev data
		$db->setQuery('DELETE FROM #__hotel_data WHERE id > 0')->execute();

		// prepare to insert items
		$items = array();
		foreach ($hotels as $hotel)
		{

			// begin new item object
			$item = new stdClass();
			$item->hotel_id = $hotel->id;
			$item->lang = substr($hotel->language, 0, 2);
			$item->slug = $hotel->alias;
			$item->title = $hotel->title;

			// begin new data object
			$data = new stdClass();
			$data->id = $hotel->id;
			$data->title = $hotel->title;
			$data->slug = $hotel->alias;
			$data->currency = $chpanel_params->get('currency', 'EUR');
			$data->lang = $item->lang;

			// deprecated in CH v3
			$data->firm = $hotel->title;
			$data->firm_id = 1;
			$data->zone_id = 1;

			// assign hotel fields
			foreach (array('info', 'text', 'conditions', 'lat', 'lng', 'zoom', 'email', 'video', 'phone', 'street', 'city', 'state', 'zip') as $v)
			{
				$data->$v = $hotel->$v;
			}

			// categories
			$data->cats = array();
			$params = json_decode($hotel->params);
			foreach (array(1, 2, 3, 4, 5, 6) as $c)
			{
				$cat = 'cat' . $c . '00';
				if (!is_array($params->$cat))
				{
					$data->cats[] = $params->$cat;
				}
				else
				{
					foreach ($params->$cat as $ca)
					{
						$data->cats[] = $ca;
					}
				}
			}

			// hotel photos
			$data->photos = self::getPhotos($hotel->id, $hotel, $images, 'hotel');

			// rooms_data
			$data->rooms_data = array();

			// other lite empty data
			$data->packs_data = array();
			$data->promos_data = array();
			$data->offers_data = array();

			foreach ($rooms as $room)
			{

				if ($room->hotel_id == $hotel->id)
				{

					// begin new room data object
					$room_data = new stdClass();
					foreach (array('id', 'title', 'info', 'text', 'video', 'rate') as $v)
					{
						$room_data->$v = $room->$v;
					}

					// capacity
					$room_data->capacity = new stdClass();
					$room_data->capacity->standard = $room->capacity;
					$room_data->capacity->max = new stdClass();
					$room_data->capacity->max->adult = $room->max_adult;
					$room_data->capacity->max->child = $room->max_child;
					$room_data->capacity->max->baby = $room->max_baby;

					// bed & smoking
					$room_data->smoking = 0;
					$room_data->bed = 0;

					// photos
					$room_data->photos = self::getPhotos($hotel->id, $room, $images, 'room');

					// assign room
					$data->rooms_data[] = $room_data;
				}
			}

			// encode data
			$item->data = json_encode($data);

			// insert the new item
			$db->insertObject('#__hotel_data', $item, 'id');

			// insert item translations
			foreach ($languages as $language)
			{

				if ($language->lang_code != $hotel->language)
				{

					$lang_ok = false;

					// search for a translation, apply and insert data object
					foreach ($translations as $translation)
					{

						if ($translation->lang == $language->lang_code && $translation->hotel_id == $hotel->id)
						{

							// prepare to add a new item
							$translated_item = $item;
							$translated_item->id = null;
							$translated_item->lang = substr($translation->lang, 0, 2);
							$translated_item->slug = $translation->alias;
							$translated_item->title = $translation->title;

							// get translated data
							$translated_data = self::applyTranslation($data, $translation);
							$translated_item->data = json_encode($translated_data);

							// insert the new item
							$db->insertObject('#__hotel_data', $translated_item, 'id');

							$lang_ok = true;
						}
					}

					// no translation found
					if (!$lang_ok)
					{

						// insert original item as the translated item
						$no_translated_item = $item;
						$no_translated_item->id = null;
						$no_translated_item->lang = substr($language->lang_code, 0, 2);

						// insert the new item
						$db->insertObject('#__hotel_data', $no_translated_item, 'id');
					}
				}
			}
		}

		return true;
	}

	/**
	 * Get the photos
	 */
	public static function getPhotos($hotel_id, $item, $images, $type = 'hotel')
	{

		// photos
		$photos = array();
		$photo = new stdClass();
		$path_item = JUri::root() . 'images/chpanel/' . $type . 's/' . $item->id;
		$photo->id = $item->id;
		$photo->title = $item->title;
		$photo->info = $item->info;
		$photo->img = $path_item . '-screen.jpg';
		$photo->img_tiny = $path_item . '-tiny.jpg';
		$photo->img_small = $path_item . '-small.jpg';
		$photo->img_med = $path_item . '-med.jpg';
		$photo->img_big = $path_item . '-big.jpg';
		$photos[] = $photo;

		// images
		if ($images)
		{

			foreach ($images as $image)
			{

				if ($image->hotel_id == $hotel_id)
				{

					$params = json_decode($image->params);
					$params->tags = isset($params->tags) ? array_keys((array) $params->tags) : array();
					$params->rooms = isset($params->rooms) ? array_keys((array) $params->rooms) : array();

					if (($type == 'hotel' && $params->tags) || ($type = 'room' && in_array($item->id, $params->rooms)))
					{

						$path_image = JUri::root() . 'images/chpanel/images/' . $image->id;

						$photo = new stdClass();
						$photo->id = $image->id;
						$photo->title = $image->title;
						$photo->info = $image->info;
						$photo->img = $path_image . '-screen.jpg';
						$photo->img_tiny = $path_image . '-tiny.jpg';
						$photo->img_small = $path_image . '-small.jpg';
						$photo->img_med = $path_image . '-med.jpg';
						$photo->img_big = $path_image . '-big.jpg';
						$photo->tags = $params->tags;
						$photos[] = $photo;
					}
				}
			}
		}

		return $photos;
	}

	/**
	 * Apply translation to hotel object item
	 */
	public static function applyTranslation($data, $translation)
	{

		// get translation strings
		$strings = json_decode($translation->translation);

		$data->title = $translation->title;
		$data->slug = $translation->alias;
		$data->lang = substr($translation->lang, 0, 2);
		$data->firm = $translation->title;

		// hotel strings
		foreach ($strings as $string => $value)
		{
			if (isset($data->$string))
			{
				$data->$string = $value;
			}
		}

		// hotel photos
		foreach ($data->photos as $i => $photo)
		{
			if (!$i)
			{
				$photo->title = $data->title;
				$photo->info = $data->info;
			}
			else
			{
				foreach ($strings as $string => $value)
				{
					if ($string == 'image_' . $photo->id)
					{
						$photo->title = $value;
					}
					if ($string == 'image_info_' . $photo->id)
					{
						$photo->info = $value;
					}
				}
			}
		}

		// hotel rooms
		foreach ($data->rooms_data as $room)
		{

			// room strings
			foreach ($strings as $string => $value)
			{
				if ($string == 'room_' . $room->id)
				{
					$room->title = $value;
				}
				if ($string == 'room_info_' . $room->id)
				{
					$room->info = $value;
				}
				if ($string == 'room_text_' . $room->id)
				{
					$room->text = $value;
				}
				if ($string == 'room_video_' . $room->id)
				{
					$room->video = $value;
				}
			}

			// room photos
			foreach ($room->photos as $i => $photo)
			{
				if (!$i)
				{
					$photo->title = $room->title;
					$photo->info = $room->info;
				}
				else
				{
					foreach ($strings as $string => $value)
					{
						if ($string == 'image_' . $photo->id)
						{
							$photo->title = $value;
						}
						if ($string == 'image_info_' . $photo->id)
						{
							$photo->info = $value;
						}
					}
				}
			}
		}

		return $data;
	}

}
