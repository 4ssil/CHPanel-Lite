<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Translation Model
 */
class CHPanelModelTranslation extends JModelLegacy
{

	/**
	 * Text prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEM';

	/**
	 * Get the item
	 */
	public function getItem()
	{

		if (!isset($this->item))
		{

			// prepare query
			$db = $this->_db;
			$id = JRequest::getInt('id', 0);

			// get the item
			$query = $db->getQuery(true)->select('a.*')->from('#__chpanel_hotels AS a')->where("a.id = $id");
			$this->item = $db->setQuery($query)->loadObject();

			// params
			$this->item->params = $this->item ? json_decode($this->item->params) : array();
			if (count($this->item->params))
			{
				foreach ($this->item->params as $paramt => $paramv)
				{
					$paramn = 'params_' . $paramt;
					$this->item->$paramn = $paramv;
				}
			}

			// get the item rooms
			$query_rooms = $db->getQuery(true)->select('*')->from('#__chpanel_rooms')->where("hotel_id = $id");
			$this->item->rooms = $db->setQuery($query_rooms)->loadObjectList();

			// get the item images
			$query_images = $db->getQuery(true)->select('*')->from('#__chpanel_images')->where("hotel_id = $id");
			$this->item->images = $db->setQuery($query_images)->loadObjectList();
		}

		return $this->item;
	}

	/**
	 * Get the translation
	 */
	public function getTranslation()
	{

		if (!isset($this->translation))
		{

			// check state (error saving)
			$data = JFactory::getApplication()->getUserState('com_chpanel.translation.data', null);
			if ($data)
			{
				$data->data = json_decode($data->translation);
				$this->translation = $data;
				return $this->translation;
			}

			// prepare query
			$db = $this->_db;
			$id = JRequest::getInt('id', 0);

			// get translation
			$query = $db->getQuery(true)->select('a.*')->from('#__chpanel_translations AS a');
			$query->where('a.lang = ' . $db->quote(JRequest::getCmd('lang')));
			$query->where("a.hotel_id = $id");
			$this->translation = $db->setQuery($query)->loadObject();

			if ($this->translation)
			{
				$strings = json_decode($this->translation->translation);
				$this->translation->data = new stdClass();
				foreach ($strings as $field => $value)
				{
					$this->translation->data->$field = $value;
				}
			}
		}

		return $this->translation;
	}

	/**
	 * getTranslationId
	 */
	private function getTranslationId()
	{
		$translation = $this->getTranslation();
		return $translation->id;
	}

	/**
	 * Save
	 */
	public function save()
	{

		// preapre 
		$db = $this->_db;
		$item = $this->getItem();
		$user = JFactory::getUser();

		// get main values
		$data = array();
		$data['hotel_id'] = JRequest::getInt('id');
		$data['lang'] = JRequest::getCmd('lang');
		$data['title'] = trim(JRequest::getString('title'));
		$data['alias'] = JApplication::stringURLSafe(JRequest::getString('alias'));
		if (trim(str_replace('-', '', $data['alias'])) == '')
		{
			$data['alias'] = JApplication::stringURLSafe($data['title']);
		}

		// prepare an array with the rest of the translation strings
		$strings = array(
			'info',
			'text',
			'conditions',
			'video',
			'street', 'zip', 'city', 'region', 'country', 'phone', 'email'
		);
		foreach ($item->images as $image)
		{
			$strings[] = 'image_' . $image->id;
			$strings[] = 'image_info_' . $image->id;
		}
		foreach ($item->rooms as $room)
		{
			$strings[] = 'room_' . $room->id;
			$strings[] = 'room_info_' . $room->id;
			$strings[] = 'room_text_' . $room->id;
			$strings[] = 'room_video_' . $room->id;
		}

		// get the translations for each string
		$translation = array();
		foreach ($strings as $string)
		{
			$translation[$string] = JRequest::getString($string);
		}

		// encode translation
		$data['translation'] = json_encode($translation);

		// check the data
		$check = $this->check($data);
		if (!$check)
		{
			// store the data to model state (so it can be recovered if save fails)
			JFactory::getApplication()->setUserState('com_chpanel.translation.data', json_decode(json_encode($data)));
			return false;
		}

		// delete current translation if exists
		$this->delete();

		// add the new translation
		$query = $db->getQuery(true)->insert('#__chpanel_translations');
		$query->set('created = ' . $db->Quote(JFactory::getDate()->toSql()));
		$query->set('created_by = ' . $user->id);
		foreach ($data as $field => $value)
		{
			$query->set($db->quoteName($field) . ' = ' . $db->quote((string) $value));
		}
		$db->setQuery($query)->query();

		return true;
	}

	/**
	 * Delete a translation
	 */
	public function delete()
	{

		// delete current translation if exists
		$db = $this->_db;
		$query_delete = $db->getQuery(true)->delete('#__chpanel_translations')->where('id = ' . JRequest::getInt('tid', 0));
		$db->setQuery($query_delete)->query();

		return true;
	}

	/**
	 * Check before save translation
	 */
	private function check($data)
	{

		$db = JFactory::getDbo();
		$q_l = $db->quote($data['lang']);
		$q_a = $db->quote($data['alias']);

		// check item title
		if ($data['title'] == '')
		{
			$this->setError(JText::_('COM_CHPANEL_ANY_ERROR_NOTITLE'));
		}

		// check item alias
		else
		{

			$alias_ok = true;
			$data['alias'] = JApplication::stringURLSafe($data['alias']);
			if (trim(str_replace('-', '', $data['alias'])) == '')
			{
				$data['alias'] = JApplication::stringURLSafe($data['title']);
				if (trim(str_replace('-', '', $data['alias'])) == '')
				{
					$alias_ok = false;
					$this->setError(JText::_('COM_CHPANEL_ANY_ERROR_NOALIAS'));
				}
			}

			// check duplicated alias
			if ($alias_ok)
			{

				// check item unique alias (in translations)
				$q = "SELECT `hotel_id` FROM `#__chpanel_translations` WHERE `alias` = $q_a AND `lang` = $q_l AND hotel_id != " . (int) $data['hotel_id'];
				$r = $db->setQuery($q)->loadColumn();
				if ($r)
				{
					$this->setError(JText::_('COM_CHPANEL_ITEM_ALIAS_ERROR'));
				}

				// check duplicated alias in different languages
				else
				{

					$q = "SELECT `id` FROM `#__chpanel_hotels` WHERE `alias` = $q_a AND `language` = $q_l AND id != " . (int) $data['hotel_id'];
					$r = $db->setQuery($q)->loadColumn();
					if ($r)
					{
						$this->setError(JText::_('COM_CHPANEL_ITEM_ALIAS_ERROR'));
					}
				}
			}
		}

		if (!$this->getErrors())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

}
