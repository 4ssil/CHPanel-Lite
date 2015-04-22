<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Panel View
 */
class CHPanelViewPanel extends JViewLegacy
{

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{

		// get model data
		$this->info = $this->get('Info');
		$this->banner = $this->get('Banner');
		$this->bookings = $this->get('Bookings');
		$this->checkins = $this->get('Checkins');

		// create the toolbar
		CHPanelHelper::getToolbar('panel');

		// sidebar
		$this->sidebar = JHtmlSidebar::render();

		// display the view layout
		parent::display($tpl);
	}

}
