<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Hotels Model
 */
class CHPanelModelHotels extends JModelList
{

	/**
	 * Text prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEMS';

	/**
	 * Constructor
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.id',
				'a.created',
				'a.modified',
				'a.title',
				'a.alias',
				'a.state'
			);
		}
		parent::__construct($config);
	}

	/**
	 * State
	 */
	protected function populateState($ordering = null, $direction = null)
	{

		$filters = array('search', 'state');

		foreach ($filters as $filter)
		{
			$var = $this->getUserStateFromRequest($this->context . '.filter.' . $filter, 'filter_' . $filter);
			$this->setState('filter.' . $filter, $var);
		}

		parent::populateState('a.title', 'DESC');
	}

	/**
	 * Filters
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		return parent::getStoreId($id);
	}

	/**
	 * The List Query
	 */
	protected function getListQuery()
	{

		// main query
		$query = $this->_db->getQuery(true);
		$query->select('a.*');
		$query->from('#__chpanel_hotels AS a');

		// joins
		$query->select('l.title AS language_title')->join('LEFT', '#__languages AS l ON l.lang_code = a.language');
		$query->select('COUNT(i.id) AS images')->join('LEFT', '#__chpanel_images AS i ON i.hotel_id = a.id')->group('a.id');

		// checked out
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out_time');

		// state filter
		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}
		else if ($state != '*')
		{
			$query->where('a.state IN (0,1)');
		}

		// search filter
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $this->_db->Quote('%' . $this->_db->escape($search, true) . '%');
				$query->where('(a.title LIKE ' . $search . ')');
			}
		}

		// language filter
		$filter_language = $this->getState('filter.language');
		if ($filter_language)
		{
			$language = $this->_db->quote($filter_language);
			$query->where("(a.language = $language)");
		}

		// ordering clause
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		return $query;
	}

}
