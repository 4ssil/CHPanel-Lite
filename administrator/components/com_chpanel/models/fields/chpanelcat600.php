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
class JFormFieldCHPanelCat600 extends JFormField
{

	/**
	 * Filter name
	 */
	protected $type = 'CHPanelCat600';

	/**
	 * Get the options
	 */
	public function getInput()
	{

		$this->multiple = true;

		$options = array();
		for ($i = 601; $i <= 712; $i++)
		{
			$option = new stdClass();
			$option->id = $i;
			$option->title = JText::_('COM_HOTEL_CAT_' . $i);
			$options[] = $option;
		}

		return JHtml::_('select.genericlist', $options, $this->name, 'class="inputbox" multiple="multiple" size="8"', 'id', 'title', $this->value, $this->id);
	}

}
