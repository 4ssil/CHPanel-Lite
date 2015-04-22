<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Manage View
 */
class CHPanelViewManage extends JViewLegacy
{

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{

		// get the data
		$this->months = $this->get('Months');
		$this->month_days = $this->get('MonthDays');
		$this->month_start = $this->get('MonthStart');
		$this->month_16 = $this->get('Month16');
		$this->hotels = $this->get('Hotels');
		$this->hotel = $this->get('Hotel');
		$this->inventory = $this->get('Inventory');
		$this->bookings_in_progress = $this->get('BookingsInProgress');

		// filters
		$this->state = $this->get('State');

		// default dates
		$app = JFactory::getApplication();
		$date_format = CHPanelHelper::getDateFormat(JText::_('COM_CHPANEL_LOCALE'));

		// start & end
		$this->start = $app->getUserStateFromRequest('com_chpanel.manage.start', 'start', JFactory::getDate()->format($date_format), 'string');
		$this->end = $app->getUserStateFromRequest('com_chpanel.manage.end', 'end', JFactory::getDate('+ 1 day')->format($date_format), 'string');

		// title 
		$date = new JDate('01-' . $this->state->get('filter.month'));
		$this->title = $this->hotel ? $this->hotel->title . ' - ' . $date->format('F Y') : $date->format('F Y');

		// toolbar
		CHPanelHelper::getToolbar('manage');

		// sidebar
		$this->sidebar = JHtmlSidebar::render();

		// display the view layout
		parent::display($tpl);
	}

}
