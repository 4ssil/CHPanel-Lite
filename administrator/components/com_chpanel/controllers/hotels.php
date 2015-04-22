<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Hotels Controller
 */
class CHPanelControllerHotels extends JControllerAdmin
{

	/**
	 * Strings prefix
	 */
	protected $text_prefix = 'COM_CHPANEL_ITEMS';

	/**
	 * constructor
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('unfeatured', 'featured');
	}

	/**
	 * get The model
	 */
	public function getModel($name = 'Hotel', $prefix = 'CHPanelModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Rebuild data on publish
	 */
	public function publish()
	{

		parent::publish();

		CHPanelHelperData::buildDataObject();
	}

}
