<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * CHPanel Helper
 */
class CHPanelHelper
{

	/**
	 * Perform date modifications to make it suitable for JDate
	 */
	static function correctDateFormat($date)
	{

		$date_format = self::getDateFormat(JText::_('COM_CHPANEL_LOCALE'));

		if ($date_format == 'd/m/Y')
		{
			return str_replace('/', '-', $date);
		}

		if ($date_format == 'd.m.Y')
		{
			return str_replace('.', '-', $date);
		}

		return $date;
	}

	/**
	 * Avoid date errors on language debug and language switch
	 */
	static function getDateFormat($locale)
	{
		return trim(str_replace('*', '', $locale));
	}

	/**
	 * Apply translation to hotel object item
	 */
	public static function applyTranslation($item, $translation)
	{

		$item->title = $translation->title;
		$item->alias = $translation->alias;
		$strings = json_decode($translation->translation);

		// hotel strings
		foreach ($strings as $string => $value)
		{
			if (isset($item->$string))
			{
				$item->$string = $value;
			}
		}

		// hotel photos
		foreach ($item->photos as $i => $photo)
		{
			if (!$i)
			{
				$photo->title = $item->title;
				$photo->info = $item->info;
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
		foreach ($item->rooms[0] as $room)
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

		return $item;
	}

}
