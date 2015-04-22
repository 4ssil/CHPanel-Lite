<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Hotel Controller
 */
class CHPanelControllerHotel extends JControllerForm
{

	/**
	 * Prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEM';

	/**
	 * Rebuild data on save
	 */
	protected function postSaveHook()
	{

		CHPanelHelperData::buildDataObject();
	}

	/**
	 * batch
	 */
	public function batch($model = null)
	{

		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Hotel', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_chpanel&view=hotels' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

}
