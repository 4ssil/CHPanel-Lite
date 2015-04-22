<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Manage Controller
 */
class CHPanelControllerManage extends JControllerLegacy
{

	/**
	 * Apply task
	 */
	function apply()
	{

		$app = JFactory::getApplication();
		$model = $this->getModel('Manage');
		$apply = 'index.php?option=com_chpanel&view=manage';

		$save = $model->update();
		if (!$save)
		{
			$errors = $model->getErrors();
			foreach ($errors as $error)
			{
				$app->enqueueMessage($error, 'warning');
			}
		}

		$this->setRedirect($apply);
	}

}
