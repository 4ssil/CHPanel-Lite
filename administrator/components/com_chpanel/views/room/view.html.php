<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Room View
 */
class CHPanelViewRoom extends JViewLegacy
{

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{

		// get the data from the model
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');

		// get the image
		$imageHelper = new CHPanelHelperImage(JComponentHelper::getParams('com_chpanel'));
		$this->image = $imageHelper->getImage($this->item->id, 'rooms');

		// get params
		$this->params = JComponentHelper::getParams('com_chpanel');

		// create the toolbar
		CHPanelHelper::getToolbar(false, $this->item->id, $this->item->title);

		// display the view template
		parent::display($tpl);
	}

}
