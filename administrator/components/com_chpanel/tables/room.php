<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Room Table
 */
class CHPanelTableRoom extends JTable
{

	/**
	 * Constructor
	 */
	function __construct(&$db)
	{
		parent::__construct('#__chpanel_rooms', 'id', $db);
	}

	/**
	 * Bind
	 */
	public function bind($array, $ignore = '')
	{

		// bind params
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Extend store
	 */
	public function store($updateNulls = false)
	{

		$date = JFactory::getDate();
		$user = JFactory::getUser();

		// created & modified
		if (!$this->id)
		{
			$this->created = $date->toSql();
			$this->created_by = $user->get('id');
		}
		$this->modified = $date->toSql();
		$this->modified_by = $user->get('id');

		// ordering
		if (!$this->id)
		{
			$where = "hotel_id = " . $this->hotel_id;
			$this->ordering = $this->getNextOrder($where);
		}

		// params
		// Transform the params field
		if (is_array($this->params))
		{
			$registry = new JRegistry();
			$registry->loadArray($this->params);
			$this->params = (string) $registry;
		}

		return parent::store($updateNulls);
	}

	/**
	 * Check
	 */
	public function check()
	{

		// title 
		if (trim($this->title) == '')
		{
			$this->setError(JText::_('COM_CHPANEL_ANY_ERROR_NOTITLE'));
			return false;
		}

		return true;
	}

	/**
	 * Publish method (yeah this is sad but we are on J!3)
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{

		$k = $this->_tbl_key;

		// sanitize input
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// if there are no primary keys set check if the instance key is set already
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			else
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		// get table instance
		$table = JTable::getInstance('Room', 'CHPanelTable');

		foreach ($pks as $pk)
		{

			// Load the item
			if (!$table->load($pk))
			{
				$this->setError($table->getError());
			}

			// verify checkout
			if ($table->checked_out == 0 || $table->checked_out == $userId)
			{

				// change the state
				$table->state = $state;
				$table->checked_out = 0;
				$table->checked_out_time = $this->_db->getNullDate();

				// check the row
				$table->check();

				// store the row
				if (!$table->store())
				{
					$this->setError($table->getError());
				}
			}
		}

		return count($this->getErrors()) == 0;
	}

}
