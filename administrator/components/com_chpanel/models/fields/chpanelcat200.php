<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

// load helper
JFormHelper::loadFieldClass('list');

/**
 * CHPanelType
 */
class JFormFieldCHPanelCat200 extends JFormFieldList
{

	/**
	 * Filter name
	 */
	protected $type = 'CHPanelCat200';

	/**
	 * Get the options
	 */
	public function getOptions()
	{

		$options = array();
		for ($i = 201; $i <= 206; $i++)
		{
			$option = new stdClass();
			$option->value = $i;
			$option->text = JText::_('COM_HOTEL_CAT_' . $i);
			$options[] = $option;
		}

		return array_merge(parent::getOptions(), $options);
	}

}
