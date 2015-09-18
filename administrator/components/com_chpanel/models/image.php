<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Image Model
 */
class CHPanelModelImage extends JModelAdmin
{

	/**
	 * Text prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEM';

	/**
	 * Get the table
	 */
	public function getTable($type = 'Image', $prefix = 'CHPanelTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * get the form
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_chpanel.image', 'image', array('control' => 'jform', 'load_data' => $loadData));
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

		$data = JFactory::getApplication()->getUserState('com_chpanel.edit.image.data', array());

		if (empty($data))
		{

			$data = $this->getItem();

			// default values
			if ($this->getState('image.id') == 0)
			{
				$filters = JFactory::getApplication()->getUserState('com_chpanel.images.filter');
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
			$imageHelper->uploadImage($file, $this->getState('image.id'), 'images');
		}
		else
		{
			if (JRequest::getInt('image_delete'))
			{
				$imageHelper->deleteImage($this->getState('image.id'), 'images');
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

		// delete images
		$imageHelper = new CHPanelHelperImage(JComponentHelper::getParams('com_chpanel'));
		foreach ($pks as $pk)
		{
			$imageHelper->deleteImage($pk, 'images');
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

	/**
	 * Get the hotel rooms
	 */
	public function getRooms()
	{

		if (JRequest::getInt('id', 0))
		{
			$item = $this->getItem();
			$hotel_id = $item->hotel_id;
		}
		else
		{
			$filters = JFactory::getApplication()->getUserState('com_chpanel.images.filter');
			$hotel_id = $filters['hotel'];
		}

		// query
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.title');
		$query->from('#__chpanel_rooms AS a');
		$query->where('a.hotel_id = ' . $hotel_id);
		$query->where('a.state IN (0,1)');
		$query->order('a.ordering');

		return $db->setQuery($query)->loadObjectList();
	}

}
