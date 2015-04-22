<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Bookings View
 */
class CHPanelViewBookings extends JViewLegacy
{

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{

		// get the data from the model
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');

		// filters
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// start & end
		$app = JFactory::getApplication();
		$this->date = $app->getUserStateFromRequest('com_chpanel.bookings.date', 'date', '', 'string');
		$this->start = $app->getUserStateFromRequest('com_chpanel.bookings.start', 'start', '', 'string');
		$this->end = $app->getUserStateFromRequest('com_chpanel.bookings.end', 'end', '', 'string');

		// toolbar and sidbar
		CHPanelHelper::getToolbar('bookings', $this->state->get('filter.state') == -2);
		$this->sidebar = JHtmlSidebar::render();

		// display the view template
		parent::display($tpl);
	}

}
