<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Translation View
 */
class CHPanelViewTranslation extends JViewLegacy
{

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{

		// get the data from the model
		$this->item = $this->get('Item');
		$this->translation = $this->get('Translation');
		$this->language = CHPanelHelperLangs::getLangTitle(JRequest::getCmd('lang'));
		$this->params = JComponentHelper::getParams('com_chpanel');

		// create the toolbar
		CHPanelHelper::getToolbar(false, true, $this->item->title . ' (' . JText::sprintf('COM_CHPANEL_TRANSLATION_TITLE', $this->language) . ')');

		// display the view template
		parent::display($tpl);
	}

}
