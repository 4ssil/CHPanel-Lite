<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Translation Controller
 */
class CHPanelControllerTranslation extends JControllerForm
{

	/**
	 * Prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEM';

	/**
	 * Edit task
	 */
	function edit($key = null, $urlVar = null)
	{
		$edit = 'index.php?option=com_chpanel&view=translation&id=' . JRequest::getInt('id') . '&lang=' . JRequest::getCmd('lang');
		$this->setRedirect($edit);
	}

	/**
	 * Save task
	 */
	function save($key = null, $urlVar = null)
	{

		$app = JFactory::getApplication();
		$model = $this->getModel();
		$apply = 'index.php?option=com_chpanel&view=translation&id=' . JRequest::getInt('id') . '&lang=' . JRequest::getCmd('lang');

		$save = $model->save();
		if (!$save)
		{
			$errors = $model->getErrors();
			foreach ($errors as $error)
			{
				$app->enqueueMessage($error, 'warning');
			}
			$this->setRedirect($apply);
			return false;
		}

		// rebuild data object
		CHPanelHelperData::buildDataObject();

		JFactory::getApplication()->setUserState('com_chpanel.translation.data', null);
		if (JRequest::getWord('task') == 'save')
		{
			$this->setRedirect('index.php?option=com_chpanel&view=hotels', JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));
		}
		else
		{
			$this->setRedirect($apply, JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));
		}
	}

	/**
	 * Cancel task
	 */
	function cancel($key = null)
	{
		JFactory::getApplication()->setUserState('com_chpanel.translation.data', null);
		$this->setRedirect('index.php?option=com_chpanel&view=hotels');
	}

	/**
	 * Delete task
	 */
	function delete()
	{
		$model = $this->getModel();
		$model->delete();
		JFactory::getApplication()->setUserState('com_chpanel.translation.data', null);
		$this->setRedirect('index.php?option=com_chpanel&view=hotels', JText::_('COM_CHPANEL_TRANSLATION_DELETED'));
	}

}
