<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Room Model
 */
class CHPanelModelRoom extends JModelAdmin
{

	/**
	 * Text prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEM';

	/**
	 * Get the table
	 */
	public function getTable($type = 'Room', $prefix = 'CHPanelTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * get the form
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_chpanel.room', 'room', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	/**
	 * Get the data that should be injected to the form
	 */
	protected function loadFormData()
	{

		$data = JFactory::getApplication()->getUserState('com_chpanel.edit.room.data', array());

		if (empty($data))
		{

			$data = $this->getItem();

			// default values
			if ($this->getState('room.id') == 0)
			{
				$filters = JFactory::getApplication()->getUserState('com_chpanel.rooms.filter');
				$data->set('hotel_id', $filters['hotel']);
			}

			// hotel
			$hotel_table = JTable::getInstance('Hotel', 'CHPanelTable');
			$hotel_table->load($data->hotel_id);
			$data->set('hotel', $hotel_table->title);
		}

		return $data;
	}

	/**
	 * Override save method to add the image processing
	 */
	public function save($data)
	{

		// perform default save
		$save = parent::save($data);
		if (!$save)
		{
			return false;
		}

		// save or delete image
		$imageHelper = new CHPanelHelperImage(JComponentHelper::getParams('com_chpanel'));
		$file = $_FILES['image'];
		if ($file['size'])
		{
			$imageHelper->uploadImage($file, $this->getState('room.id'), 'rooms');
		}
		else
		{
			if (JRequest::getInt('image_delete'))
			{
				$imageHelper->deleteImage($this->getState('room.id'), 'rooms');
			}
		}

		return true;
	}

	/**
	 * Override delete method to add the image processing
	 */
	public function delete(&$pks)
	{

		// standard joomla delete
		$delete = parent::delete($pks);
		if (!$delete)
		{
			return false;
		}

		// delete rooms
		$imageHelper = new CHPanelHelperImage(JComponentHelper::getParams('com_chpanel'));
		foreach ($pks as $pk)
		{
			$imageHelper->deleteImage($pk, 'rooms');
		}

		return true;
	}

	/**
	 * Set reordering conditions
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'hotel_id = ' . (int) $table->hotel_id;
		return $condition;
	}

}
