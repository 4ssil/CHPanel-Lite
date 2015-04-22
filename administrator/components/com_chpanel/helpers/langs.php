<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Language Helper
 */
class CHPanelHelperLangs
{

	/**
	 * Get current website's content languages
	 */
	static function getLangs()
	{
		$db = JFactory::getDbo();
		$query = 'SELECT * FROM `#__languages` WHERE `published` = 1 ORDER BY `ordering`';
		return $db->setQuery($query)->loadObjectList();
	}

	/**
	 * Get current website's content languages
	 */
	static function getLangTitle($code)
	{
		$db = JFactory::getDbo();
		$query = 'SELECT `title` FROM `#__languages` WHERE `lang_code` = ' . $db->quote($code);
		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Build the list header with the languages to translate
	 */
	static function listHeader($langs, $view = 'items')
	{
		$html = '';
		$path = JURI::root() . 'media/mod_languages/images/';
		$width = $view == 'items' ? 'width: 10%; ' : 'width: 20%; ';
		foreach ($langs as $lang)
		{
			$html .= '<th class="nowrap center" style="' . $width . '">' . "\n";
			$html .= '  <img alt="' . $lang->title_native . '" src="' . $path . $lang->image . '.gif' . '" title="' . $lang->title_native . '" />' . "\n";
			$html .= '</th>' . "\n";
		}
		return $html;
	}

	/**
	 * Build the list link to languages translations
	 */
	static function listItem($langs, $translations, $item)
	{

		$html = '';

		foreach ($langs as $lang)
		{

			if ($item->language == '*')
			{

				$html .='<td class="small center">-</td>';
			}
			else
			{

				$disabled = '';
				$state = 'missing';
				$link = JRoute::_('index.php?option=com_chpanel&task=translation.edit&id=' . $item->id . '&lang=' . $lang->lang_code);
				if ($item->language == $lang->lang_code)
				{
					$state = 'original';
					$link = 'javascript:;';
					$disabled = 'disabled';
				}
				else
				{
					if (!empty($translations))
					{
						foreach ($translations as $translation)
						{
							if ($translation->hotel_id == $item->id && $translation->lang == $lang->lang_code)
							{
								$state = 'ok';
								if ($item->modified > $translation->created)
								{
									$state = 'check';
								}
							}
						}
					}
				}
				$html .='<td class="small center"><a class="btn btn-mini ' . $disabled . ' translation-' . $state . '" href="' . $link . '">' . JText::_('COM_CHPANEL_ANY_TRANSLATION_' . strtoupper($state)) . '</a></td>';
			}
		}

		return $html;
	}

	/**
	 * Get the translations for a items and a table
	 */
	static function getTranslations($items)
	{

		if (!$items)
		{
			return array();
		}

		$ids = array();
		foreach ($items as $item)
		{
			$ids[] = $item->id;
		}

		$db = JFactory::getDbo();
		$query = "SELECT * FROM `#__chpanel_translations` WHERE `hotel_id` IN(" . implode(',', $ids) . ")";
		$translations = $db->setQuery($query)->loadObjectList();

		return $translations;
	}

}
