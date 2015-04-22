<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Hotel Model
 */
class CHPanelModelHotel extends JModelAdmin
{

	/**
	 * Text prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEM';

	/**
	 * Get the table
	 */
	public function getTable($type = 'Hotel', $prefix = 'CHPanelTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * get the form
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_chpanel.hotel', 'hotel', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	/**
	 * Get the data that should be injected in the form.
	 */
	protected function loadFormData()
	{

		$data = JFactory::getApplication()->getUserState('com_chpanel.edit.hotel.data', array());

		if (!$data)
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Extend save method to add the image and tag processing
	 */
	public function save($data)
	{

		// joomla save 
		$save = parent::save($data);
		if (!$save)
		{
			return false;
		}

		// get the hotel id
		$hotel_id = $this->getState('hotel.id');
		if (!$hotel_id)
		{
			return false;
		}

		// save or delete image
		$imageHelper = new CHPanelHelperImage(JComponentHelper::getParams('com_chpanel'));
		$file = $_FILES['image'];
		if ($file['size'])
		{
			$imageHelper->uploadImage($file, $hotel_id, 'hotels');
		}
		else
		{
			if (JRequest::getInt('image_delete'))
			{
				$imageHelper->deleteImage($hotel_id, 'hotels');
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

			// delete hotel image file
			$imageHelper->deleteImage($pk, 'hotels');

			// delete hotel gallery images
			$query_images = $this->_db->getQuery(true)->select('id')->from('#__chpanel_images')->where("`hotel_id` = " . (int) $pk);
			$images_ids = $this->_db->setQuery($query_images)->loadColumn();
			if (count($images_ids))
			{
				// delete images files
				foreach ($images_ids as $image_id)
				{
					$imageHelper->deleteImage($image_id, 'images');
				}
				// db delete images
				$query_delete_images = $this->_db->getQuery(true)->delete('#__chpanel_images')->where("`hotel_id` = " . (int) $pk);
				$this->_db->setQuery($query_delete_images)->query();
			}
		}

		// delete hotel translations
		$this->_db->setQuery("DELETE FROM `#__chpanel_translations` WHERE `hotel_id` IN(" . implode(',', $pks) . ")")->query();

		return true;
	}

}
